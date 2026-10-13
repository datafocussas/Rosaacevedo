#!/usr/bin/env bash
# Prepara el servidor de Hostinger. Lo ejecuta GitHub Actions en cada despliegue, antes de activar.sh.
# La primera vez crea las carpetas, el archivo .env (con claves aleatorias) y conecta public_html con el sitio.
# Las siguientes veces no cambia nada de eso: solo actualiza la clave de acceso temporal del sitio.
#
# Uso: bash preparar.sh RUTA VERSION PUBLIC_HTML
# Variables de entorno: PHP_BIN, URL_SITIO, DB_NOMBRE, DB_USUARIO, DB_CLAVE, SITIO_USUARIO, SITIO_CLAVE
set -euo pipefail

RUTA="$1"
VERSION="$2"
PUBLIC_HTML="$3"
DIR="$RUTA/releases/$VERSION"
ENV="$RUTA/shared/.env"

# PHP 8.3: el de la línea de comandos de Hostinger no siempre es la versión elegida en hPanel.
PHP="$(bash "$DIR/deploy/php.sh")"
echo "PHP: $("$PHP" -r 'echo PHP_VERSION;') ($PHP)"

mkdir -p "$RUTA/releases" "$RUTA/shared/storage"/{app/public,app/private,framework/{cache/data,sessions,views},logs}
chmod -R 775 "$RUTA/shared/storage"

# 1. Archivo .env: solo se crea si no existe. Nunca se sobrescribe (tiene las llaves de cifrado).
if [ ! -f "$ENV" ]; then
  if [ -z "${DB_NOMBRE:-}" ] || [ -z "${DB_USUARIO:-}" ] || [ -z "${DB_CLAVE:-}" ]; then
    echo "ERROR: faltan los secretos DB_NOMBRE, DB_USUARIO o DB_CLAVE en GitHub. Revisa la guía (paso de GitHub)."
    exit 1
  fi

  APP_KEY="base64:$("$PHP" -r 'echo base64_encode(random_bytes(32));')"
  HMAC="$("$PHP" -r 'echo bin2hex(random_bytes(32));')"

  # Se parte de .env.example y se reemplazan las líneas de producción.
  NUEVA_APP_KEY="$APP_KEY" NUEVO_HMAC="$HMAC" "$PHP" -r '
    [$_, $origen, $destino] = $argv;
    // Comillas simples: Laravel las toma literales (sin interpretar $ ni barras) aunque la clave las tenga.
    $q = fn ($v) => str_contains($v, chr(39)) ? chr(34).addcslashes($v, chr(34).chr(92)).chr(34) : chr(39).$v.chr(39);
    $valores = [
      "APP_ENV" => "production", "APP_DEBUG" => "false", "APP_URL" => getenv("URL_SITIO") ?: "https://rosaacevedo.com",
      "APP_KEY" => getenv("NUEVA_APP_KEY"), "LOG_LEVEL" => "warning",
      "DB_HOST" => "localhost", "DB_DATABASE" => getenv("DB_NOMBRE"), "DB_USERNAME" => getenv("DB_USUARIO"), "DB_PASSWORD" => getenv("DB_CLAVE"),
      "SESSION_SECURE_COOKIE" => "true", "RESPONSE_CACHE_ENABLED" => "true", "DOS_FACTORES" => "true",
      "CELULAR_HMAC_KEY" => getenv("NUEVO_HMAC"),
    ];
    $lineas = file($origen, FILE_IGNORE_NEW_LINES);
    foreach ($lineas as &$linea) {
      foreach ($valores as $clave => $valor) {
        if (str_starts_with($linea, $clave."=")) {
          $linea = $clave."=".$q((string) $valor);
        }
      }
    }
    file_put_contents($destino, implode(PHP_EOL, $lineas).PHP_EOL);
  ' "$DIR/.env.example" "$ENV.nuevo"
  mv "$ENV.nuevo" "$ENV"
  chmod 600 "$ENV"
  echo "Se creó $ENV con llaves nuevas. No lo borres: ahí están las llaves que cifran los celulares."
fi

# 2. Clave de acceso temporal (sitio cerrado mientras la campaña aprueba los textos). Se actualiza en cada
#    despliegue desde los secretos SITIO_USUARIO y SITIO_CLAVE; si se borran en GitHub, el sitio queda abierto.
"$PHP" -r '
  [$_, $archivo] = $argv;
  $lineas = array_values(array_filter(file($archivo, FILE_IGNORE_NEW_LINES), fn ($l) => ! str_starts_with($l, "PRUEBAS_USUARIO=") && ! str_starts_with($l, "PRUEBAS_CLAVE=")));
  $q = fn ($v) => str_contains($v, chr(39)) ? chr(34).addcslashes($v, chr(34).chr(92)).chr(34) : chr(39).$v.chr(39);
  $usuario = (string) getenv("SITIO_USUARIO");
  $clave = (string) getenv("SITIO_CLAVE");
  if ($usuario !== "" && $clave !== "") {
    $lineas[] = "PRUEBAS_USUARIO=".$q($usuario);
    $lineas[] = "PRUEBAS_CLAVE=".$q($clave);
    echo "Sitio cerrado con usuario y contraseña (secretos SITIO_USUARIO y SITIO_CLAVE).".PHP_EOL;
  } else {
    echo "Sitio abierto al público.".PHP_EOL;
  }
  file_put_contents($archivo, implode(PHP_EOL, $lineas).PHP_EOL);
' "$ENV"

# 3. public_html → current/public. Lo que hubiera antes (la página por defecto de Hostinger) se guarda aparte.
if [ -n "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ]; then
  if [ -e "$PUBLIC_HTML" ]; then
    mv "$PUBLIC_HTML" "$PUBLIC_HTML.respaldo-$(date +%Y%m%d-%H%M%S)"
    echo "La carpeta public_html anterior quedó como respaldo junto a ella."
  fi
  mkdir -p "$(dirname "$PUBLIC_HTML")"
  ln -sfn "$RUTA/current/public" "$PUBLIC_HTML"
  echo "public_html ahora apunta al sitio."
fi

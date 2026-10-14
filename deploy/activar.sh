#!/usr/bin/env bash
# Activa una versión subida a releases/ en Hostinger (sección 06).
# Uso: bash activar.sh /home/uXXXX/rosaacevedo 20261024-190000-abc1234
# Variables opcionales: PHP_BIN, PANEL_CORREO y PANEL_CLAVE (primer usuario administrador).
set -euo pipefail

RUTA="$1"
VERSION="$2"
DIR="$RUTA/releases/$VERSION"
PHP="$(bash "$DIR/deploy/php.sh")"
CONSERVAR=5

cd "$DIR"

# Archivos compartidos entre versiones.
ln -sfn "$RUTA/shared/.env" "$DIR/.env"
rm -rf "$DIR/storage"
ln -sfn "$RUTA/shared/storage" "$DIR/storage"
ln -sfn "$RUTA/shared/storage/app/public" "$DIR/public/storage"

"$PHP" -v | head -1
"$PHP" artisan down --retry=15 || true
# Si algo falla de aquí en adelante, el sitio no se queda en mantenimiento: sigue la versión anterior.
trap '"$PHP" artisan up >/dev/null 2>&1 || true' ERR
"$PHP" artisan migrate --force

# Datos base (comunas, ejes, temas, redes, menú, roles, políticas y páginas): solo la primera vez, para no
# volver a crear lo que el equipo borre o cambie desde el panel.
if [ ! -f "$RUTA/shared/.datos-base-cargados" ]; then
  "$PHP" artisan db:seed --force
  touch "$RUTA/shared/.datos-base-cargados"
fi

# Primer usuario administrador, desde los secretos PANEL_CORREO y PANEL_CLAVE. Si ya hay usuarios, no hace nada.
if [ -n "${PANEL_CORREO:-}" ] && [ -n "${PANEL_CLAVE:-}" ]; then
  "$PHP" artisan usuarios:crear "$PANEL_CORREO" --nombre="Administrador" --rol=administrador --clave-del-entorno --si-no-hay-usuarios \
    || echo "AVISO: no se pudo crear el usuario del panel. Revisa que PANEL_CLAVE tenga 12 caracteres con mayúsculas, minúsculas y números."
fi
"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan event:cache
"$PHP" artisan filament:optimize

# Cambio atómico del enlace current.
ln -sfn "$DIR" "$RUTA/current.nuevo"
mv -Tf "$RUTA/current.nuevo" "$RUTA/current"

cd "$RUTA/current"
"$PHP" artisan responsecache:clear || true
"$PHP" artisan queue:restart || true
"$PHP" artisan up

# Conserva las últimas 5 versiones para revertir con un solo cambio de enlace.
ls -1dt "$RUTA"/releases/*/ | tail -n +$((CONSERVAR + 1)) | xargs -r rm -rf

echo "Versión $VERSION activa."

#!/usr/bin/env bash
# Imprime la ruta de un PHP 8.3 o superior en el servidor. En Hostinger el «php» de la consola puede ser otra
# versión; las alternativas viven en /opt/alt/phpXX. PHP_BIN (secreto HOSTINGER_PHP_BIN) manda si existe.
set -euo pipefail

for candidato in "${PHP_BIN:-}" /opt/alt/php83/usr/bin/php /opt/alt/php84/usr/bin/php php8.3 php83 php; do
  [ -n "$candidato" ] || continue
  ruta="$(command -v "$candidato" 2>/dev/null || true)"
  [ -n "$ruta" ] || continue
  if "$ruta" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
    echo "$ruta"
    exit 0
  fi
done

echo "ERROR: no encontré PHP 8.3 en el servidor. En hPanel → Avanzado → Configuración de PHP elige 8.3." >&2
exit 1

#!/usr/bin/env bash
# Prepara la estructura en Hostinger la primera vez (por SSH). Uso:
#   bash primera-vez.sh /home/uXXXX/rosaacevedo /home/uXXXX/domains/rosaacevedo.com/public_html
set -euo pipefail
RUTA="$1"
PUBLIC_HTML="$2"

mkdir -p "$RUTA/releases" "$RUTA/shared/storage"/{app/public,framework/{cache/data,sessions,views},logs}

if [ ! -f "$RUTA/shared/.env" ]; then
  echo "Crea $RUTA/shared/.env a partir de .env.example (APP_KEY, DB_*, CELULAR_HMAC_KEY…) y vuelve a ejecutar."
  exit 1
fi

# public_html → current/public. Si el panel no permite el enlace simbólico, ver deploy/public_html.htaccess.
if [ -d "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ]; then
  mv "$PUBLIC_HTML" "$PUBLIC_HTML.respaldo-$(date +%Y%m%d)"
fi
ln -sfn "$RUTA/current/public" "$PUBLIC_HTML"

chmod -R 775 "$RUTA/shared/storage"
echo "Listo. Programa el cron en hPanel (Avanzado → Cron Jobs):"
echo "* * * * * cd $RUTA/current && ${PHP_BIN:-php} artisan schedule:run >> /dev/null 2>&1"

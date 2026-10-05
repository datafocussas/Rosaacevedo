#!/usr/bin/env bash
# Activa una versión subida a releases/ en Hostinger (sección 06).
# Uso: PHP_BIN=/opt/alt/php83/usr/bin/php bash activar.sh /home/uXXXX/rosaacevedo 20261024-190000-abc1234
set -euo pipefail

RUTA="$1"
VERSION="$2"
PHP="${PHP_BIN:-php}"
DIR="$RUTA/releases/$VERSION"
CONSERVAR=5

cd "$DIR"

# Archivos compartidos entre versiones.
ln -sfn "$RUTA/shared/.env" "$DIR/.env"
rm -rf "$DIR/storage"
ln -sfn "$RUTA/shared/storage" "$DIR/storage"
ln -sfn "$RUTA/shared/storage/app/public" "$DIR/public/storage"

"$PHP" -v | head -1
"$PHP" artisan down --retry=15 || true
"$PHP" artisan migrate --force
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

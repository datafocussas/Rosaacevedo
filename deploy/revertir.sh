#!/usr/bin/env bash
# Vuelve a la versión anterior cambiando el enlace current. Uso: bash revertir.sh /home/uXXXX/rosaacevedo
# Ojo: no revierte migraciones. Si la versión nueva migró la base, revisar antes de revertir.
set -euo pipefail
RUTA="$1"
PHP="${PHP_BIN:-php}"
ACTUAL=$(readlink -f "$RUTA/current")
ANTERIOR=$(ls -1dt "$RUTA"/releases/*/ | sed 's#/$##' | grep -v "^$ACTUAL$" | head -1)
[ -n "$ANTERIOR" ] || { echo "No hay versión anterior."; exit 1; }
ln -sfn "$ANTERIOR" "$RUTA/current.nuevo" && mv -Tf "$RUTA/current.nuevo" "$RUTA/current"
cd "$RUTA/current" && "$PHP" artisan config:cache && "$PHP" artisan responsecache:clear || true
echo "Activa: $ANTERIOR"

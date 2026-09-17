#!/usr/bin/env bash
# Dijalankan DI SERVER Hostinger oleh workflow deploy, lewat:
#   ssh ... 'bash -s' < deploy/remote-deploy.sh
#
# Dipisah dari deploy.yml supaya bisa dibaca dan diuji manual:
#   ssh -p 65002 uXXXX@srv1981.hstgr.io 'bash -s' < deploy/remote-deploy.sh
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-$HOME/domains/j-corp.id/public_html}"
PHP="${PHP_BIN:-php}"

cd "$DEPLOY_PATH"

# storage/ sengaja tidak ikut rsync supaya upload pengguna dan log tidak terhapus.
# Yang perlu dipastikan di sini hanya kerangka direktorinya ada.
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public
chmod -R 775 storage bootstrap/cache

# php artisan storage:link memakai symlink(), yang masuk disable_functions di
# shared hosting ini. Shell tidak kena batasan itu, jadi symlink dibuat di sini.
ln -sfn "$DEPLOY_PATH/storage/app/public" "$DEPLOY_PATH/public/storage"

"$PHP" artisan migrate --force --no-interaction

# Konversi foto client (truk Ayodya dll.) ke WebP di storage.
# Perintah ini idempoten — aman dijalankan ulang di setiap deploy.
"$PHP" artisan jcorp:import-client-media

# Seed konten client (katalog, profil) ke database.
# ClientContentSeeder idempoten: hanya mengisi/memperbarui data yang
# masih kosong atau masih berisi teks contoh.
"$PHP" artisan db:seed --class=ClientContentSeeder --force --no-interaction

"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "Deploy selesai di $DEPLOY_PATH"

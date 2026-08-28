#!/usr/bin/env bash
# Jalankan di SSH Linux dengan bash; proses dump/git tidak memakai PHP exec().
set -Eeuo pipefail
umask 077

script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
project=$(cd -- "$script_dir/../.." && pwd -P)
destination=''
label='hostinger-testing'
php_binary='php'
dump_binary='mysqldump'
check=false
confirmed=false
snapshot=''

usage() {
    printf '%s\n' 'bash deploy/backup/backup.sh --destination /absolute/private/path [--check | --confirm-no-writes]' \
        '  --project PATH --label local|hostinger-testing --php PHP_BINARY --dump DUMP_BINARY'
}
while (( $# )); do
    case "$1" in
        --destination|--project|--label|--php|--dump)
            (( $# >= 2 )) || { usage; exit 1; }
            case "$1" in
                --destination) destination=$2 ;; --project) project=$2 ;; --label) label=$2 ;;
                --php) php_binary=$2 ;; --dump) dump_binary=$2 ;;
            esac
            shift 2 ;;
        --check) check=true; shift ;;
        --confirm-no-writes) confirmed=true; shift ;;
        --help) usage; exit 0 ;;
        *) usage; exit 1 ;;
    esac
done
[[ -n "$destination" ]] || { usage; exit 1; }
for binary in "$php_binary" "$dump_binary" git; do
    command -v "$binary" >/dev/null || { printf '%s\n' 'Dependensi CLI belum tersedia.' >&2; exit 1; }
done
[[ $("$php_binary" -r 'echo PHP_OS_FAMILY;') != 'Windows' ]] || {
    printf '%s\n' 'Windows harus memakai backup.ps1 untuk menerapkan ACL privat.' >&2; exit 1;
}

cleanup() {
    # Hanya berkas credential milik snapshot run ini; tidak ada penghapusan rekursif/retensi otomatis.
    if [[ -n "$snapshot" && -f "$snapshot/client.cnf" ]]; then
        rm -- "$snapshot/client.cnf"
    fi
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

helper="$script_dir/tool.php"
project=$(cd -- "$project" && pwd -P)
name="jcorp-snapshot-$(date -u +%Y%m%dT%H%M%SZ)-$("$php_binary" -r 'echo bin2hex(random_bytes(4));')"
proposed="${destination%/}/$label/$name"
"$php_binary" "$helper" preflight "$project" "$proposed" "$label"
git_root=$(git -C "$project" rev-parse --show-toplevel)
[[ $(cd -- "$git_root" && pwd -P) == "$project" ]] || {
    printf '%s\n' 'Project harus tepat berada di root repository Git.' >&2; exit 1;
}
commit=$(git -C "$project" rev-parse --verify HEAD)
version=$("$dump_binary" --version)
is_maria=false
[[ "$version" != *MariaDB* && "$version" != *mariadb* ]] || is_maria=true
[[ "$version" == *mysqldump* || "$is_maria" == true ]] || { printf '%s\n' 'Client dump tidak dikenal.' >&2; exit 1; }
"$php_binary" "$helper" check-client "$project" "$is_maria"
printf 'Commit: %s\n' "$commit"
if [[ "$check" == true ]]; then
    printf '%s\n' 'Pemeriksaan selesai. Tidak membuat snapshot atau mengubah data.'
    exit 0
fi
[[ "$confirmed" == true ]] || {
    printf '%s\n' 'Hentikan perubahan konten/upload/migration/worker dahulu, lalu tambahkan --confirm-no-writes.' >&2
    exit 1
}
# Tujuan canonical dari helper, bukan hasil eval/source .env.
snapshot=$("$php_binary" "$helper" destination "$project" "$proposed")
[[ ! -e "$snapshot" && ! -L "$snapshot" ]] || { printf '%s\n' 'Folder snapshot sudah ada.' >&2; exit 1; }
mkdir -p -- "$(dirname -- "$snapshot")"
mkdir -m 700 -- "$snapshot"
"$php_binary" "$helper" prepare "$project" "$snapshot" "$label"
database=$("$php_binary" "$helper" database-name "$project")
dump_options=("--defaults-file=$snapshot/client.cnf")
if [[ "$is_maria" == false ]]; then
    dump_options+=(--no-login-paths --set-gtid-purged=OFF --column-statistics=0)
fi
dump_options+=(--single-transaction --quick --skip-lock-tables --hex-blob --no-tablespaces --routines --events --triggers "--result-file=$snapshot/database.sql" "$database")
if ! "$dump_binary" "${dump_options[@]}" 2>"$snapshot/dump-error.log"; then
    printf '%s\n' 'Dump gagal; periksa dump-error.log secara privat. Snapshot tidak boleh dipakai.' >&2
    exit 1
fi
rm -- "$snapshot/client.cnf"
git -C "$project" archive --format=zip --output="$snapshot/source.zip" "$commit"
git -C "$project" status --porcelain=v1 --untracked-files=normal >"$snapshot/git-status.txt"
git -C "$project" diff --binary --no-ext-diff "$commit" -- >"$snapshot/git-tracked.patch"
"$php_binary" "$helper" finish "$snapshot" "$commit"
"$php_binary" "$helper" verify "$snapshot"
if [[ -s "$snapshot/git-status.txt" ]]; then
    printf '%s\n' 'PERINGATAN: working tree kotor; source untracked tidak ada dalam source.zip. Periksa git-status.txt.'
fi
printf 'Snapshot: %s\n' "$snapshot"
printf '%s\n' 'Belum dienkripsi. Salin ke perangkat/lokasi lain yang terenkripsi; jangan unggah ke Git/folder website.'

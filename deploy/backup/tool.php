<?php

declare(strict_types=1);

// Jangan pernah menyediakan backup lewat route HTTP, termasuk jika deployment salah root.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/Backup.php';

use JCorp\Backup\Backup;
use JCorp\Backup\BackupException;

umask(0077);
ini_set('display_errors', '0');
set_error_handler(static function (int $severity, string $message): never {
    // Pesan PHP/driver bisa memuat konfigurasi; jangan meneruskannya ke terminal/log CI.
    throw new BackupException('Operasi berkas/konfigurasi gagal. Periksa izin dan dependensi; detail sensitif tidak ditampilkan.');
});

try {
    $command = $argv[1] ?? 'help';
    switch ($command) {
        case 'preflight':
            if ($argc !== 5) {
                throw new BackupException('preflight memerlukan PROJECT DESTINATION LABEL.');
            }
            echo json_encode(Backup::preflight($argv[2], $argv[3], $argv[4]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
            break;
        case 'prepare':
            if ($argc !== 5) {
                throw new BackupException('prepare memerlukan PROJECT SNAPSHOT LABEL.');
            }
            Backup::prepare($argv[2], $argv[3], $argv[4]);
            break;
        case 'database-name':
            if ($argc !== 3) {
                throw new BackupException('database-name memerlukan PROJECT.');
            }
            echo Backup::settings(Backup::project($argv[2]))['database']['database'];
            break;
        case 'destination':
            if ($argc !== 4) {
                throw new BackupException('destination memerlukan PROJECT DESTINATION.');
            }
            echo Backup::destination($argv[3], Backup::project($argv[2]));
            break;
        case 'check-client':
            if ($argc !== 4 || ! in_array($argv[3], ['true', 'false'], true)) {
                throw new BackupException('check-client memerlukan PROJECT true|false.');
            }
            $settings = Backup::settings(Backup::project($argv[2]));
            $version = Backup::queryValue(Backup::connect($settings['database']), 'SELECT VERSION()');
            if (str_contains((string) $version, 'MariaDB') !== ($argv[3] === 'true')) {
                throw new BackupException('Gunakan client dump yang sesuai keluarga server (MySQL/MariaDB).');
            }
            break;
        case 'extraction-target':
            if ($argc !== 4) {
                throw new BackupException('extraction-target memerlukan SNAPSHOT TARGET.');
            }
            echo Backup::extractionTarget($argv[2], $argv[3]);
            break;
        case 'finish':
            if ($argc !== 4) {
                throw new BackupException('finish memerlukan SNAPSHOT COMMIT.');
            }
            Backup::finish($argv[2], $argv[3]);
            echo 'Snapshot selesai dan lolos pemeriksaan integritas.'.PHP_EOL;
            break;
        case 'verify':
            if ($argc !== 3) {
                throw new BackupException('verify memerlukan SNAPSHOT.');
            }
            $metadata = Backup::verify($argv[2]);
            echo 'Integritas OK. Label: '.$metadata['label'].'; commit: '.$metadata['git_commit'].PHP_EOL;
            echo 'Ini bukan bukti restore database berhasil. Backup memuat rahasia dan BELUM dienkripsi.'.PHP_EOL;
            break;
        case 'extract':
            if ($argc !== 4) {
                throw new BackupException('extract memerlukan SNAPSHOT TARGET.');
            }
            Backup::extract($argv[2], $argv[3]);
            echo 'Ekstraksi terisolasi selesai; tidak ada SQL yang diimpor atau aplikasi yang diubah.'.PHP_EOL;
            break;
        default:
            echo "Helper internal backup. Gunakan backup.ps1/backup.sh; verifikasi: php deploy/backup/tool.php verify PATH_SNAPSHOT\n";
            exit($command === 'help' ? 0 : 1);
    }
} catch (PDOException) {
    fwrite(STDERR, "Koneksi/pemeriksaan database gagal. Periksa MySQL aktif, nama database, dan hak akses secara lokal; jangan kirim password ke chat.\n");
    exit(1);
} catch (Throwable $error) {
    $message = $error instanceof BackupException ? $error->getMessage() : 'Konfigurasi/metadata tidak valid; detail sensitif tidak ditampilkan.';
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
}

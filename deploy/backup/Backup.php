<?php

declare(strict_types=1);

namespace JCorp\Backup;

use Dotenv\Dotenv;
use Illuminate\Support\ConfigurationUrlParser;
use PDO;
use PDOStatement;
use RuntimeException;
use ZipArchive;

/** Hanya exception ini yang aman ditampilkan kepada operator. */
final class BackupException extends RuntimeException {}

/** Operasi berkas saja; proses mysqldump/git tetap dijalankan oleh shell. */
final class Backup
{
    private const PAYLOADS = ['database.sql', 'files.zip', 'source.zip', 'git-status.txt', 'git-tracked.patch'];

    public static function project(string $path): string
    {
        $root = realpath($path);
        if ($root === false || ! is_file($root.'/artisan') || ! is_file($root.'/composer.json')) {
            throw new BackupException('Folder project Laravel tidak valid.');
        }

        return str_replace('\\', '/', $root);
    }

    /** Menolak traversal, lokasi publik, dan backup yang masuk ke project sendiri. */
    public static function destination(string $path, string $project): string
    {
        $resolvedProject = realpath($project);
        $project = str_replace('\\', '/', $resolvedProject !== false ? $resolvedProject : $project);
        $path = str_replace('\\', '/', $path);
        self::need((bool) preg_match('~^(?:[A-Za-z]:/|/(?!/))~', $path), 'Tujuan harus berupa path absolut lokal.');
        self::need(! preg_match('~[\x00-\x1f]|(?:^|/)\.\.(?:/|$)~', $path), 'Path tujuan tidak aman.');
        $path = rtrim($path, '/');
        $tail = [];
        $existing = $path;
        while (! file_exists($existing)) {
            self::need(! is_link($existing), 'Symlink rusak tidak boleh menjadi tujuan.');
            $tail[] = basename($existing);
            $parent = dirname($existing);
            self::need($parent !== $existing, 'Parent tujuan tidak ditemukan.');
            $existing = $parent;
        }
        self::need(is_dir($existing), 'Parent tujuan bukan direktori.');
        $resolved = str_replace('\\', '/', (string) realpath($existing));
        if ($tail !== []) {
            $resolved = rtrim($resolved, '/').'/'.implode('/', array_reverse($tail));
        }
        self::need(! self::inside($resolved, $project) && ! self::inside($project, $resolved), 'Backup/pemulihan harus di luar project, bukan parent project.');
        foreach ([$path, $resolved] as $candidate) {
            self::need(! preg_match('~(?:^|/)(?:public_html|public|htdocs|www|wwwroot)(?:/|$)~i', $candidate), 'Backup/pemulihan tidak boleh berada di folder publik.');
        }

        return $resolved;
    }

    public static function inside(string $path, string $root): bool
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        if (PHP_OS_FAMILY === 'Windows') {
            $path = strtolower($path);
            $root = strtolower($root);
        }

        return $path === $root || str_starts_with($path, $root.'/');
    }

    /** @return array<string, mixed> */
    public static function settings(string $project): array
    {
        self::regularFile($project.'/.env', $project);
        $env = Dotenv::createArrayBacked($project)->load();
        self::need(! empty($env['APP_KEY']), 'APP_KEY belum tersedia; backup konfigurasi tidak lengkap.');
        foreach (['APP_KEY', 'DB_CONNECTION', 'DB_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_SOCKET'] as $key) {
            $external = getenv($key);
            self::need($external === false || $external === ($env[$key] ?? ''), 'Ada konfigurasi environment eksternal yang berbeda dari .env. Tinjau koneksi aktif dahulu.');
        }
        $db = (new ConfigurationUrlParser)->parseConfiguration([
            'driver' => $env['DB_CONNECTION'] ?? '',
            'url' => $env['DB_URL'] ?? null,
            'host' => $env['DB_HOST'] ?? '127.0.0.1',
            'port' => $env['DB_PORT'] ?? '3306',
            'database' => $env['DB_DATABASE'] ?? '',
            'username' => $env['DB_USERNAME'] ?? '',
            'password' => $env['DB_PASSWORD'] ?? '',
            'unix_socket' => $env['DB_SOCKET'] ?? '',
        ]);
        foreach (['driver', 'host', 'port', 'database', 'username', 'password', 'unix_socket'] as $field) {
            self::need(is_scalar($db[$field] ?? null), 'Nilai konfigurasi database tidak valid.');
        }
        self::need(in_array($db['driver'], ['mysql', 'mariadb'], true), 'Toolkit ini khusus MySQL/MariaDB; koneksi lain ditolak.');
        self::need((bool) preg_match('/^[A-Za-z0-9_]+$/D', (string) $db['database']), 'Nama database harus eksplisit dan hanya berisi huruf, angka, underscore.');
        self::need(in_array($db['host'], ['127.0.0.1', 'localhost', '::1'], true), 'Jalankan backup di mesin database berada; koneksi non-loopback belum didukung.');
        self::need(ctype_digit((string) $db['port']) && (int) $db['port'] > 0 && (int) $db['port'] <= 65535, 'Port database tidak valid.');
        self::need(empty($env['MYSQL_ATTR_SSL_CA']) && empty($db['options']), 'Opsi TLS/custom database perlu ditinjau sebelum menggunakan toolkit ini.');
        self::need(! preg_match('/[;\x00-\x1f]/', (string) $db['unix_socket']), 'Path socket database tidak valid.');

        // Jangan mengambil .env yang berbeda dari konfigurasi yang sedang dipakai aplikasi.
        if (is_file($project.'/bootstrap/cache/config.php')) {
            $cache = require $project.'/bootstrap/cache/config.php';
            $connection = $cache['database']['connections'][$cache['database']['default'] ?? ''] ?? [];
            $active = (new ConfigurationUrlParser)->parseConfiguration($connection);
            foreach (['driver', 'host', 'port', 'database', 'username', 'password', 'unix_socket'] as $key) {
                self::need((string) ($active[$key] ?? '') === (string) ($db[$key] ?? ''), 'Config cache dan .env berbeda. Periksa konfigurasi aktif dahulu; tidak ada cache yang diubah.');
            }
            self::need(($cache['app']['key'] ?? '') === $env['APP_KEY'], 'APP_KEY pada cache berbeda dari .env.');
            self::need(empty($active['options']), 'Opsi koneksi cached perlu ditinjau dahulu.');
            $publicRoot = str_replace('\\', '/', (string) ($cache['filesystems']['disks']['public']['root'] ?? ''));
            self::need($publicRoot === $project.'/storage/app/public', 'Storage publik custom belum didukung.');
        }

        return ['database' => $db, 'app_env' => $env['APP_ENV'] ?? 'unknown'];
    }

    /** @param array<string, mixed> $db */
    public static function connect(array $db): PDO
    {
        $endpoint = empty($db['unix_socket'])
            ? 'host='.$db['host'].';port='.$db['port']
            : 'unix_socket='.$db['unix_socket'];

        return new PDO('mysql:'.$endpoint.';dbname='.$db['database'].';charset=utf8mb4', (string) $db['username'], (string) $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    /** @return array<string, mixed> */
    public static function preflight(string $project, string $destination, string $label): array
    {
        self::need(in_array($label, ['local', 'hostinger-testing'], true), 'Label harus local atau hostinger-testing.');
        self::need(extension_loaded('zip') && extension_loaded('pdo_mysql'), 'PHP CLI memerlukan ekstensi zip dan pdo_mysql.');
        $project = self::project($project);
        $destination = self::destination($destination, $project);
        $settings = self::settings($project);
        $db = self::connect($settings['database']);
        $query = $db->prepare('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ? ORDER BY TABLE_NAME');
        $query->execute([$settings['database']['database'], 'BASE TABLE']);
        $tables = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
            self::need($row['ENGINE'] === 'InnoDB', 'Ada tabel non-InnoDB; snapshot konsisten memerlukan prosedur khusus.');
            $name = $row['TABLE_NAME'];
            $tables[$name] = (int) self::queryValue($db, 'SELECT COUNT(*) FROM `'.str_replace('`', '``', $name).'`');
        }
        foreach (['businesses', 'catalog_items', 'portfolio_items', 'users', 'migrations'] as $required) {
            self::need(array_key_exists($required, $tables), 'Database bukan skema J-Corporate yang lengkap.');
        }
        foreach (['storage/app/public', 'public/build', 'public/images'] as $required) {
            self::need(is_dir($project.'/'.$required), 'Folder wajib belum tersedia: '.$required);
        }
        foreach (['public/.htaccess', 'public/index.php', 'public/build/manifest.json'] as $required) {
            self::regularFile($project.'/'.$required, $project);
        }

        return [
            'format' => 1,
            'label' => $label,
            'created_at_utc' => gmdate('c'),
            'project' => $project,
            'destination' => $destination,
            'app_env' => $settings['app_env'],
            'database' => $settings['database']['database'],
            'database_version' => self::queryValue($db, 'SELECT VERSION()'),
            'table_counts_before_dump' => $tables,
            'root_htaccess_present' => is_file($project.'/.htaccess'),
            'consistency' => 'Operator harus menghentikan perubahan konten, upload, migration, dan worker selama backup.',
            'encrypted' => false,
        ];
    }

    public static function prepare(string $project, string $destination, string $label): void
    {
        $metadata = self::preflight($project, $destination, $label);
        $destination = $metadata['destination'];
        self::need(is_dir($destination) && count(scandir($destination) ?: []) === 2, 'Folder snapshot harus baru dan kosong.');
        self::privatePermissions($destination, true);
        self::write($destination.'/INCOMPLETE', 'Backup belum selesai; jangan digunakan untuk restore.');
        self::writeJson($destination.'/pending.json', $metadata);
        $db = self::settings($metadata['project'])['database'];
        self::write($destination.'/client.cnf', self::clientOptions($db));
    }

    /**
     * Password hanya masuk berkas sementara privat, bukan argumen proses/history.
     *
     * @param  array<string, mixed>  $db
     */
    public static function clientOptions(array $db): string
    {
        $options = ['host' => $db['host'], 'port' => $db['port'], 'user' => $db['username'], 'password' => $db['password'], 'default-character-set' => 'utf8mb4'];
        if (! empty($db['unix_socket'])) {
            $options['socket'] = $db['unix_socket'];
        }
        $text = "[client]\n";
        foreach ($options as $key => $value) {
            $escaped = strtr((string) $value, ['\\' => '\\\\', '"' => '\\"', "\n" => '\\n', "\r" => '\\r', "\t" => '\\t']);
            $text .= $key.'="'.$escaped."\"\n";
        }

        return $text;
    }

    public static function finish(string $destination, string $commit): void
    {
        self::need((bool) preg_match('/^[a-f0-9]{40,64}$/D', $commit), 'Commit Git tidak valid.');
        self::need(is_file($destination.'/INCOMPLETE'), 'Snapshot tidak dalam tahap pembuatan.');
        self::need(! file_exists($destination.'/COMPLETE'), 'Snapshot sudah memiliki marker COMPLETE.');
        self::need(! file_exists($destination.'/client.cnf'), 'Hapus credential sementara sebelum menyelesaikan snapshot.');
        $metadata = self::readJson($destination.'/pending.json');
        $project = self::project($metadata['project']);
        self::destination($destination, $project);
        foreach (['database.sql', 'source.zip'] as $file) {
            self::need(is_file($destination.'/'.$file) && filesize($destination.'/'.$file) > 0, 'Payload wajib kosong/tidak tersedia: '.$file);
        }

        $files = [];
        foreach (['.env', '.htaccess', 'public/.htaccess', 'public/index.php'] as $file) {
            if (file_exists($project.'/'.$file)) {
                self::regularFile($project.'/'.$file, $project);
                $files[$file] = $project.'/'.$file;
            }
        }
        foreach (['storage/app/public', 'storage/app/private', 'public/build', 'public/images'] as $directory) {
            if (is_dir($project.'/'.$directory)) {
                self::collect($project, $directory, $files);
            }
        }
        ksort($files);
        $inventory = self::archive($destination.'/files.zip', $files);
        self::zipInventory($destination.'/source.zip');
        $metadata['git_commit'] = $commit;
        $metadata['git_dirty'] = trim((string) file_get_contents($destination.'/git-status.txt')) !== '';
        $metadata['code_scope'] = 'source.zip = commit Git; patch = perubahan tracked; source untracked tidak termasuk. files.zip menyimpan konfigurasi, storage, build dan images aktual.';
        $metadata['files'] = $inventory;
        $metadata['artifacts'] = [];
        foreach (self::PAYLOADS as $file) {
            self::regularFile($destination.'/'.$file, $destination);
            self::privatePermissions($destination.'/'.$file);
            $metadata['artifacts'][$file] = ['sha256' => hash_file('sha256', $destination.'/'.$file), 'bytes' => filesize($destination.'/'.$file)];
        }
        self::writeJson($destination.'/manifest.json', $metadata);
        self::verifyPayloads($destination, $metadata);
        unlink($destination.'/pending.json');
        unlink($destination.'/INCOMPLETE');
        // Publish marker paling akhir; crash sebelumnya tetap tidak dianggap lengkap.
        self::write($destination.'/COMPLETE', hash_file('sha256', $destination.'/manifest.json')."\n");
    }

    /** @return array<string, mixed> */
    public static function verify(string $destination): array
    {
        self::need(! file_exists($destination.'/INCOMPLETE') && ! file_exists($destination.'/client.cnf'), 'Snapshot masih belum selesai/credential sementara belum dibersihkan.');
        self::need(is_file($destination.'/COMPLETE') && is_file($destination.'/manifest.json'), 'Snapshot belum lengkap: marker COMPLETE/manifest tidak ada.');
        self::regularFile($destination.'/COMPLETE', $destination);
        self::regularFile($destination.'/manifest.json', $destination);
        self::need(hash_equals(trim((string) file_get_contents($destination.'/COMPLETE')), (string) hash_file('sha256', $destination.'/manifest.json')), 'Checksum manifest tidak cocok.');
        $metadata = self::readJson($destination.'/manifest.json');
        self::verifyPayloads($destination, $metadata);

        return $metadata;
    }

    /** @param array<string, mixed> $metadata */
    private static function verifyPayloads(string $destination, array $metadata): void
    {
        self::need(($metadata['format'] ?? null) === 1, 'Format snapshot tidak didukung.');
        self::need(array_keys($metadata['artifacts'] ?? []) === self::PAYLOADS, 'Daftar payload tidak lengkap/tidak dikenal.');
        foreach ($metadata['artifacts'] as $name => $expected) {
            self::regularFile($destination.'/'.$name, $destination);
            self::need(filesize($destination.'/'.$name) === $expected['bytes'] && hash_equals($expected['sha256'], (string) hash_file('sha256', $destination.'/'.$name)), 'Checksum/ukuran payload berbeda: '.$name);
        }
        foreach (['.env', 'public/.htaccess', 'public/index.php', 'public/build/manifest.json'] as $required) {
            self::need(isset($metadata['files'][$required]), 'Arsip kehilangan berkas wajib: '.$required);
        }
        self::need(self::zipInventory($destination.'/files.zip') === $metadata['files'], 'Isi arsip berbeda dari inventaris.');
        self::zipInventory($destination.'/source.zip');
    }

    /** Hanya mengekstrak ke folder kosong. Tidak mengimpor SQL atau menyalin ke aplikasi aktif. */
    public static function extract(string $destination, string $target): void
    {
        $metadata = self::verify($destination);
        $target = self::extractionTarget($destination, $target);
        self::need(is_dir($target) && count(scandir($target) ?: []) === 2, 'Buat direktori privat kosong terlebih dahulu; target berisi data ditolak.');
        self::privatePermissions($target, true);
        $zip = new ZipArchive;
        self::need($zip->open($destination.'/files.zip', ZipArchive::RDONLY) === true, 'Arsip tidak bisa dibaca.');
        try {
            foreach ($metadata['files'] as $name => $expected) {
                self::entryName($name);
                $file = $target.'/'.$name;
                if (! is_dir(dirname($file))) {
                    self::need(mkdir(dirname($file), 0700, true), 'Direktori ekstraksi gagal dibuat.');
                }
                $input = $zip->getStream($name);
                if ($input === false) {
                    throw new BackupException('Berkas ekstraksi gagal dibuka.');
                }
                $output = false;
                try {
                    $output = fopen($file, 'xb');
                    if ($output === false) {
                        throw new BackupException('Target ekstraksi gagal dibuka.');
                    }
                    self::need(stream_copy_to_stream($input, $output) === $expected['bytes'], 'Ekstraksi berkas tidak lengkap.');
                } finally {
                    fclose($input);
                    if (is_resource($output)) {
                        fclose($output);
                    }
                }
                self::privatePermissions($file);
                self::need(hash_file('sha256', $file) === $expected['sha256'], 'Hasil ekstraksi gagal verifikasi.');
            }
        } finally {
            $zip->close();
        }
        foreach (['storage/app/public', 'storage/app/private'] as $directory) {
            if (! is_dir($target.'/'.$directory)) {
                self::need(mkdir($target.'/'.$directory, 0700, true), 'Folder storage kosong gagal dibuat.');
            }
        }
    }

    public static function extractionTarget(string $destination, string $target): string
    {
        $metadata = self::verify($destination);
        $destination = (string) realpath($destination);
        // Original project mungkin berasal dari OS lain, tetapi source tetap dilarang bila path sejenis.
        $target = self::destination($target, $metadata['project']);
        self::need(! self::inside($target, $destination) && ! self::inside($destination, $target), 'Target ekstraksi harus terpisah dari snapshot.');

        return $target;
    }

    /** @param array<string, string> $files */
    private static function collect(string $project, string $relative, array &$files): void
    {
        $directory = $project.'/'.$relative;
        $actual = (string) realpath($directory);
        self::need(! is_link($directory) && self::inside($actual, $directory) && self::inside($directory, $actual), 'Symlink/junction pada sumber backup ditolak.');
        foreach (new \DirectoryIterator($directory) as $item) {
            if ($item->isDot()) {
                continue;
            }
            $name = $relative.'/'.$item->getFilename();
            self::entryName($name);
            self::need(! $item->isLink(), 'Symlink di dalam sumber backup ditolak.');
            if ($item->isDir()) {
                self::collect($project, $name, $files);
            } else {
                self::regularFile($project.'/'.$name, $project);
                $files[$name] = $project.'/'.$name;
            }
        }
    }

    /**
     * @param  array<string, string>  $files
     * @return array<string, array{sha256: string, bytes: int}>
     */
    public static function archive(string $path, array $files): array
    {
        $zip = new ZipArchive;
        self::need($zip->open($path, ZipArchive::CREATE | ZipArchive::EXCL) === true, 'Arsip sudah ada atau tidak dapat dibuat.');
        $before = [];
        foreach ($files as $name => $file) {
            self::entryName($name);
            $before[$name] = ['sha256' => hash_file('sha256', $file), 'bytes' => filesize($file)];
            self::need($zip->addFile($file, $name), 'Berkas gagal ditambahkan ke arsip.');
        }
        self::need($zip->close(), 'Arsip gagal diselesaikan; kemungkinan ruang disk habis.');
        self::privatePermissions($path);
        $actual = self::zipInventory($path);
        ksort($before);
        self::need($actual === $before, 'Berkas berubah ketika backup dibuat; hentikan penulisan lalu ulangi.');

        return $actual;
    }

    /** @return array<string, array{sha256: string, bytes: int}> */
    public static function zipInventory(string $path): array
    {
        $zip = new ZipArchive;
        self::need($zip->open($path, ZipArchive::RDONLY | ZipArchive::CHECKCONS) === true, 'ZIP rusak/tidak dapat dibaca.');
        $result = [];
        $seen = [];
        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                if ($name === false) {
                    throw new BackupException('Nama entry ZIP tidak valid.');
                }
                self::entryName(rtrim($name, '/'));
                $identity = strtolower(rtrim($name, '/'));
                self::need(! isset($seen[$identity]), 'Entry ZIP duplikat/berbeda kapitalisasi ditolak.');
                $seen[$identity] = true;
                $opsys = $attributes = 0;
                self::need($zip->getExternalAttributesIndex($index, $opsys, $attributes), 'Atribut ZIP tidak terbaca.');
                self::need((($attributes >> 16) & 0170000) !== 0120000, 'Symlink pada ZIP ditolak.');
                if (str_ends_with($name, '/')) {
                    continue;
                }
                $input = $zip->getStream($name);
                if ($input === false) {
                    throw new BackupException('Entry ZIP tidak bisa dibaca.');
                }
                $hash = hash_init('sha256');
                $bytes = hash_update_stream($hash, $input);
                fclose($input);
                $stat = $zip->statIndex($index);
                self::need($stat !== false && $bytes === $stat['size'], 'Ukuran entry ZIP tidak cocok.');
                $result[$name] = ['sha256' => hash_final($hash), 'bytes' => $bytes];
            }
        } finally {
            $zip->close();
        }
        ksort($result);

        return $result;
    }

    public static function queryValue(PDO $database, string $sql): mixed
    {
        $statement = $database->query($sql);
        if (! $statement instanceof PDOStatement) {
            throw new BackupException('Query pemeriksaan database gagal.');
        }
        $value = $statement->fetchColumn();
        if ($value === false) {
            throw new BackupException('Query pemeriksaan database tidak menghasilkan nilai.');
        }

        return $value;
    }

    public static function entryName(string $name): void
    {
        self::need($name !== '' && ! preg_match('~[\\\\:\x00-\x1f]|^/|(?:^|/)\.{1,2}(?:/|$)|//~', $name), 'Path entry arsip tidak aman.');
        foreach (explode('/', $name) as $part) {
            self::need(! preg_match('/[. ]$/', $part) && ! preg_match('/^(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\.|$)/i', $part), 'Nama berkas tidak aman untuk pemulihan lintas OS.');
        }
    }

    private static function regularFile(string $file, string $root): void
    {
        self::need(is_file($file) && ! is_link($file) && self::inside((string) realpath($file), (string) realpath($root)), 'Berkas hilang, bukan berkas biasa, atau berada di luar sumber.');
    }

    public static function privatePermissions(string $path, bool $directory = false): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            self::need(chmod($path, $directory ? 0700 : 0600), 'Izin privat tidak dapat diterapkan.');
        }
        // Windows: ACL diterapkan wrapper PowerShell sebelum berkas sensitif ditulis.
    }

    public static function write(string $path, string $contents): void
    {
        self::need(file_put_contents($path, $contents, LOCK_EX) === strlen($contents), 'Penulisan berkas tidak lengkap.');
        self::privatePermissions($path);
    }

    /** @param array<string, mixed> $value */
    public static function writeJson(string $path, array $value): void
    {
        self::write($path, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
    }

    /** @return array<string, mixed> */
    private static function readJson(string $path): array
    {
        self::need(is_file($path) && filesize($path) < 32 * 1024 * 1024, 'Metadata tidak ditemukan/terlalu besar.');
        $value = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        self::need(is_array($value), 'Metadata tidak valid.');

        return $value;
    }

    private static function need(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new BackupException($message);
        }
    }
}

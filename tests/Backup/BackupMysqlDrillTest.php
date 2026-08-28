<?php

declare(strict_types=1);

namespace Tests\Backup;

use JCorp\Backup\Backup;
use PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

require_once dirname(__DIR__, 2).'/deploy/backup/Backup.php';

/** Opt-in khusus Laragon lokal; tidak membaca .env aplikasi atau memakai schema test aplikasi. */
final class BackupMysqlDrillTest extends TestCase
{
    public function test_windows_backup_can_restore_to_a_new_restricted_database(): void
    {
        if (getenv('JCORP_BACKUP_MYSQL_DRILL') !== '1' || PHP_OS_FAMILY !== 'Windows') {
            self::markTestSkipped('Opt-in Windows: JCORP_BACKUP_MYSQL_DRILL=1 dan client MySQL tersedia.');
        }
        $dump = getenv('JCORP_DRILL_MYSQLDUMP') ?: 'mysqldump';
        $mysql = getenv('JCORP_DRILL_MYSQL') ?: 'mysql';
        $shell = getenv('JCORP_DRILL_POWERSHELL') ?: 'powershell.exe';
        // Hanya root lokal tanpa password yang biasa digunakan Laragon, bukan credential server.
        $admin = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $id = bin2hex(random_bytes(8));
        $source = 'jcorp_backup_drill_'.$id.'_src';
        $target = 'jcorp_backup_drill_'.$id.'_dst';
        $restoreUser = 'jc_drill_'.$id;
        $restorePassword = bin2hex(random_bytes(20)).' #; space\\"';
        $created = [];
        $userCreated = false;
        $fixture = str_replace('\\', '/', sys_get_temp_dir()).'/jcorp-backup-drill-'.$id;
        mkdir($fixture, 0700);
        $fixture = str_replace('\\', '/', (string) realpath($fixture));
        $project = $fixture.'/project';
        $repo = dirname(__DIR__, 2);
        try {
            foreach ([$source, $target] as $schema) {
                self::assertMatchesRegularExpression('/^jcorp_backup_drill_[a-f0-9]{16}_(src|dst)$/D', $schema);
                $admin->exec('CREATE DATABASE `'.$schema.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                $created[] = $schema;
            }
            $admin->exec('USE `'.$source.'`');
            foreach (['businesses', 'users', 'migrations'] as $table) {
                $admin->exec('CREATE TABLE `'.$table.'` (id INT PRIMARY KEY, name VARCHAR(255), payload LONGBLOB NULL) ENGINE=InnoDB');
            }
            foreach (['catalog_items', 'portfolio_items'] as $table) {
                $admin->exec('CREATE TABLE `'.$table.'` (id INT PRIMARY KEY, business_id INT, name VARCHAR(255), FOREIGN KEY (business_id) REFERENCES businesses(id)) ENGINE=InnoDB');
            }
            $insert = $admin->prepare('INSERT INTO businesses VALUES (1, ?, ?)');
            $insert->execute(['Data buatan — 中文 🧪 quotes \' "', "\x00\xff\x01binary\r\n"]);
            $admin->exec("INSERT INTO users VALUES (1, 'fake-admin@example.invalid', NULL)");
            $admin->exec("INSERT INTO migrations VALUES (1, 'fixture_migration', NULL)");
            $admin->exec("INSERT INTO catalog_items VALUES (1, 1, 'Katalog buatan')");
            $admin->exec("INSERT INTO portfolio_items VALUES (1, 1, 'Galeri buatan')");
            $sourceRows = $admin->query('SELECT * FROM businesses')->fetchAll(PDO::FETCH_ASSOC);

            foreach (['storage/app/public', 'storage/app/private', 'public/build', 'public/images'] as $directory) {
                mkdir($project.'/'.$directory, 0700, true);
            }
            $files = [
                'artisan' => '<?php // fixture only', 'composer.json' => '{}',
                '.env' => "APP_KEY=base64:fake-drill-key\nAPP_ENV=testing\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=$source\nDB_USERNAME=root\nDB_PASSWORD=\n",
                '.htaccess' => 'Options -Indexes', 'public/.htaccess' => 'Options -Indexes',
                'public/index.php' => '<?php // fixture only', 'public/build/manifest.json' => '{}',
                'public/build/app.js' => '// fixture', 'public/images/logo.txt' => 'logo fixture',
                'storage/app/public/image.webp' => "RIFF\x00\xffWEBP synthetic fixture",
                'storage/app/private/sample.txt' => 'private synthetic fixture',
            ];
            foreach ($files as $name => $contents) {
                Backup::write($project.'/'.$name, $contents);
            }
            $this->runCommand(['git', 'init', $project]);
            $this->runCommand(['git', '-C', $project, 'add', 'artisan', 'composer.json', 'public']);
            $this->runCommand(['git', '-C', $project, '-c', 'user.name=Backup Drill', '-c', 'user.email=drill@example.invalid', 'commit', '-m', 'test: synthetic backup source']);

            $command = [$shell, '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $repo.'/deploy/backup/backup.ps1', '-Project', $project, '-Destination', $fixture.'/snapshots', '-PhpBinary', PHP_BINARY, '-DumpBinary', $dump];
            $this->runCommand([...$command, '-Check']);
            self::assertDirectoryDoesNotExist($fixture.'/snapshots');
            $rejected = new Process($command, $repo);
            $rejected->run();
            self::assertNotSame(0, $rejected->getExitCode(), 'Backup must require explicit write freeze acknowledgement');
            self::assertDirectoryDoesNotExist($fixture.'/snapshots');
            $this->runCommand([...$command, '-ConfirmNoWrites']);
            $snapshots = glob($fixture.'/snapshots/local/jcorp-snapshot-*');
            self::assertCount(1, $snapshots);
            $snapshot = str_replace('\\', '/', $snapshots[0]);
            $manifest = Backup::verify($snapshot);
            self::assertSame($source, $manifest['database']);
            self::assertTrue($manifest['root_htaccess_present']);
            self::assertFileDoesNotExist($snapshot.'/client.cnf');
            self::assertSame(1, $manifest['table_counts_before_dump']['catalog_items']);
            $this->runCommand([$shell, '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $repo.'/deploy/backup/extract.ps1', '-Snapshot', $snapshot, '-Target', $fixture.'/recovered', '-PhpBinary', PHP_BINARY]);
            foreach ($files as $name => $contents) {
                if (str_starts_with($name, 'public/') || str_starts_with($name, 'storage/') || in_array($name, ['.env', '.htaccess'], true)) {
                    self::assertSame(hash('sha256', $contents), hash_file('sha256', $fixture.'/recovered/'.$name));
                }
            }

            // Akun restore hanya dapat mengubah schema BARU, tidak source atau aplikasi nyata.
            $admin->exec("CREATE USER '$restoreUser'@'127.0.0.1' IDENTIFIED BY ".$admin->quote($restorePassword));
            $userCreated = true;
            $admin->exec("GRANT ALL PRIVILEGES ON `$target`.* TO '$restoreUser'@'127.0.0.1'");
            $options = Backup::clientOptions(['host' => '127.0.0.1', 'port' => '3306', 'username' => $restoreUser, 'password' => $restorePassword, 'unix_socket' => '']);
            Backup::write($fixture.'/restore.cnf', $options);
            $input = fopen($snapshot.'/database.sql', 'rb');
            try {
                $this->runCommand([$mysql, '--defaults-file='.$fixture.'/restore.cnf', '--no-login-paths', '--binary-mode', '--local-infile=0', '--database='.$target], $input);
            } finally {
                fclose($input);
            }
            $restored = new PDO('mysql:host=127.0.0.1;port=3306;dbname='.$target.';charset=utf8mb4', $restoreUser, $restorePassword, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            self::assertSame($sourceRows, $restored->query('SELECT * FROM businesses')->fetchAll(PDO::FETCH_ASSOC));
            foreach ($manifest['table_counts_before_dump'] as $table => $count) {
                self::assertSame($count, (int) $restored->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn());
            }
            self::assertSame($sourceRows, $admin->query('SELECT * FROM businesses')->fetchAll(PDO::FETCH_ASSOC), 'Original source rows must remain unchanged');
            try {
                $restored->query('SELECT * FROM `'.$source.'`.businesses');
                self::fail('Restore user unexpectedly has source access');
            } catch (\PDOException) {
                self::addToAssertionCount(1);
            }

            // Broken view membuat native mysqldump gagal setelah prepare (bukan saat preflight).
            $admin->exec('CREATE TABLE drill_dependency (id INT) ENGINE=InnoDB');
            $admin->exec('CREATE VIEW drill_broken_view AS SELECT id FROM drill_dependency');
            $admin->exec('DROP TABLE drill_dependency');
            $failedDump = new Process([...$command, '-ConfirmNoWrites'], $repo);
            $failedDump->setTimeout(120);
            $failedDump->run();
            self::assertNotSame(0, $failedDump->getExitCode());
            $allSnapshots = glob($fixture.'/snapshots/local/jcorp-snapshot-*');
            self::assertCount(2, $allSnapshots);
            foreach ($allSnapshots as $candidate) {
                if (str_replace('\\', '/', $candidate) !== $snapshot) {
                    self::assertFileExists($candidate.'/INCOMPLETE');
                    self::assertFileDoesNotExist($candidate.'/COMPLETE');
                    self::assertFileDoesNotExist($candidate.'/client.cnf');
                }
            }
        } finally {
            // Hanya objek dengan nama acak yang berhasil dibuat pada test ini.
            foreach ($created as $schema) {
                self::assertMatchesRegularExpression('/^jcorp_backup_drill_'.$id.'_(src|dst)$/D', $schema);
                $admin->exec('DROP DATABASE `'.$schema.'`');
            }
            if ($userCreated) {
                self::assertSame('jc_drill_'.$id, $restoreUser);
                $admin->exec("DROP USER '$restoreUser'@'127.0.0.1'");
            }
            self::assertSame(strtolower(str_replace('\\', '/', (string) realpath(sys_get_temp_dir()))), strtolower(dirname($fixture)));
            self::assertSame('jcorp-backup-drill-'.$id, basename($fixture));
            $entries = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fixture, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($entries as $entry) {
                if ($entry->isDir() && ! $entry->isLink()) {
                    rmdir($entry->getPathname());
                } else {
                    if (! is_writable($entry->getPathname())) {
                        chmod($entry->getPathname(), 0600);
                    }
                    unlink($entry->getPathname());
                }
            }
            rmdir($fixture);
        }
    }

    /** @param list<string> $command */
    private function runCommand(array $command, mixed $input = null): void
    {
        $process = new Process($command, dirname(__DIR__, 2));
        $process->setTimeout(120);
        if ($input !== null) {
            $process->setInput($input);
        }
        $process->run();
        // Hanya fixture sintetis, tidak pernah log proses backup database asli.
        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
    }
}

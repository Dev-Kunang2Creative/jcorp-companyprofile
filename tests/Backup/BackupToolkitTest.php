<?php

declare(strict_types=1);

namespace Tests\Backup;

use JCorp\Backup\Backup;
use JCorp\Backup\BackupException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use ZipArchive;

require_once dirname(__DIR__, 2).'/deploy/backup/Backup.php';

/** Tidak mem-boot Laravel atau mengakses database aplikasi. */
final class BackupToolkitTest extends TestCase
{
    private string $fixture;

    protected function setUp(): void
    {
        $this->fixture = str_replace('\\', '/', sys_get_temp_dir()).'/jcorp-backup-test-'.bin2hex(random_bytes(8));
        mkdir($this->fixture, 0700);
        $this->fixture = str_replace('\\', '/', (string) realpath($this->fixture));
    }

    protected function tearDown(): void
    {
        // Hanya folder acak yang dibuat oleh test ini, tidak pernah workspace/database aktif.
        $root = str_replace('\\', '/', (string) realpath($this->fixture));
        self::assertMatchesRegularExpression('~/jcorp-backup-test-[a-f0-9]{16}$~', $root);
        self::assertSame(strtolower(str_replace('\\', '/', (string) realpath(sys_get_temp_dir()))), strtolower(dirname($root)));
        $entries = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($entries as $entry) {
            if ($entry->isDir() && ! $entry->isLink()) {
                rmdir($entry->getPathname());
            } else {
                unlink($entry->getPathname());
            }
        }
        rmdir($root);
    }

    public static function unsafeEntries(): array
    {
        return array_map(fn ($value) => [$value], ['../.env', '/etc/passwd', 'C:/file', 'a\\b', 'a/../b', 'a//b', "a\0b", 'CON.txt', 'a/file.', 'a/file ', 'a/./b']);
    }

    #[DataProvider('unsafeEntries')]
    public function test_unsafe_archive_entry_is_rejected(string $name): void
    {
        $this->expectException(BackupException::class);
        Backup::entryName($name);
    }

    public function test_destination_rejects_project_ancestor_public_and_traversal(): void
    {
        $project = $this->fixture.'/project';
        mkdir($project);
        foreach ([$project, $project.'/backup', $this->fixture, $this->fixture.'/public_html/backup', $this->fixture.'/public/backup', $this->fixture.'/safe/../escape', 'relative/path'] as $target) {
            try {
                Backup::destination($target, $project);
                self::fail('Unsafe destination was accepted');
            } catch (BackupException) {
                self::addToAssertionCount(1);
            }
        }
        self::assertSame($this->fixture.'/private/new', Backup::destination($this->fixture.'/private/new', $project));
        self::assertFalse(Backup::inside($project.'-other', $project));
    }

    public function test_credentials_stay_quoted_in_option_file(): void
    {
        $options = Backup::clientOptions(['host' => 'localhost', 'port' => '3306', 'username' => 'fixture', 'password' => "fake\\\"#;\nsecret", 'unix_socket' => '']);
        self::assertStringContainsString('password="fake\\\\\\"#;\\nsecret"', $options);
        self::assertSame(7, count(explode("\n", $options)));
        self::assertStringNotContainsString('[mysqldump]', $options);
    }

    public function test_completed_snapshot_round_trip_preserves_unicode_and_binary_upload(): void
    {
        [$snapshot, $project] = $this->snapshot();
        $manifest = Backup::verify($snapshot);
        self::assertFalse($manifest['encrypted']);
        self::assertFileDoesNotExist($snapshot.'/INCOMPLETE');
        self::assertFileDoesNotExist($snapshot.'/pending.json');
        $target = $this->fixture.'/recovered';
        mkdir($target, 0700);
        Backup::extract($snapshot, $target);
        self::assertSame(file_get_contents($project.'/storage/app/public/example.webp'), file_get_contents($target.'/storage/app/public/example.webp'));
        self::assertSame(file_get_contents($project.'/.env'), file_get_contents($target.'/.env'));
        self::assertDirectoryExists($target.'/storage/app/private');
        self::assertFileDoesNotExist($target.'/database.sql');
    }

    public function test_payload_tampering_is_detected(): void
    {
        [$snapshot] = $this->snapshot();
        Backup::write($snapshot.'/database.sql', 'tampered');
        $this->expectException(BackupException::class);
        Backup::verify($snapshot);
    }

    public function test_manifest_tampering_is_detected(): void
    {
        [$snapshot] = $this->snapshot();
        Backup::write($snapshot.'/manifest.json', '{}');
        $this->expectException(BackupException::class);
        Backup::verify($snapshot);
    }

    public function test_incomplete_marker_prevents_restore_even_if_complete_exists(): void
    {
        [$snapshot] = $this->snapshot();
        Backup::write($snapshot.'/INCOMPLETE', 'unfinished');
        $this->expectException(BackupException::class);
        Backup::verify($snapshot);
    }

    public function test_leftover_credentials_prevent_restore(): void
    {
        [$snapshot] = $this->snapshot();
        Backup::write($snapshot.'/client.cnf', 'fake');
        $this->expectException(BackupException::class);
        Backup::verify($snapshot);
    }

    public function test_nonempty_extraction_target_is_never_overwritten(): void
    {
        [$snapshot] = $this->snapshot();
        $target = $this->fixture.'/recovered';
        mkdir($target);
        Backup::write($target.'/keep.txt', 'keep');
        try {
            Backup::extract($snapshot, $target);
            self::fail('Nonempty target was accepted');
        } catch (BackupException) {
            self::assertSame('keep', file_get_contents($target.'/keep.txt'));
        }
    }

    public function test_extraction_cannot_target_original_project_or_snapshot(): void
    {
        [$snapshot, $project] = $this->snapshot();
        foreach ([$project, $snapshot.'/recovered', $this->fixture] as $target) {
            try {
                Backup::extractionTarget($snapshot, $target);
                self::fail('Unsafe extraction target was accepted');
            } catch (BackupException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function test_zip_traversal_is_rejected_before_extraction(): void
    {
        $zip = new ZipArchive;
        $zip->open($this->fixture.'/bad.zip', ZipArchive::CREATE);
        $zip->addFromString('../escaped.txt', 'unsafe');
        $zip->close();
        $this->expectException(BackupException::class);
        Backup::zipInventory($this->fixture.'/bad.zip');
    }

    public function test_zip_case_collisions_are_rejected(): void
    {
        $zip = new ZipArchive;
        $zip->open($this->fixture.'/bad.zip', ZipArchive::CREATE);
        $zip->addFromString('file.txt', 'one');
        $zip->addFromString('FILE.txt', 'two');
        $zip->close();
        $this->expectException(BackupException::class);
        Backup::zipInventory($this->fixture.'/bad.zip');
    }

    public function test_zip_symlink_is_rejected(): void
    {
        $zip = new ZipArchive;
        $zip->open($this->fixture.'/bad.zip', ZipArchive::CREATE);
        $zip->addFromString('link', '/etc/passwd');
        $zip->setExternalAttributesName('link', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        $zip->close();
        $this->expectException(BackupException::class);
        Backup::zipInventory($this->fixture.'/bad.zip');
    }

    public function test_cached_database_mismatch_is_rejected_without_touching_cache(): void
    {
        [, $project] = $this->snapshot();
        mkdir($project.'/bootstrap/cache', 0700, true);
        Backup::write($project.'/bootstrap/cache/config.php', '<?php return '.var_export(['database' => ['default' => 'mysql', 'connections' => ['mysql' => ['driver' => 'mysql', 'database' => 'wrong']]]], true).';');
        $before = hash_file('sha256', $project.'/bootstrap/cache/config.php');
        try {
            Backup::settings($project);
            self::fail('Different config cache accepted');
        } catch (BackupException) {
            self::assertSame($before, hash_file('sha256', $project.'/bootstrap/cache/config.php'));
        }
    }

    public function test_windows_snapshot_acl_does_not_require_changing_the_owner(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            self::markTestSkipped('ACL regression test requires Windows.');
        }

        $shell = getenv('JCORP_DRILL_POWERSHELL') ?: 'powershell.exe';
        $process = new Process([
            $shell, '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File',
            __DIR__.'/check-private-directory.ps1', '-Fixture', $this->fixture,
        ], dirname(__DIR__, 2));
        $process->setTimeout(30);
        $process->run();

        self::assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        self::assertStringContainsString('Private ACL regression: OK', $process->getOutput());
    }

    /** @return array{string, string} */
    private function snapshot(): array
    {
        $project = $this->fixture.'/project';
        $snapshot = $this->fixture.'/snapshot';
        foreach ([$project.'/storage/app/public', $project.'/public/build', $project.'/public/images', $snapshot] as $directory) {
            mkdir($directory, 0700, true);
        }
        $files = [
            'artisan' => '<?php // fixture', 'composer.json' => '{}',
            '.env' => "APP_KEY=base64:fake-test-only\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_DATABASE=fixture\nDB_USERNAME=fixture\nDB_PASSWORD=fake\n",
            'public/.htaccess' => 'Options -Indexes', 'public/index.php' => '<?php // fixture',
            'public/build/manifest.json' => '{}',
            'storage/app/public/example.webp' => "\x00\xffBerkas buatan — bukan foto pengguna\n",
        ];
        foreach ($files as $relative => $contents) {
            Backup::write($project.'/'.$relative, $contents);
        }
        Backup::writeJson($snapshot.'/pending.json', ['format' => 1, 'label' => 'local', 'project' => $project, 'destination' => $snapshot, 'encrypted' => false]);
        Backup::write($snapshot.'/INCOMPLETE', 'pending');
        Backup::write($snapshot.'/database.sql', '-- synthetic dump');
        Backup::write($snapshot.'/git-status.txt', '');
        Backup::write($snapshot.'/git-tracked.patch', '');
        Backup::archive($snapshot.'/source.zip', ['composer.json' => $project.'/composer.json']);
        Backup::finish($snapshot, str_repeat('a', 40));

        return [$snapshot, $project];
    }
}

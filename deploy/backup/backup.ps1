[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$Destination,
    [string]$Project = (Join-Path $PSScriptRoot '../..'),
    [ValidateSet('local', 'hostinger-testing')][string]$Label = 'local',
    [string]$PhpBinary = 'php',
    [string]$DumpBinary = 'mysqldump',
    [switch]$Check,
    [switch]$ConfirmNoWrites
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
. (Join-Path $PSScriptRoot 'private-directory.ps1')
$helper = Join-Path $PSScriptRoot 'tool.php'
$snapshot = $null

function Invoke-BackupHelper {
    param([string[]]$Arguments)
    $output = & $PhpBinary $helper @Arguments
    if ($LASTEXITCODE -ne 0) { throw 'Pemeriksaan/operasi backup gagal; snapshot tidak boleh digunakan.' }
    return $output
}

function Invoke-BackupGit {
    param([string[]]$Arguments)
    $output = & git -C $Project @Arguments
    if ($LASTEXITCODE -ne 0) { throw 'Git gagal. Tidak ada checkout/reset/pull yang dilakukan.' }
    return $output
}

try {
    foreach ($binary in @($PhpBinary, $DumpBinary, 'git')) {
        $null = Get-Command $binary -CommandType Application -ErrorAction Stop
    }
    $Project = (Resolve-Path -LiteralPath $Project).Path
    $name = 'jcorp-snapshot-' + [DateTime]::UtcNow.ToString('yyyyMMddTHHmmssZ') + '-' + [guid]::NewGuid().ToString('N').Substring(0, 8)
    $proposed = Join-Path (Join-Path $Destination $Label) $name
    $metadata = (Invoke-BackupHelper -Arguments @('preflight', $Project, $proposed, $Label)) -join "`n" | ConvertFrom-Json
    $Project = $metadata.project
    $gitRoot = ((Invoke-BackupGit -Arguments @('rev-parse', '--show-toplevel')) -join '').Trim().Replace('\', '/')
    if ($gitRoot -ine $Project) { throw 'Project harus tepat berada di root repository Git, bukan subfolder repository lain.' }
    $commit = ((Invoke-BackupGit -Arguments @('rev-parse', '--verify', 'HEAD')) -join '').Trim()
    $version = (& $DumpBinary --version) -join ' '
    if ($LASTEXITCODE -ne 0) { throw 'Binary dump tidak dapat dijalankan.' }
    $isMaria = $version -match 'MariaDB|mariadb'
    if (-not $isMaria -and $version -notmatch 'mysqldump') { throw 'Binary harus mysqldump atau mariadb-dump.' }
    if (($metadata.database_version -match 'MariaDB') -ne $isMaria) {
        throw 'Gunakan client dump yang sesuai keluarga server (MySQL/MariaDB).'
    }
    Write-Output "Preflight OK: $Label; database $($metadata.database); commit $commit"
    Write-Output "Tujuan: $($metadata.destination)"
    if ($Label -eq 'hostinger-testing' -and -not $metadata.root_htaccess_present) {
        Write-Warning 'Root .htaccess tidak ada. Pastikan document root server memang public/ sebelum deployment.'
    }
    if ($Check) {
        Write-Output 'Pemeriksaan selesai. Tidak membuat snapshot atau mengubah data.'
        return
    }
    if (-not $ConfirmNoWrites) {
        throw 'Hentikan perubahan konten/upload/migration/worker dahulu, lalu tambahkan -ConfirmNoWrites. Tidak ada maintenance mode otomatis.'
    }
    $snapshot = $metadata.destination
    New-PrivateBackupDirectory -Path $snapshot
    $null = Invoke-BackupHelper -Arguments @('prepare', $Project, $snapshot, $Label)

    $dumpArguments = @("--defaults-file=$snapshot/client.cnf")
    if (-not $isMaria) { $dumpArguments += @('--no-login-paths', '--set-gtid-purged=OFF', '--column-statistics=0') }
    $dumpArguments += @('--single-transaction', '--quick', '--skip-lock-tables', '--hex-blob', '--no-tablespaces', '--routines', '--events', '--triggers', "--result-file=$snapshot/database.sql", [string]$metadata.database)
    # --result-file menghindari perubahan encoding oleh redirection Windows PowerShell.
    # Stderr privat: error server dapat berisi informasi sensitif. Jangan tampilkan di chat/CI.
    $previousPreference = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    & $DumpBinary @dumpArguments 2> (Join-Path $snapshot 'dump-error.log')
    $dumpExit = $LASTEXITCODE
    $ErrorActionPreference = $previousPreference
    if ($dumpExit -ne 0) { throw 'Dump gagal. Periksa dump-error.log secara privat; jangan gunakan snapshot ini.' }
    Remove-Item -LiteralPath (Join-Path $snapshot 'client.cnf') -ErrorAction Stop

    $null = Invoke-BackupGit -Arguments @('archive', '--format=zip', "--output=$snapshot/source.zip", $commit)
    $utf8 = New-Object System.Text.UTF8Encoding($false)
    $gitStatus = (Invoke-BackupGit -Arguments @('status', '--porcelain=v1', '--untracked-files=normal')) -join "`n"
    [System.IO.File]::WriteAllText((Join-Path $snapshot 'git-status.txt'), $gitStatus, $utf8)
    # Git menulis patch langsung ke berkas: encoding tidak melewati pipeline PowerShell.
    $null = Invoke-BackupGit -Arguments @('diff', '--binary', '--no-ext-diff', "--output=$snapshot/git-tracked.patch", $commit, '--')
    $null = Invoke-BackupHelper -Arguments @('finish', $snapshot, $commit)
    Invoke-BackupHelper -Arguments @('verify', $snapshot)
    if ($gitStatus.Trim()) { Write-Warning 'Working tree kotor. Source untracked tidak termasuk source.zip; baca git-status.txt sebelum rollback kode.' }
    Write-Output "Snapshot: $snapshot"
    Write-Output 'Simpan salinan di perangkat/lokasi lain yang terenkripsi. Jangan unggah snapshot ke Git atau folder website.'
} finally {
    if ($snapshot) {
        $credentialFile = Join-Path $snapshot 'client.cnf'
        if (Test-Path -LiteralPath $credentialFile) {
            Remove-Item -LiteralPath $credentialFile -ErrorAction Stop
        }
    }
}

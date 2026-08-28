[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$Snapshot,
    [Parameter(Mandatory = $true)][string]$Target,
    [string]$PhpBinary = 'php'
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'private-directory.ps1')
$helper = Join-Path $PSScriptRoot 'tool.php'
& $PhpBinary $helper verify $Snapshot
if ($LASTEXITCODE -ne 0) { throw 'Snapshot tidak valid.' }
# Validasi tujuan tanpa membaca DB/aplikasi sumber; dapat digunakan pada perangkat pemulihan.
$validated = & $PhpBinary $helper extraction-target $Snapshot $Target
if ($LASTEXITCODE -ne 0) { throw 'Tujuan ekstraksi ditolak.' }
New-PrivateBackupDirectory -Path $validated
& $PhpBinary $helper extract $Snapshot $validated
if ($LASTEXITCODE -ne 0) { throw 'Ekstraksi gagal. Folder parsial dibiarkan untuk pemeriksaan; jangan dipakai sebagai hasil restore.' }

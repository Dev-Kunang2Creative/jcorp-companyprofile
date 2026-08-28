[CmdletBinding()]
param([Parameter(Mandatory = $true)][string]$Fixture)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest
. (Join-Path $PSScriptRoot '../../deploy/backup/private-directory.ps1')

function Read-TestDirectoryAcl {
    param([System.IO.DirectoryInfo]$Directory)
    if ($PSVersionTable.PSVersion.Major -ge 6) {
        return [System.IO.FileSystemAclExtensions]::GetAccessControl($Directory)
    }
    return $Directory.GetAccessControl()
}

function Write-TestDirectoryAcl {
    param([System.IO.DirectoryInfo]$Directory, [System.Security.AccessControl.DirectorySecurity]$Acl)
    if ($PSVersionTable.PSVersion.Major -ge 6) {
        [System.IO.FileSystemAclExtensions]::SetAccessControl($Directory, $Acl)
    } else {
        $Directory.SetAccessControl($Acl)
    }
}

# PHPUnit membuat dan membersihkan fixture sintetis; tidak menyentuh tujuan backup pengguna.
$fixtureDirectory = Get-Item -LiteralPath $Fixture
$tempDirectory = Get-Item -LiteralPath ([System.IO.Path]::GetTempPath())
if ($fixtureDirectory.Name -notmatch '^jcorp-backup-test-[a-f0-9]{16}$' -or
    $fixtureDirectory.Parent.FullName -ine $tempDirectory.FullName.TrimEnd('\', '/')) {
    throw 'ACL test only accepts its own PHPUnit temporary fixture.'
}
$parentPath = Join-Path $fixtureDirectory.FullName 'inherited-modify'
if (Test-Path -LiteralPath $parentPath) { throw 'Fixture already exists.' }
$parentDirectory = New-Item -ItemType Directory -Path $parentPath
$user = [System.Security.Principal.WindowsIdentity]::GetCurrent().User
$children = [System.Security.AccessControl.InheritanceFlags]::ContainerInherit -bor [System.Security.AccessControl.InheritanceFlags]::ObjectInherit
$none = [System.Security.AccessControl.PropagationFlags]::None
$allow = [System.Security.AccessControl.AccessControlType]::Allow
$parentAcl = New-Object System.Security.AccessControl.DirectorySecurity
$parentAcl.SetAccessRuleProtection($true, $false)
$parentAcl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule(
    $user, [System.Security.AccessControl.FileSystemRights]::FullControl,
    [System.Security.AccessControl.InheritanceFlags]::None, $none, $allow
)))
# Anak hanya mewarisi Modify, seperti folder di root D:. Pemilik tetap dapat mengubah DACL.
$parentAcl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule(
    $user, [System.Security.AccessControl.FileSystemRights]::Modify, $children, $none, $allow
)))
$otherUsers = New-Object System.Security.Principal.SecurityIdentifier('S-1-5-11')
$parentAcl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule(
    $otherUsers, [System.Security.AccessControl.FileSystemRights]::ReadAndExecute, $children, $none, $allow
)))
Write-TestDirectoryAcl -Directory $parentDirectory -Acl $parentAcl
$parentBefore = (Read-TestDirectoryAcl $parentDirectory).GetSecurityDescriptorSddlForm([System.Security.AccessControl.AccessControlSections]::All)

$inheritedDirectory = New-Item -ItemType Directory -Path (Join-Path $parentPath 'inheritance-check')
$inheritedAcl = Read-TestDirectoryAcl $inheritedDirectory
$expectedOwner = $inheritedAcl.GetOwner([System.Security.Principal.SecurityIdentifier]).Value
$expectedGroup = $inheritedAcl.GetGroup([System.Security.Principal.SecurityIdentifier]).Value
foreach ($rule in $inheritedAcl.GetAccessRules($true, $true, [System.Security.Principal.SecurityIdentifier])) {
    if ($rule.AccessControlType -eq $allow -and
        ($rule.FileSystemRights -band [System.Security.AccessControl.FileSystemRights]::TakeOwnership)) {
        throw 'Fixture unexpectedly grants WRITE_OWNER; regression would not be exercised.'
    }
}

$snapshot = Join-Path $parentPath 'snapshot'
New-PrivateBackupDirectory -Path $snapshot
$snapshotDirectory = Get-Item -LiteralPath $snapshot
$snapshotAcl = Read-TestDirectoryAcl $snapshotDirectory
$rules = @($snapshotAcl.GetAccessRules($true, $true, [System.Security.Principal.SecurityIdentifier]))
if (-not $snapshotAcl.AreAccessRulesProtected -or $rules.Count -ne 1 -or
    $rules[0].IdentityReference.Value -ne $user.Value -or $rules[0].AccessControlType -ne $allow -or
    $rules[0].FileSystemRights -ne [System.Security.AccessControl.FileSystemRights]::FullControl -or
    $rules[0].InheritanceFlags -ne $children -or $rules[0].PropagationFlags -ne $none -or $rules[0].IsInherited) {
    throw 'Snapshot must have exactly one protected, inheritable current-user FullControl rule.'
}
if ($snapshotAcl.GetOwner([System.Security.Principal.SecurityIdentifier]).Value -ne $expectedOwner -or
    $snapshotAcl.GetGroup([System.Security.Principal.SecurityIdentifier]).Value -ne $expectedGroup) {
    throw 'Creating a private snapshot must not change owner or primary group.'
}

# File dan subfolder mendapat aturan privat yang sama; hanya data test non-sensitif.
$payload = New-Item -ItemType File -Path (Join-Path $snapshot 'synthetic-payload.txt')
$child = New-Item -ItemType Directory -Path (Join-Path $snapshot 'nested')
$nestedPayload = New-Item -ItemType File -Path (Join-Path $child.FullName 'synthetic-payload.txt')
foreach ($entry in @($payload, $child, $nestedPayload)) {
    if ($PSVersionTable.PSVersion.Major -ge 6) {
        $entryAcl = [System.IO.FileSystemAclExtensions]::GetAccessControl($entry)
    } else {
        $entryAcl = $entry.GetAccessControl()
    }
    $entryRules = @($entryAcl.GetAccessRules($true, $true, [System.Security.Principal.SecurityIdentifier]))
    if ($entryRules.Count -ne 1 -or $entryRules[0].IdentityReference.Value -ne $user.Value -or
        $entryRules[0].AccessControlType -ne $allow -or -not $entryRules[0].IsInherited -or
        $entryRules[0].FileSystemRights -ne [System.Security.AccessControl.FileSystemRights]::FullControl) {
        throw 'A child inherited access beyond the snapshot user.'
    }
}
$snapshotBefore = $snapshotAcl.GetSecurityDescriptorSddlForm([System.Security.AccessControl.AccessControlSections]::All)
$existingRejected = $false
try { New-PrivateBackupDirectory -Path $snapshot } catch { $existingRejected = $true }
if (-not $existingRejected -or -not (Test-Path -LiteralPath $payload.FullName)) {
    throw 'Existing target must be rejected without overwriting its files.'
}
if ((Read-TestDirectoryAcl $parentDirectory).GetSecurityDescriptorSddlForm([System.Security.AccessControl.AccessControlSections]::All) -ne $parentBefore -or
    (Read-TestDirectoryAcl $snapshotDirectory).GetSecurityDescriptorSddlForm([System.Security.AccessControl.AccessControlSections]::All) -ne $snapshotBefore) {
    throw 'Parent or existing-target permissions were modified.'
}
Write-Output 'Private ACL regression: OK'

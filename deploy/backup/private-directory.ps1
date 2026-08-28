# Dipakai hanya untuk folder BARU; tidak mengubah ACL folder milik pengguna yang sudah ada.
function New-PrivateBackupDirectory {
    param([Parameter(Mandatory = $true)][string]$Path)

    if (Test-Path -LiteralPath $Path) {
        throw 'Folder tujuan sudah ada. Pilih nama baru agar tidak ada data yang ditimpa.'
    }
    $directory = New-Item -ItemType Directory -Path $Path -ErrorAction Stop
    $user = [System.Security.Principal.WindowsIdentity]::GetCurrent().User
    $acl = New-Object System.Security.AccessControl.DirectorySecurity
    # Ubah DACL saja. SetOwner meminta WRITE_OWNER, yang tidak termasuk izin Modify
    # bawaan root drive D:, walaupun akun ini sudah menjadi pemilik folder baru.
    $acl.SetAccessRuleProtection($true, $false)
    $inheritance = [System.Security.AccessControl.InheritanceFlags]::ContainerInherit -bor [System.Security.AccessControl.InheritanceFlags]::ObjectInherit
    $rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
        $user,
        [System.Security.AccessControl.FileSystemRights]::FullControl,
        $inheritance,
        [System.Security.AccessControl.PropagationFlags]::None,
        [System.Security.AccessControl.AccessControlType]::Allow
    )
    $acl.AddAccessRule($rule)
    # Hindari autoload modul PS7 ke Windows PowerShell 5.1 pada child process.
    if ($PSVersionTable.PSVersion.Major -ge 6) {
        [System.IO.FileSystemAclExtensions]::SetAccessControl($directory, $acl)
        $actual = [System.IO.FileSystemAclExtensions]::GetAccessControl($directory, [System.Security.AccessControl.AccessControlSections]::Access)
    } else {
        $directory.SetAccessControl($acl)
        $actual = $directory.GetAccessControl([System.Security.AccessControl.AccessControlSections]::Access)
    }
    # Jangan menulis credential/SQL bila filesystem tidak menyimpan ACL privat dengan benar.
    $rules = @($actual.GetAccessRules($true, $true, [System.Security.Principal.SecurityIdentifier]))
    if (-not $actual.AreAccessRulesProtected -or $rules.Count -ne 1 -or
        $rules[0].IdentityReference.Value -ne $user.Value -or
        $rules[0].AccessControlType -ne [System.Security.AccessControl.AccessControlType]::Allow -or
        $rules[0].FileSystemRights -ne [System.Security.AccessControl.FileSystemRights]::FullControl -or
        $rules[0].InheritanceFlags -ne $inheritance -or
        $rules[0].PropagationFlags -ne [System.Security.AccessControl.PropagationFlags]::None -or $rules[0].IsInherited) {
        throw 'Izin privat folder backup tidak terverifikasi. Proses berhenti sebelum menyalin data.'
    }
}

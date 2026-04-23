$ErrorActionPreference = 'Stop'

$base    = 'c:\Users\user\Downloads\Programing\SGOplus-Software-Key'
$zipDest = Join-Path $base 'sgoplus-software-key-v1.2.0.zip'

if (Test-Path $zipDest) { Remove-Item $zipDest -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem

$zip = [System.IO.Compression.ZipFile]::Open($zipDest, 'Create')

function Add-FileToZip {
    param(
        [System.IO.Compression.ZipArchive] $Archive,
        [string] $FilePath,
        [string] $EntryName
    )
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
        $Archive,
        $FilePath,
        $EntryName,
        [System.IO.Compression.CompressionLevel]::Optimal
    ) | Out-Null
}

function Add-DirToZip {
    param(
        [System.IO.Compression.ZipArchive] $Archive,
        [string] $DirPath,
        [string] $ZipPrefix
    )
    Get-ChildItem -Path $DirPath -Recurse -File | ForEach-Object {
        $relative  = $_.FullName.Substring($DirPath.Length).TrimStart('\', '/')
        $entryName = "$ZipPrefix/$($relative.Replace('\','/'))"
        Add-FileToZip -Archive $Archive -FilePath $_.FullName -EntryName $entryName
    }
}

# Main plugin file
Add-FileToZip -Archive $zip `
    -FilePath (Join-Path $base 'sgoplus-software-key.php') `
    -EntryName 'sgoplus-software-key/sgoplus-software-key.php'

# Readme
Add-FileToZip -Archive $zip `
    -FilePath (Join-Path $base 'readme.txt') `
    -EntryName 'sgoplus-software-key/readme.txt'

# includes/ (PHP classes + libraries)
Add-DirToZip -Archive $zip `
    -DirPath (Join-Path $base 'includes') `
    -ZipPrefix 'sgoplus-software-key/includes'

# assets/dist/ (built frontend)
Add-DirToZip -Archive $zip `
    -DirPath (Join-Path $base 'assets\dist') `
    -ZipPrefix 'sgoplus-software-key/assets/dist'

# assets/logo.png
Add-FileToZip -Archive $zip `
    -FilePath (Join-Path $base 'assets\logo.png') `
    -EntryName 'sgoplus-software-key/assets/logo.png'

$zip.Dispose()

$size = (Get-Item $zipDest).Length / 1KB
Write-Host "ZIP created: $zipDest ($([Math]::Round($size,1)) KB)"

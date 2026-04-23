Add-Type -AssemblyName System.IO.Compression.FileSystem
$z = [System.IO.Compression.ZipFile]::OpenRead('c:\Users\user\Downloads\Programing\SGOplus-Software-Key\sgoplus-software-key-v1.2.0.zip')
$z.Entries | Select-Object FullName | Sort-Object FullName | Format-Table -AutoSize
$z.Dispose()

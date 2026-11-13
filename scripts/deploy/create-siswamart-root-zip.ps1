$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$stageDir = Join-Path $projectRoot 'dist\deploy-clean-staging'
$zipPath = Join-Path $projectRoot 'dist\hosting.zip'
$publicDest = Join-Path $stageDir 'public_html'
$appDest = Join-Path $stageDir 'siswamart'

Write-Host "==> 1. Memastikan aset frontend Vite ter-build terbaru..." -ForegroundColor Cyan
Push-Location $projectRoot
try {
    npm run build
} finally {
    Pop-Location
}

Write-Host "==> 2. Membersihkan folder staging dan file zip lama..." -ForegroundColor Cyan
if (Test-Path $stageDir) { Remove-Item -Recurse -Force $stageDir }
if (Test-Path $zipPath) { Remove-Item -Force $zipPath }

New-Item -ItemType Directory -Force -Path $publicDest, $appDest | Out-Null

Write-Host "==> 3. Menyiapkan berkas public_html..." -ForegroundColor Cyan
Get-ChildItem -LiteralPath (Join-Path $projectRoot 'public') -Force |
    Where-Object Name -ne 'storage' |
    ForEach-Object { Copy-Item -LiteralPath $_.FullName -Destination $publicDest -Recurse -Force }

# Sertakan deploy-helper.php di dalam public_html
$deployHelper = Join-Path $projectRoot 'scripts\deploy\deploy-helper.php'
if (Test-Path $deployHelper) {
    Copy-Item -LiteralPath $deployHelper -Destination (Join-Path $publicDest 'deploy-helper.php') -Force
}

Write-Host "==> 4. Menyiapkan berkas siswamart (Laravel core)..." -ForegroundColor Cyan
$folders = @('app', 'bootstrap', 'config', 'database', 'lang', 'resources', 'routes', 'vendor')
foreach ($folder in $folders) {
    $source = Join-Path $projectRoot $folder
    if (Test-Path $source) {
        Write-Host "    - Menyalin $folder..."
        Copy-Item -LiteralPath $source -Destination $appDest -Recurse -Force
    }
}

# Bersihkan bootstrap/cache
$bootstrapCache = Join-Path $appDest 'bootstrap\cache'
if (Test-Path $bootstrapCache) {
    Get-ChildItem -LiteralPath $bootstrapCache -Force |
        Where-Object Name -ne '.gitignore' |
        ForEach-Object { [System.IO.File]::Delete($_.FullName) }
}

# Siapkan struktur storage yang bersih
Write-Host "    - Menyiapkan storage..."
$storageSrc = Join-Path $projectRoot 'storage'
$storageDest = Join-Path $appDest 'storage'
Copy-Item -LiteralPath $storageSrc -Destination $appDest -Recurse -Force

# Bersihkan temporary framework files dari storage staging
$cleanPaths = @(
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views',
    'storage\framework\testing',
    'storage\logs'
)
foreach ($rel in $cleanPaths) {
    $targetDir = Join-Path $appDest $rel
    if (Test-Path $targetDir) {
        Get-ChildItem -LiteralPath $targetDir -Force |
            Where-Object Name -ne '.gitignore' |
            ForEach-Object { Remove-Item -LiteralPath $_.FullName -Force -Recurse -ErrorAction SilentlyContinue }
    } else {
        New-Item -ItemType Directory -Force -Path $targetDir | Out-Null
        Set-Content -Path (Join-Path $targetDir '.gitignore') -Value "*`n!.gitignore"
    }
}

# Bersihkan file temporary *.tmp di storage/app
Get-ChildItem -Path (Join-Path $appDest 'storage\app') -Filter '*.tmp' -File | Remove-Item -Force -ErrorAction SilentlyContinue

# Salin berkas akar
foreach ($file in @('artisan', 'composer.json', 'composer.lock', '.env.example', '.env.hosting.example')) {
    $source = Join-Path $projectRoot $file
    if (Test-Path $source) { Copy-Item -LiteralPath $source -Destination $appDest -Force }
}

Write-Host "==> 5. Mengompresi paket zip hosting (Unix compliant)..." -ForegroundColor Cyan
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zipStream = [System.IO.File]::Open($zipPath, [System.IO.FileMode]::CreateNew)
$archive = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create)

try {
    $root = $stageDir.TrimEnd('\')
    $rootLength = $root.Length
    $directoryPermissions = [Convert]::ToInt32('41ED0000', 16) # 040755
    $filePermissions = [Convert]::ToInt32('81A40000', 16)      # 0100644
    $executablePermissions = [Convert]::ToInt32('81ED0000', 16)# 0100755

    $directories = @($root) + [System.IO.Directory]::GetDirectories($root, '*', [System.IO.SearchOption]::AllDirectories)
    foreach ($directory in $directories) {
        $relative = $directory.Substring($rootLength).TrimStart('\').Replace('\', '/')
        if ($relative) {
            $entry = $archive.CreateEntry($relative + '/')
            $entry.ExternalAttributes = $directoryPermissions
        }
    }

    $files = [System.IO.Directory]::GetFiles($root, '*', [System.IO.SearchOption]::AllDirectories)
    $total = $files.Count
    $idx = 0
    foreach ($filePath in $files) {
        $idx++
        $entryName = $filePath.Substring($rootLength).TrimStart('\').Replace('\', '/')
        $entry = $archive.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)
        if ((Split-Path $filePath -Leaf) -eq 'artisan' -or $filePath.EndsWith('.sh')) {
            $entry.ExternalAttributes = $executablePermissions
        } else {
            $entry.ExternalAttributes = $filePermissions
        }
        $entryStream = $entry.Open()
        $fileStream = [System.IO.File]::OpenRead($filePath)
        try { $fileStream.CopyTo($entryStream) }
        finally { $fileStream.Dispose(); $entryStream.Dispose() }
    }
}
finally {
    $archive.Dispose()
    $zipStream.Dispose()
}

Write-Host "==> 5.1 Menandai header ZIP dengan Unix OS identifier..." -ForegroundColor Cyan
$zipBytes = [System.IO.File]::ReadAllBytes($zipPath)
for ($i = 0; $i -lt $zipBytes.Length - 4; $i++) {
    if ($zipBytes[$i] -eq 0x50 -and $zipBytes[$i+1] -eq 0x4b -and $zipBytes[$i+2] -eq 0x01 -and $zipBytes[$i+3] -eq 0x02) {
        if ($zipBytes[$i + 5] -ne 3) {
            $zipBytes[$i + 5] = 3
        }
    }
}
[System.IO.File]::WriteAllBytes($zipPath, $zipBytes)

Write-Host "==> 6. Memvalidasi isi ZIP..." -ForegroundColor Cyan
$check = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
try {
    $badEntries = @($check.Entries | Where-Object { $_.FullName.Contains('\') -or $_.ExternalAttributes -eq 0 })
    $envEntries = @($check.Entries | Where-Object { $_.FullName -match '(^|/)\.env$' })
    $writerEntries = @($check.Entries | Where-Object FullName -eq 'siswamart/app/Support/XlsxWriter.php')
    $styleEntries = @($check.Entries | Where-Object FullName -eq 'siswamart/app/Exports/Concerns/StylesSheet.php')
    $helperEntries = @($check.Entries | Where-Object FullName -eq 'public_html/deploy-helper.php')
    $migrationEntries = @($check.Entries | Where-Object FullName -eq 'siswamart/database/migrations/2026_11_11_000001_ensure_cache_table_value_is_medium_text.php')

    if ($badEntries.Count -gt 0) { throw "Ditemukan entri dengan backslash: $($badEntries.Count)" }
    if ($envEntries.Count -gt 0) { throw "File .env sensitif tidak sengaja masuk ke zip!" }
    if ($writerEntries.Count -ne 1) { throw "XlsxWriter.php tidak ditemukan di siswamart!" }
    if ($styleEntries.Count -ne 1) { throw "StylesSheet.php tidak ditemukan di siswamart!" }
    if ($helperEntries.Count -ne 1) { throw "deploy-helper.php tidak ditemukan di public_html!" }
    if ($migrationEntries.Count -ne 1) { throw "Migration cache medium text tidak ditemukan di siswamart!" }

    [pscustomobject]@{
        Status                   = 'SUCCESS'
        ZipFile                  = $zipPath
        SizeMB                   = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
        TotalEntries             = $check.Entries.Count
        EnvironmentFilesIncluded = $envEntries.Count
        ExcelWriterIncluded      = $writerEntries.Count
        DeployHelperIncluded     = $helperEntries.Count
        CacheMigrationIncluded   = $migrationEntries.Count
        InvalidEntries           = $badEntries.Count
    } | Format-List
}
finally { $check.Dispose() }

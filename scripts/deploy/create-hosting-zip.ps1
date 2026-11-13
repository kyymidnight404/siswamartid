# PowerShell script to package SiswaMart for Shared Hosting (cPanel / DirectAdmin)
$ErrorActionPreference = "Stop"

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..\..")).Path
$stagingDir = Join-Path $projectRoot "build_staging"
$distDir = Join-Path $projectRoot "dist"

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Compress-DirToUnixZip {
    param(
        [string]$SourceDirectory,
        [string]$ZipFilePath
    )

    if (Test-Path $ZipFilePath) {
        Remove-Item -Force $ZipFilePath
    }

    $sourcePath = (Resolve-Path $SourceDirectory).Path.TrimEnd('\')
    $sourceLength = $sourcePath.Length

    $dirPerm = [System.Convert]::ToInt32("41ED0000", 16)  # 040755 (drwxr-xr-x)
    $filePerm = [System.Convert]::ToInt32("81A40000", 16) # 0100644 (-rw-r--r--)
    $execPerm = [System.Convert]::ToInt32("81ED0000", 16) # 0100755 (-rwxr-xr-x)

    $zipStream = [System.IO.File]::Open($ZipFilePath, [System.IO.FileMode]::Create)
    $archive = New-Object System.IO.Compression.ZipArchive($zipStream, [System.IO.Compression.ZipArchiveMode]::Create)

    try {
        # 1. Daftarkan seluruh direktori dengan trailing forward slash '/' dan izin 755
        $dirs = [System.IO.Directory]::GetDirectories($sourcePath, "*", [System.IO.SearchOption]::AllDirectories)
        foreach ($dir in $dirs) {
            $dirRel = $dir.Substring($sourceLength).TrimStart('\').Replace('\', '/') + '/'
            $dirEntry = $archive.CreateEntry($dirRel)
            $dirEntry.ExternalAttributes = $dirPerm
        }

        # 2. Daftarkan seluruh file dengan forward slash '/' dan izin Unix yang valid (644 / 755)
        $files = [System.IO.Directory]::GetFiles($sourcePath, "*", [System.IO.SearchOption]::AllDirectories)
        $total = $files.Count
        $i = 0
        foreach ($filePath in $files) {
            $i++
            if ($i % 500 -eq 0 -or $i -eq $total) {
                Write-Progress -Activity "Mengompresi $(Split-Path $ZipFilePath -Leaf)" -Status "$i dari $total berkas" -PercentComplete (($i / $total) * 100)
            }

            # Pastikan 100% path separator berstandar Unix '/'
            $entryName = $filePath.Substring($sourceLength).TrimStart('\').Replace('\', '/')

            $entry = $archive.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)

            # Tetapkan izin file Unix standar agar tidak Permission Denied di hosting
            $leafName = Split-Path $filePath -Leaf
            if ($leafName -eq "artisan" -or $filePath.EndsWith(".sh")) {
                $entry.ExternalAttributes = $execPerm
            } else {
                $entry.ExternalAttributes = $filePerm
            }

            $entryStream = $entry.Open()
            $fileStream = [System.IO.File]::OpenRead($filePath)
            try {
                $fileStream.CopyTo($entryStream)
            } finally {
                $fileStream.Dispose()
                $entryStream.Dispose()
            }
        }
    } finally {
        Write-Progress -Activity "Mengompresi $(Split-Path $ZipFilePath -Leaf)" -Completed
        $archive.Dispose()
        $zipStream.Dispose()
    }

    # Tandai header ZIP dengan Unix OS identifier (Host OS code = 3)
    $zipBytes = [System.IO.File]::ReadAllBytes($ZipFilePath)
    for ($i = 0; $i -lt $zipBytes.Length - 4; $i++) {
        if ($zipBytes[$i] -eq 0x50 -and $zipBytes[$i+1] -eq 0x4B -and $zipBytes[$i+2] -eq 0x01 -and $zipBytes[$i+3] -eq 0x02) {
            if ($zipBytes[$i + 5] -ne 3) {
                $zipBytes[$i + 5] = 3 # 3 = UNIX
            }
        }
    }
    [System.IO.File]::WriteAllBytes($ZipFilePath, $zipBytes)
}

function Test-ZipPathSeparators {
    param(
        [string]$ZipFilePath
    )
    $zip = [System.IO.Compression.ZipFile]::OpenRead((Resolve-Path $ZipFilePath))
    $badEntries = @()
    $zeroPermEntries = @()
    $envEntries = @()
    $total = $zip.Entries.Count
    foreach ($entry in $zip.Entries) {
        if ($entry.FullName.Contains("\")) {
            $badEntries += $entry.FullName
        }
        if ($entry.ExternalAttributes -eq 0) {
            $zeroPermEntries += $entry.FullName
        }
        if ($entry.FullName -match '(^|/)\.env$') {
            $envEntries += $entry.FullName
        }
    }
    $zip.Dispose()
    if ($badEntries.Count -gt 0) {
        Write-Error "VALIDASI GAGAL! Ditemukan $($badEntries.Count) entri dengan backslash '\' pada $ZipFilePath"
        return $false
    }
    if ($zeroPermEntries.Count -gt 0) {
        Write-Error "VALIDASI GAGAL! Ditemukan $($zeroPermEntries.Count) entri dengan permission 0 pada $ZipFilePath"
        return $false
    }
    if ($envEntries.Count -gt 0) {
        Write-Error "VALIDASI GAGAL! File .env rahasia terdeteksi di dalam $ZipFilePath"
        return $false
    }
    Write-Host "    [OK] $(Split-Path $ZipFilePath -Leaf): $total entri terverifikasi (Unix '/' & permissions 755/644 & Unix OS header)" -ForegroundColor Green
    return $true
}

Write-Host "==> 1. Memastikan aset frontend Vite ter-build terbaru..." -ForegroundColor Cyan
Push-Location $projectRoot
try {
    npm run build
} finally {
    Pop-Location
}

Write-Host "==> 2. Membersihkan folder staging dan dist lama..." -ForegroundColor Cyan
if (Test-Path $stagingDir) { Remove-Item -Recurse -Force $stagingDir }
if (Test-Path $distDir) { Remove-Item -Recurse -Force $distDir }

New-Item -ItemType Directory -Force -Path (Join-Path $stagingDir "public_html") | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $stagingDir "core") | Out-Null
New-Item -ItemType Directory -Force -Path $distDir | Out-Null

Write-Host "==> 3. Menyiapkan berkas public_html..." -ForegroundColor Cyan
$publicStaging = Join-Path $stagingDir "public_html"

# Salin semua berkas public kecuali symlink storage
Get-ChildItem -Path (Join-Path $projectRoot "public") | ForEach-Object {
    if ($_.Name -ne "storage") {
        Copy-Item -Path $_.FullName -Destination $publicStaging -Recurse -Force
    }
}

# Sertakan deploy-helper.php di dalam public_html untuk kemudahan migrasi & storage link
$deployHelper = Join-Path $projectRoot "scripts\deploy\deploy-helper.php"
if (Test-Path $deployHelper) {
    Copy-Item -Path $deployHelper -Destination (Join-Path $publicStaging "deploy-helper.php") -Force
}

Write-Host "==> 4. Menyiapkan berkas core..." -ForegroundColor Cyan
$coreStaging = Join-Path $stagingDir "core"

$coreFolders = @("app", "bootstrap", "config", "database", "lang", "resources", "routes", "vendor")
foreach ($folder in $coreFolders) {
    $src = Join-Path $projectRoot $folder
    if (Test-Path $src) {
        Write-Host "    - Menyalin $folder..."
        Copy-Item -Path $src -Destination (Join-Path $coreStaging $folder) -Recurse -Force
    }
}

# Bersihkan cache di bootstrap/cache pada staging
$bootstrapCache = Join-Path $coreStaging "bootstrap\cache"
if (Test-Path $bootstrapCache) {
    Get-ChildItem -Path $bootstrapCache -Exclude ".gitignore" | Remove-Item -Force -Recurse
}

# Siapkan struktur storage yang bersih
Write-Host "    - Menyiapkan storage..."
$storageSrc = Join-Path $projectRoot "storage"
$storageDest = Join-Path $coreStaging "storage"

# Salin storage
Copy-Item -Path $storageSrc -Destination $coreStaging -Recurse -Force

# Bersihkan framework cache/views/sessions/logs dan file tmp dari storage staging
$cleanPaths = @(
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\framework\testing",
    "storage\logs"
)
foreach ($rel in $cleanPaths) {
    $targetDir = Join-Path $coreStaging $rel
    if (Test-Path $targetDir) {
        Get-ChildItem -Path $targetDir -Exclude ".gitignore" | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
    } else {
        New-Item -ItemType Directory -Force -Path $targetDir | Out-Null
        Set-Content -Path (Join-Path $targetDir ".gitignore") -Value "*`n!.gitignore"
    }
}

# Bersihkan file temporary *.tmp di storage/app
Get-ChildItem -Path (Join-Path $coreStaging "storage\app") -Filter "*.tmp" -File | Remove-Item -Force -ErrorAction SilentlyContinue

# Salin berkas akar core
$coreFiles = @(
    "artisan",
    "composer.json",
    "composer.lock",
    ".env.example",
    ".env.hosting.example"
)
foreach ($file in $coreFiles) {
    $srcFile = Join-Path $projectRoot $file
    if (Test-Path $srcFile) {
        Copy-Item -Path $srcFile -Destination $coreStaging -Force
    }
}

# Buat panduan instalasi di dalam staging, core, dan dist
$panduanContent = @"
================================================================================
                    PANDUAN DEPLOY SISWAMART KE HOSTING
                  (DirectAdmin / cPanel / Shared Hosting)
================================================================================

Struktur File & Folder di Hosting:
--------------------------------------------------------------------------------
domains/siswamartid.my.id/   (atau /home/username/)
├── core/                    <-- Folder aplikasi Laravel (app, vendor, storage, .env)
└── public_html/             <-- Web root publik (index.php, css, assets, build)

index.php di public_html otomatis mendeteksi:
- core
- siswamart
- siswamart_core
sehingga path langsung terhubung otomatis tanpa perlu mengedit index.php!

--------------------------------------------------------------------------------
PILIHAN CARA UPLOAD & EKSTRAK:
--------------------------------------------------------------------------------

[OPSI A - PALING CEPAT (1 File ZIP)]
1. Upload berkas `hosting.zip` ke folder induk domain Anda:
   - DirectAdmin: /domains/siswamartid.my.id/
   - cPanel: /home/username/
2. Klik kanan / menu "Extract" pada `hosting.zip`.
3. Folder `public_html/` dan `core/` akan langsung terisi rapi secara otomatis!

[OPSI B - DUA FILE ZIP TERPISAH]
1. Upload `public_html.zip` ke dalam folder `public_html/` lalu "Extract".
2. Upload `core.zip` ke dalam folder `core/` (sejajar dengan `public_html`) lalu "Extract".

--------------------------------------------------------------------------------
LANGKAH SELANJUTNYA:
--------------------------------------------------------------------------------

1. KONFIGURASI DATABASE & .ENV:
   - Masuk ke folder `core/`.
   - Salin file `.env.hosting.example` menjadi `.env`.
   - Buka dan sesuaikan kredensial database di `.env`:
       APP_URL=https://siswamartid.my.id
       DB_DATABASE=nama_db_hosting
       DB_USERNAME=user_db_hosting
       DB_PASSWORD=password_db_hosting

2. SETUP OTOMATIS 1-KLIK (MIGRASI + STORAGE LINK + CACHE):
   Buka URL berikut di browser Anda:
   https://siswamartid.my.id/deploy-helper.php?key=siswamart2026

   Helper ini akan otomatis:
   - Menjalankan migrasi tabel database (php artisan migrate --force)
   - Membuat symlink storage agar gambar produk & avatar toko tampil
   - Membersihkan dan mengoptimasi cache Laravel

3. KEAMANAN:
   Setelah muncul pesan "SETUP SELESAI!", segera HAPUS berkas `deploy-helper.php` 
   dari folder `public_html/`.

================================================================================
SiswaMart siap melayani pengunjung di https://siswamartid.my.id
================================================================================
"@

Set-Content -Path (Join-Path $stagingDir "PANDUAN_HOSTING.txt") -Value $panduanContent -Encoding UTF8
Set-Content -Path (Join-Path $coreStaging "PANDUAN_HOSTING.txt") -Value $panduanContent -Encoding UTF8
Set-Content -Path (Join-Path $distDir "PANDUAN_HOSTING.txt") -Value $panduanContent -Encoding UTF8
Set-Content -Path (Join-Path $projectRoot "PANDUAN_HOSTING.txt") -Value $panduanContent -Encoding UTF8

Write-Host "==> 5. Mengompresi menjadi ZIP berstandar Unix (forward-slash '/' separator)..." -ForegroundColor Cyan

# A. ZIP Tunggal Berisi public_html dan core (Sesuai Permintaan Utama)
$hostingZip = Join-Path $distDir "hosting.zip"
Write-Host "    - Mengompresi hosting.zip (berisi folder public_html dan core)..."
Compress-DirToUnixZip -SourceDirectory $stagingDir -ZipFilePath $hostingZip

# B. ZIP Terpisah untuk fleksibilitas upload
$publicZip = Join-Path $distDir "public_html.zip"
$coreZip = Join-Path $distDir "core.zip"

Write-Host "    - Mengompresi public_html.zip..."
Compress-DirToUnixZip -SourceDirectory $publicStaging -ZipFilePath $publicZip

Write-Host "    - Mengompresi core.zip (termasuk vendor)..."
Compress-DirToUnixZip -SourceDirectory $coreStaging -ZipFilePath $coreZip

# Salin ke root project agar mudah diakses langsung
Copy-Item -Path $hostingZip -Destination (Join-Path $projectRoot "hosting.zip") -Force
Copy-Item -Path $publicZip -Destination (Join-Path $projectRoot "public_html.zip") -Force
Copy-Item -Path $coreZip -Destination (Join-Path $projectRoot "core.zip") -Force

Write-Host "==> 6. Memvalidasi separator path pada seluruh file ZIP..." -ForegroundColor Cyan
Test-ZipPathSeparators -ZipFilePath $hostingZip | Out-Null
Test-ZipPathSeparators -ZipFilePath $publicZip | Out-Null
Test-ZipPathSeparators -ZipFilePath $coreZip | Out-Null

Write-Host "==> 7. Membersihkan berkas staging sementara..." -ForegroundColor Cyan
Remove-Item -Recurse -Force $stagingDir

$hostingSize = [math]::Round((Get-Item $hostingZip).Length / 1MB, 2)
$publicSize = [math]::Round((Get-Item $publicZip).Length / 1MB, 2)
$coreSize = [math]::Round((Get-Item $coreZip).Length / 1MB, 2)

Write-Host ""
Write-Host "=================================================================" -ForegroundColor Green
Write-Host " BERKAS ZIP HOSTING BERHASIL DIBUAT (UNIX '/' COMPATIBLE)" -ForegroundColor Green
Write-Host "=================================================================" -ForegroundColor Green
Write-Host "1. hosting.zip (berisi public_html/ & core/) : $hostingSize MB"
Write-Host "2. public_html.zip                           : $publicSize MB"
Write-Host "3. core.zip                                  : $coreSize MB"
Write-Host ""
Write-Host "Lokasi berkas siap upload:"
Write-Host "- $hostingZip"
Write-Host "- $(Join-Path $projectRoot "hosting.zip")"
Write-Host "- $publicZip"
Write-Host "- $coreZip"

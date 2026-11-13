<?php
/**
 * SiswaMart Deploy Helper (Untuk Hosting DirectAdmin / cPanel tanpa Terminal)
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$secretKey = 'siswamart2026';

if (($_GET['key'] ?? '') !== $secretKey) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    die('Akses ditolak: Kunci pengaman tidak valid. Gunakan ?key=siswamart2026');
}

define('LARAVEL_START', microtime(true));

$candidatePaths = [
    dirname(__DIR__) . '/siswamart', // Struktur DirectAdmin: /domains/siswamartid.my.id/siswamart
    dirname(__DIR__) . '/core',
    dirname(__DIR__) . '/siswamart/core',
    dirname(__DIR__) . '/siswamart/siswamart',
    dirname(__DIR__) . '/siswamart/hosting/core',
    dirname(__DIR__) . '/hosting/core',
    dirname(__DIR__) . '/siswamart_core',
    dirname(__DIR__) . '/siswamart-core',
    dirname(__DIR__) . '/siswamartid',
    dirname(__DIR__) . '/core/core',
    dirname(__DIR__) . '/laravel',
    dirname(dirname(__DIR__)) . '/siswamart',
    dirname(dirname(__DIR__)) . '/core',
    dirname(dirname(__DIR__)) . '/siswamart/core',
    dirname(dirname(__DIR__)) . '/siswamart_core',
    dirname(dirname(__DIR__)) . '/siswamart-core',
    dirname(dirname(__DIR__)) . '/siswamartid',
    dirname(dirname(__DIR__)) . '/core/core',
    dirname(dirname(__DIR__)) . '/laravel',
    dirname(__DIR__),
];

$applicationPath = null;
foreach ($candidatePaths as $candidate) {
    if (is_file($candidate . '/vendor/autoload.php') && file_exists($candidate . '/bootstrap/app.php')) {
        $applicationPath = $candidate;
        break;
    }
}

// Pencarian dinamis jika belum ditemukan
if (! $applicationPath) {
    $searchRoots = array_unique([dirname(__DIR__), dirname(dirname(__DIR__))]);
    foreach ($searchRoots as $root) {
        if (!is_dir($root)) continue;
        $subDirs = @glob($root . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($subDirs as $sub) {
            if (basename($sub) === 'public_html') continue;
            if (is_file($sub . '/vendor/autoload.php') && file_exists($sub . '/bootstrap/app.php')) {
                $applicationPath = $sub;
                break 2;
            }
            $deepDirs = @glob($sub . '/*', GLOB_ONLYDIR) ?: [];
            foreach ($deepDirs as $deep) {
                if (basename($deep) === 'public_html') continue;
                if (is_file($deep . '/vendor/autoload.php') && file_exists($deep . '/bootstrap/app.php')) {
                    $applicationPath = $deep;
                    break 2;
                }
            }
        }
    }
}

if (! $applicationPath) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    $diag = "Folder core Laravel tidak ditemukan.\n\nJalur yang telah diperiksa:\n- " . implode("\n- ", $candidatePaths) . "\n\n";
    $diag .= "Struktur direktori aktual di " . dirname(__DIR__) . ":\n";
    $items = @glob(dirname(__DIR__) . '/*') ?: [];
    foreach ($items as $item) {
        $name = basename($item);
        if (is_dir($item)) {
            $diag .= " [DIR]  " . $name . "\n";
            $subs = @glob($item . '/*') ?: [];
            foreach (array_slice($subs, 0, 15) as $sub) {
                $diag .= "         └── " . (is_dir($sub) ? "[DIR] " : "") . basename($sub) . "\n";
            }
            if (count($subs) > 15) {
                $diag .= "         └── ... (" . (count($subs) - 15) . " item lainnya)\n";
            }
        } else {
            $diag .= " [FILE] " . $name . "\n";
        }
    }
    $diag .= "\nSaran:\n";
    $diag .= "1. Jika Anda mengekstrak file ZIP di dalam folder 'siswamart', periksa apakah ada folder 'core' di dalamnya (misal: siswamart/core).\n";
    $diag .= "2. Pastikan file 'vendor/autoload.php' dan 'bootstrap/app.php' ada di dalam folder tersebut.\n";
    die($diag);
}

try {
    require $applicationPath.'/vendor/autoload.php';
    $app = require_once $applicationPath.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SiswaMart - Boot Error</title><style>body{font-family:monospace;background:#18181b;color:#f87171;padding:28px;line-height:1.6}h2{color:#ef4444}pre{background:#09090b;padding:16px;border-radius:8px;border:1px solid #27272a;overflow-x:auto;color:#f4f4f5}</style></head><body>';
    echo '<h2>⚠️ Gagal Memulai Laravel (Bootstrap Error)</h2>';
    echo '<p style="color:#fbbf24">Pesan Error: <strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
    echo '<p style="color:#a1a1aa">Kemungkinan penyebab:</p>';
    echo '<ol style="color:#e4e4e7">';
    echo '<li>Folder <code>storage/</code> atau <code>bootstrap/cache/</code> tidak memiliki izin tulis (chmod 775 atau 777).</li>';
    echo '<li>File <code>.env</code> belum dibuat di folder core (salin dari <code>.env.hosting.example</code>).</li>';
    echo '<li>Ekstensi PHP penting belum aktif di hosting (misal: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath).</li>';
    echo '</ol>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SiswaMart Deploy Helper</title><style>body{font-family:monospace;background:#18181b;color:#4ade80;padding:28px;line-height:1.6}h2{color:#38bdf8}h3{color:#f59e0b;margin-top:20px}pre{background:#09090b;padding:16px;border-radius:8px;border:1px solid #27272a;overflow-x:auto;color:#f4f4f5}</style></head><body>';
echo '<h2>🚀 SiswaMart Hosting Setup & Migration Helper</h2>';
echo '<p style="color:#a1a1aa">Path core terdeteksi: <strong>' . htmlspecialchars($applicationPath) . '</strong></p>';

// 0. Bersihkan file residu backslash akibat ekstraksi Windows di Linux
echo '<h3>0. Memeriksa & Membersihkan Residu File Backslash Windows...</h3>';
echo '<pre>';
$cleanedCount = 0;
if (is_dir($applicationPath)) {
    $dirHandle = opendir($applicationPath);
    if ($dirHandle) {
        while (($file = readdir($dirHandle)) !== false) {
            if ($file !== '.' && $file !== '..' && strpos($file, '\\') !== false) {
                $filePath = $applicationPath . '/' . $file;
                if (is_file($filePath)) {
                    if (@unlink($filePath)) {
                        $cleanedCount++;
                    }
                }
            }
        }
        closedir($dirHandle);
    }
}
if ($cleanedCount > 0) {
    echo "Berhasil menghapus {$cleanedCount} file residu backslash Windows di folder core.\n";
} else {
    echo "Tidak ada file residu backslash yang ditemukan.\n";
}
echo '</pre>';

// 1. Migrate Database
echo '<h3>1. Menjalankan Database Migration (migrate --force)...</h3>';
echo '<pre>';
try {
    $kernel->call('migrate', ['--force' => true]);
    echo htmlspecialchars($kernel->output() ?: 'Tidak ada migration baru (skema sudah up-to-date).');
} catch (\Throwable $e) {
    echo 'Peringatan / Error: ' . htmlspecialchars($e->getMessage());
}
echo '</pre>';

// 2. Storage Link
echo '<h3>2. Menghubungkan Storage (storage:link)...</h3>';
echo '<pre>';
try {
    $target = $applicationPath . '/storage/app/public';
    $link = __DIR__ . '/storage';
    if (! file_exists($link)) {
        if (@symlink($target, $link)) {
            echo "Storage symlink berhasil dibuat: $link -> $target\n";
        } else {
            $kernel->call('storage:link');
            echo htmlspecialchars($kernel->output());
        }
    } else {
        echo "Storage symlink sudah terpasang.\n";
    }
} catch (\Throwable $e) {
    echo 'Info: ' . htmlspecialchars($e->getMessage());
}
echo '</pre>';

// 3. Clear Caches
echo '<h3>3. Membersihkan dan Memperbarui Cache...</h3>';
echo '<pre>';
try {
    $kernel->call('config:clear');
    echo htmlspecialchars($kernel->output());
    $kernel->call('route:clear');
    echo htmlspecialchars($kernel->output());
    $kernel->call('view:clear');
    echo htmlspecialchars($kernel->output());
    echo "Seluruh cache berhasil dibersihkan.\n";
} catch (\Throwable $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
echo '</pre>';

echo '<hr style="border:0;border-top:1px solid #3f3f46;margin:28px 0">';
echo '<div style="background:#451a03;border:1px solid #f59e0b;color:#fef3c7;padding:16px;border-radius:8px">';
echo '⚠️ <strong>Penting Demi Keamanan:</strong> Setelah proses di atas berhasil, silakan <strong>hapus file <code>deploy-helper.php</code></strong> dari File Manager hosting Anda!';
echo '</div>';
echo '</body></html>';

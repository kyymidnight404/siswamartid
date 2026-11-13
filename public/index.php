<?php

define('LARAVEL_START', microtime(true));

$candidatePaths = [
    dirname(__DIR__) . '/siswamart', // Struktur DirectAdmin: /domains/siswamartid.my.id/siswamart
    dirname(__DIR__) . '/core', // Hosting DirectAdmin/cPanel folder 'core'
    dirname(__DIR__) . '/siswamart/core',
    dirname(__DIR__) . '/siswamart/siswamart',
    dirname(__DIR__) . '/siswamart/hosting/core',
    dirname(__DIR__) . '/hosting/core',
    dirname(__DIR__) . '/siswamart_core',
    dirname(__DIR__) . '/siswamart-core',
    dirname(__DIR__) . '/siswamartid',
    dirname(__DIR__) . '/core/core',
    dirname(__DIR__) . '/laravel',
    dirname(__DIR__), // Standar Laravel lokal (public di dalam root project)
    dirname(dirname(__DIR__)) . '/siswamart',
    dirname(dirname(__DIR__)) . '/core',
    dirname(dirname(__DIR__)) . '/siswamart/core',
    dirname(dirname(__DIR__)) . '/siswamart_core',
    dirname(dirname(__DIR__)) . '/siswamart-core',
    dirname(dirname(__DIR__)) . '/siswamartid',
    dirname(dirname(__DIR__)) . '/core/core',
    dirname(dirname(__DIR__)) . '/laravel',
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
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>SiswaMart - Konfigurasi Hosting</title><style>body{font-family:Segoe UI,Roboto,sans-serif;padding:30px;line-height:1.6;background:#f8fafc;color:#1e293b}div{max-width:680px;margin:40px auto;background:#fff;padding:28px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 4px 6px -1px rgb(0 0 0/0.05)}h2{color:#e11d48;margin-top:0}pre{background:#f1f5f9;padding:12px;border-radius:6px;overflow-x:auto;font-size:12px}li{margin-bottom:8px}</style></head><body>';
    echo '<div><h2>⚠️ Folder Core Laravel Tidak Ditemukan</h2>';
    echo '<p>File <code>index.php</code> di <code>public_html</code> tidak dapat menemukan folder core Laravel (<code>vendor/autoload.php</code> dan <code>bootstrap/app.php</code>).</p>';
    echo '<p><strong>Panduan Penataan:</strong></p><ol>';
    echo '<li>Ekstrak file <strong>core</strong> sejajar dengan folder <code>public_html</code> (misal: di <code>/home/username/core</code>).</li>';
    echo '<li>Beri nama folder core salah satu dari: <code>core</code>, <code>siswamart</code>, <code>siswamart_core</code>, atau <code>siswamartid</code>.</li>';
    echo '<li>Pastikan folder <code>vendor</code> dan file <code>bootstrap/app.php</code> berada di dalam folder core tersebut.</li>';
    echo '</ol>';
    echo '<p style="color:#64748b;font-size:13px">Jalur yang telah diperiksa secara otomatis:<br><pre>' . htmlspecialchars(implode("\n", $candidatePaths)) . '</pre></p>';
    echo '</div></body></html>';
    exit;
}

if (file_exists($maintenance = $applicationPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $applicationPath.'/vendor/autoload.php';

$app = require_once $applicationPath.'/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Illuminate\Http\Request::capture());
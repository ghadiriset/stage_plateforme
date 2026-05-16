<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

sessionStart();
initDb();

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/') ?: '/';

// ── Fichiers statiques ────────────────────────────────────────
if (preg_match('#^/(css|js|uploads)/#', $uri)) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mime = match($ext) {
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg','jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
        header("Content-Type: $mime");
        readfile($file);
        exit;
    }
}

// ── Router ────────────────────────────────────────────────────
match(true) {

    // Pages publiques
    $uri === '/'             => require __DIR__ . '/pages/home.php',
    $uri === '/entreprises'  => require __DIR__ . '/pages/companies.php',
    $uri === '/stages'       => require __DIR__ . '/pages/internships.php',
    $uri === '/rapports'     => require __DIR__ . '/pages/reports.php',
    $uri === '/a-propos'     => require __DIR__ . '/pages/about.php',

    // Auth
    $uri === '/login'        => require __DIR__ . '/pages/login.php',
    $uri === '/register'     => require __DIR__ . '/pages/register.php',
    $uri === '/logout'       => (function() {
        session_destroy();
        header('Location: /');
        exit;
    })(),

    // Dashboard
    $uri === '/dashboard'    => require __DIR__ . '/pages/dashboard.php',
    $uri === '/chat'         => require __DIR__ . '/pages/chat.php',

    // Actions étudiants
    $uri === '/postuler' && $method === 'POST' => require __DIR__ . '/pages/apply.php',
    $uri === '/student/reports' && $method === 'POST' => require __DIR__ . '/pages/submit_report.php',

    // Admin — utilisateurs
    $uri === '/admin/users' && $method === 'POST' => require __DIR__ . '/pages/admin/add_user.php',
    (bool)preg_match('#^/admin/users/(\d+)/delete$#', $uri, $m) && $method === 'POST'
        => (function() use ($m) {
            $_GET['id'] = $m[1];
            require __DIR__ . '/pages/admin/delete_user.php';
        })(),

    // Admin — entreprises
    $uri === '/admin/companies' && $method === 'POST' => require __DIR__ . '/pages/admin/add_company.php',

    // Admin — stages
    $uri === '/admin/internships' && $method === 'POST' => require __DIR__ . '/pages/admin/add_internship.php',

    // Admin — rapports
    $uri === '/admin/reports' && $method === 'POST' => require __DIR__ . '/pages/admin/add_report.php',
    (bool)preg_match('#^/admin/reports/(\d+)/delete$#', $uri, $m2) && $method === 'POST'
        => (function() use ($m2) {
            $_GET['id'] = $m2[1];
            require __DIR__ . '/pages/admin/delete_report.php';
        })(),

    // API chat (polling)
    $uri === '/api/messages' => require __DIR__ . '/api/messages.php',
    $uri === '/api/send'     => require __DIR__ . '/api/send.php',

    // 404
    default => (function() {
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
    })(),
};

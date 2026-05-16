<?php
function sessionStart(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 7 * 24 * 3600,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireAuth(): void {
    if (!currentUser()) {
        header('Location: /login');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireAuth();
    $user = currentUser();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        include __DIR__ . '/../pages/forbidden.php';
        exit;
    }
}

function isRole(string $role): bool {
    return (currentUser()['role'] ?? '') === $role;
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function currentPath(): string {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}

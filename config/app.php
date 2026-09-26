<?php
session_start();
require_once __DIR__.'/database.php';

function db(): PDO {
    static $db;
    if (!$db) $db = (new Database())->connect();
    return $db;
}
function base_url(string $path=''): string {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $base . ($path ? '/'.$path : '');
}
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function rupiah($n): string {
    return 'Rp '.number_format((float)$n, 0, ',', '.');
}
function redirect(string $url): never {
    header('Location: '.$url); exit;
}
function flash(?string $type=null, ?string $message=null) {
    if ($type && $message) $_SESSION['flash'] = compact('type','message');
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function auth(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!auth()) redirect(base_url('index.php?route=login'));
}
function require_admin(): void {
    require_login();
    if ((auth()['peran'] ?? '') !== 'admin') {
        flash('danger','Akses hanya untuk administrator.');
        redirect(base_url('index.php?route=home'));
    }
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">';
}
function verify_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('CSRF token tidak valid.');
    }
}
function &cart(): array {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];
    return $_SESSION['cart'];
}
function cart_count(): int {
    $n = 0; foreach (cart() as $c) $n += (int)$c['qty']; return $n;
}
function cart_total(): float {
    $t = 0.0; foreach (cart() as $c) $t += $c['harga'] * $c['qty']; return $t;
}
function view(string $name, array $data=[]): void {
    extract($data);
    require __DIR__.'/../app/views/'.$name.'.php';
}

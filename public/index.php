<?php
require_once __DIR__.'/../config/app.php';

$route = $_GET['route'] ?? 'catalog';
if ($route === 'catalog') { require_once __DIR__.'/../app/controllers/CatalogController.php'; (new CatalogController())->index(); exit; }

if ($route === 'login') {
    if (auth()) redirect(base_url('index.php?route=home'));
    if (is_post()) {
        verify_csrf();
        $stmt = db()->prepare("SELECT * FROM pengguna WHERE email=? AND status_aktif=1 LIMIT 1");
        $stmt->execute([trim($_POST['email'] ?? '')]);
        $u = $stmt->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['kata_sandi'])) {
            $_SESSION['user'] = $u;
            flash('success','Selamat datang, '.$u['nama_lengkap'].'!');
            redirect(base_url('index.php?route=home'));
        }
        flash('danger','Email atau password salah.');
    }
    view('auth/login'); exit;
}
if ($route === 'logout') {
    session_destroy();
    redirect(base_url('index.php?route=login'));
}

require_login();

$map = [
 'home' => ['HomeController','index'],
 'kategori' => ['KategoriController','index'],
 'produk' => ['ProdukController','index'],
 'pengguna' => ['PenggunaController','index'],
 'pesanan' => ['PesananController','index'],
 'keranjang' => ['KeranjangController','keranjang'],
 'checkout' => ['KeranjangController','checkout'],
 'pembayaran' => ['KeranjangController','pembayaran'],
];

if (!isset($map[$route])) $route = 'home';
[$controller,$method] = $map[$route];

$file = __DIR__.'/../app/controllers/'.$controller.'.php';
require_once $file;
(new $controller())->$method();

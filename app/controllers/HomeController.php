<?php
class HomeController {
    public function index() {
        if ((auth()['peran'] ?? '') !== 'admin') {
            $this->menuPelanggan();
            return;
        }

        $stats = [
            'produk' => db()->query("SELECT COUNT(*) FROM produk")->fetchColumn(),
            'pesanan' => db()->query("SELECT COUNT(*) FROM pesanan")->fetchColumn(),
            'pengguna' => db()->query("SELECT COUNT(*) FROM pengguna")->fetchColumn(),
            'omzet' => db()->query("SELECT COALESCE(SUM(total_belanja),0) FROM pesanan WHERE status_pembayaran='lunas'")->fetchColumn(),
        ];
        $orders = db()->query("SELECT p.*, u.nama_lengkap FROM pesanan p JOIN pengguna u ON u.id=p.pengguna_id ORDER BY p.tanggal_pesanan DESC LIMIT 7")->fetchAll();
        view('home/index', compact('stats','orders'));
    }

    // Untuk peran 'user': dashboard menampilkan menu katalog untuk berbelanja
    private function menuPelanggan() {
        $rows = db()->query("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.kategori_id WHERE p.status='aktif' ORDER BY p.id DESC")->fetchAll();
        view('home/menu', compact('rows'));
    }
}

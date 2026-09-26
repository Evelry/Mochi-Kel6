<?php
class KeranjangController {

    // ============ KERANJANG (CART) ============
    public function keranjang() {
        require_login();
        if (is_post()) {
            verify_csrf();
            $action = $_POST['action'] ?? '';
            $cart = &cart();

            if ($action === 'tambah') {
                $produkId = (int)($_POST['produk_id'] ?? 0);
                $varian   = trim($_POST['varian'] ?? '');
                $qty      = max(1, (int)($_POST['qty'] ?? 1));

                $s = db()->prepare("SELECT * FROM produk WHERE id=? AND status='aktif'");
                $s->execute([$produkId]);
                $p = $s->fetch();

                if ($p) {
                    if ($varian === '') {
                        $opsi = array_map('trim', explode(',', $p['varian_isi']));
                        $varian = $opsi[0] ?? 'Isi 6';
                    }
                    $key = $produkId.'|'.$varian;
                    $qtyBaru = $qty + (int)($cart[$key]['qty'] ?? 0);
                    if ($qtyBaru > $p['stok']) $qtyBaru = max(1, (int)$p['stok']);

                    if ($qtyBaru < 1) {
                        flash('danger', 'Stok '.$p['nama_produk'].' sedang habis.');
                    } else {
                        $cart[$key] = [
                            'produk_id' => $produkId,
                            'nama_produk' => $p['nama_produk'],
                            'gambar' => $p['gambar'],
                            'varian' => $varian,
                            'harga' => (float)$p['harga_jual'],
                            'hpp' => (float)$p['harga_beli'],
                            'qty' => $qtyBaru,
                            'stok_tersedia' => (int)$p['stok'],
                        ];
                        flash('success', $p['nama_produk'].' ditambahkan ke keranjang.');
                    }
                }
                redirect(base_url('index.php?route=home'));
            }

            if ($action === 'update') {
                $key = $_POST['key'] ?? '';
                $qty = max(1, (int)($_POST['qty'] ?? 1));
                if (isset($cart[$key])) {
                    $cart[$key]['qty'] = min($qty, max(1, (int)$cart[$key]['stok_tersedia']));
                }
                redirect(base_url('index.php?route=keranjang'));
            }

            if ($action === 'hapus') {
                $key = $_POST['key'] ?? '';
                unset($cart[$key]);
                flash('success', 'Item dihapus dari keranjang.');
                redirect(base_url('index.php?route=keranjang'));
            }
        }

        $items = cart();
        $subtotal = cart_total();
        view('keranjang/index', compact('items', 'subtotal'));
    }

    // ============ CHECKOUT ============
    public function checkout() {
        require_login();
        $items = cart();
        if (empty($items)) {
            flash('danger', 'Keranjang kosih, silakan pilih menu terlebih dahulu.');
            redirect(base_url('index.php?route=home'));
        }

        $subtotal = cart_total();
        $ongkir = $subtotal >= 150000 ? 0.0 : 10000.0;
        $total = $subtotal + $ongkir;
        $u = auth();

        if (is_post()) {
            verify_csrf();

            // Re-validate stock right before creating the order
            foreach ($items as $key => $it) {
                $s = db()->prepare("SELECT stok FROM produk WHERE id=?");
                $s->execute([$it['produk_id']]);
                $stokNow = (int)$s->fetchColumn();
                if ($stokNow < $it['qty']) {
                    flash('danger', 'Stok '.$it['nama_produk'].' tidak mencukupi. Silakan sesuaikan keranjang.');
                    redirect(base_url('index.php?route=keranjang'));
                }
            }

            $metode = in_array($_POST['metode_pembayaran'] ?? '', ['transfer_bank', 'cod']) ? $_POST['metode_pembayaran'] : 'transfer_bank';
            $noPesanan = 'ORD-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            db()->beginTransaction();
            try {
                $stmt = db()->prepare("INSERT INTO pesanan
                    (no_pesanan,pengguna_id,total_belanja,ongkir,total_bayar,status_pesanan,status_pembayaran,metode_pembayaran,kurir,nama_penerima,no_telp_penerima,alamat_pengiriman,catatan)
                    VALUES (?,?,?,?,?,'menunggu_pembayaran','belum_lunas',?,?,?,?,?,?)");
                $stmt->execute([
                    $noPesanan, $u['id'], $subtotal, $ongkir, $total, $metode,
                    trim($_POST['kurir'] ?? 'JNE Regular'),
                    trim($_POST['nama_penerima'] ?? $u['nama_lengkap']),
                    trim($_POST['no_telp_penerima'] ?? $u['no_telp']),
                    trim($_POST['alamat_pengiriman'] ?? $u['alamat']),
                    trim($_POST['catatan'] ?? ''),
                ]);
                $pesananId = (int)db()->lastInsertId();

                foreach ($items as $it) {
                    db()->prepare("INSERT INTO detail_pesanan (pesanan_id,produk_id,varian_dipilih,kuantitas,harga_satuan,hpp_satuan,subtotal) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$pesananId, $it['produk_id'], $it['varian'], $it['qty'], $it['harga'], $it['hpp'], $it['harga'] * $it['qty']]);

                    $s = db()->prepare("SELECT stok FROM produk WHERE id=?");
                    $s->execute([$it['produk_id']]);
                    $stokSebelum = (int)$s->fetchColumn();
                    $stokSesudah = $stokSebelum - $it['qty'];

                    db()->prepare("UPDATE produk SET stok=? WHERE id=?")->execute([$stokSesudah, $it['produk_id']]);
                    db()->prepare("INSERT INTO mutasi_stok (produk_id,jenis_mutasi,jumlah,stok_sebelum,stok_sesudah,nomor_referensi,keterangan,pengguna_id) VALUES (?, 'keluar_penjualan', ?, ?, ?, ?, ?, ?)")
                        ->execute([$it['produk_id'], -$it['qty'], $stokSebelum, $stokSesudah, $noPesanan, 'Penjualan via menu pelanggan', $u['id']]);
                }

                db()->commit();
                $_SESSION['cart'] = [];
                flash('success', 'Pesanan '.$noPesanan.' berhasil dibuat. Silakan selesaikan pembayaran.');
                redirect(base_url('index.php?route=pembayaran&id='.$pesananId));
            } catch (Exception $e) {
                db()->rollBack();
                flash('danger', 'Gagal memproses pesanan, silakan coba lagi.');
                redirect(base_url('index.php?route=checkout'));
            }
        }

        view('keranjang/checkout', compact('items', 'subtotal', 'ongkir', 'total', 'u'));
    }

    // ============ PEMBAYARAN (PAYMENT) ============
    public function pembayaran() {
        require_login();
        $u = auth();
        $id = (int)($_GET['id'] ?? 0);

        $s = db()->prepare("SELECT * FROM pesanan WHERE id=? AND pengguna_id=?");
        $s->execute([$id, $u['id']]);
        $pesanan = $s->fetch();
        if (!$pesanan) { flash('danger', 'Pesanan tidak ditemukan.'); redirect(base_url('index.php?route=home')); }

        if (is_post()) {
            verify_csrf();
            $action = $_POST['action'] ?? '';
            if ($action === 'upload_bukti') {
                if (!empty($_FILES['bukti_transfer']['name']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                        $dir = __DIR__.'/../../public/uploads';
                        if (!is_dir($dir)) mkdir($dir, 0775, true);
                        $fname = 'bukti_'.$pesanan['no_pesanan'].'_'.uniqid().'.'.$ext;
                        move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $dir.'/'.$fname);
                        db()->prepare("UPDATE pesanan SET bukti_transfer=?, status_pesanan='diproses' WHERE id=?")->execute([$fname, $id]);
                        flash('success', 'Bukti transfer berhasil diunggah. Pembayaran akan segera diverifikasi.');
                    } else {
                        flash('danger', 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF.');
                    }
                } else {
                    flash('danger', 'Silakan pilih file bukti transfer.');
                }
                redirect(base_url('index.php?route=pembayaran&id='.$id));
            }
            if ($action === 'konfirmasi_cod') {
                db()->prepare("UPDATE pesanan SET status_pesanan='diproses' WHERE id=?")->execute([$id]);
                flash('success', 'Pesanan COD dikonfirmasi. Silakan siapkan pembayaran saat barang tiba.');
                redirect(base_url('index.php?route=pembayaran&id='.$id));
            }
        }

        $detail = db()->prepare("SELECT d.*, pr.nama_produk, pr.gambar FROM detail_pesanan d JOIN produk pr ON pr.id=d.produk_id WHERE d.pesanan_id=?");
        $detail->execute([$id]);
        $items = $detail->fetchAll();

        view('keranjang/pembayaran', compact('pesanan', 'items'));
    }
}

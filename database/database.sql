-- ============================================================
-- SKRIP BASIS DATA LENGKAP: E-COMMERCE MOCHI TERINTEGRASI MODEL ERP
-- Program Studi: Sistem Informasi / Teknik Informatika
-- Modul: Aplikasi Web PHP Native MVC Murni
-- Database: db_mochi_erp
-- ============================================================

-- 1. Buat Basis Data jika belum ada
CREATE DATABASE IF NOT EXISTS `db_mochi_erp` DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE `db_mochi_erp`;

-- Nonaktifkan pengecekan foreign key sementara untuk inisialisasi ulang yang bersih
SET FOREIGN_KEY_CHECKS = 0;

-- Hapus tabel lama jika ada (urutan drop yang aman)
DROP TABLE IF EXISTS `jurnal_keuangan`;
DROP TABLE IF EXISTS `akun_rekening`;
DROP TABLE IF EXISTS `mutasi_stok`;
DROP TABLE IF EXISTS `detail_pesanan`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `pengguna`;

-- ============================================================
-- TABEL 1: PENGGUNA (Tabel Master Akun & Hak Akses)
-- Peran:
-- - 'admin'  : Mengakses seluruh menu ERP (produk, stok, pesanan, keuangan, user)
-- - 'user'   : Pelanggan yang berbelanja mochi di front-end
-- ============================================================

CREATE TABLE `pengguna` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(100) NOT NULL COMMENT 'Nama lengkap pemilik akun',
  `email` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email aktif untuk login',
  `kata_sandi` VARCHAR(255) NOT NULL COMMENT 'Password yang dienkripsi dengan password_hash()',
  `no_telp` VARCHAR(20) DEFAULT NULL COMMENT 'Nomor kontak WhatsApp / HP',
  `alamat` TEXT DEFAULT NULL COMMENT 'Alamat lengkap pengiriman atau tempat tinggal',
  `peran` ENUM('admin', 'user') NOT NULL DEFAULT 'user'
  COMMENT 'Hak akses dalam sistem: admin atau user',
  `status_aktif` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Diblokir/Nonaktif',
  `dibuat_pada` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu pendaftaran akun',
  `diperbarui_pada` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel data pengguna dan hak akses ERP Toko Mochi';

-- ============================================================
-- TABEL 2: KATEGORI (Tabel Master Kategori Rasa Mochi)
-- ============================================================

CREATE TABLE `kategori` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL COMMENT 'Nama kategori rasa mochi (Original, Coklat, Strawberry, Matcha)',
  `slug` VARCHAR(120) NOT NULL UNIQUE COMMENT 'Slug ramah URL',
  `deskripsi` TEXT DEFAULT NULL COMMENT 'Penjelasan singkat kategori rasa',
  `dibuat_pada` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Master Kategori Rasa Mochi';

-- ============================================================
-- TABEL 3: PRODUK (Master Data Mochi & Persediaan)
-- Mencatat HPP (Harga Pokok Pembelian) untuk kalkulasi akuntansi laba kotor.
-- ============================================================

CREATE TABLE `produk` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kategori_id` INT NOT NULL COMMENT 'Relasi ke tabel kategori (rasa mochi)',
  `kode_produk` VARCHAR(30) NOT NULL UNIQUE COMMENT 'SKU / Barcode unik produk (misal: MCH-ORI-001)',
  `nama_produk` VARCHAR(150) NOT NULL COMMENT 'Nama komersial mochi',
  `slug` VARCHAR(180) NOT NULL UNIQUE COMMENT 'Slug untuk URL detail produk',
  `deskripsi` TEXT DEFAULT NULL COMMENT 'Deskripsi detail isian dan bahan mochi',
  `varian_isi` VARCHAR(50) NOT NULL DEFAULT 'Isi 6, Isi 12, Isi 20' COMMENT 'Pilihan varian jumlah isi per box yang tersedia',
  `harga_beli` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'HPP (Harga Pokok Pembelian) satuan saat produksi/restock',
  `harga_jual` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga jual ke konsumen e-commerce',
  `stok` INT NOT NULL DEFAULT 0 COMMENT 'Jumlah stok fisik (box) saat ini',
  `stok_minimum` INT NOT NULL DEFAULT 5 COMMENT 'Batas peringatan stok menipis pada dashboard ERP',
  `berat_gram` INT NOT NULL DEFAULT 250 COMMENT 'Berat produk dalam gram untuk hitung ongkir',
  `gambar` VARCHAR(255) DEFAULT 'default_mochi.jpg' COMMENT 'Nama file foto produk',
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif' COMMENT 'Status tayang di katalog',
  `dibuat_pada` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE
  RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Master Data Produk dan Persediaan Mochi';

-- ============================================================
-- TABEL 4: PESANAN (Header Transaksi Penjualan E-Commerce)
-- ============================================================

CREATE TABLE `pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_pesanan` VARCHAR(40) NOT NULL UNIQUE COMMENT 'Nomor unik transaksi (contoh: ORD-20260912-001)',
  `pengguna_id` INT NOT NULL COMMENT 'ID pengguna (user) yang berbelanja',
  `tanggal_pesanan` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu checkout dilakukan',
  `total_belanja` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal harga produk',
  `ongkir` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya pengiriman kurir',
  `total_bayar` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total belanja + ongkir',
  `status_pesanan` ENUM('menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL
  DEFAULT 'menunggu_pembayaran' COMMENT 'Status alur pesanan',
  `status_pembayaran` ENUM('belum_lunas', 'lunas') NOT NULL DEFAULT 'belum_lunas' COMMENT
  'Status finansial pesanan',
  `metode_pembayaran` ENUM('transfer_bank', 'cod') NOT NULL DEFAULT 'transfer_bank' COMMENT
  'Metode pembayaran yang dipilih',
  `kurir` VARCHAR(50) NOT NULL DEFAULT 'JNE Regular' COMMENT
  'Ekspedisi pengiriman (JNE, J&T, SiCepat, dll)',
  `nomor_resi` VARCHAR(50) DEFAULT NULL COMMENT 'Nomor resi paket dari ekspedisi',
  `nama_penerima` VARCHAR(100) NOT NULL COMMENT 'Nama tujuan kirim',
  `no_telp_penerima` VARCHAR(20) NOT NULL COMMENT 'Nomor telepon tujuan',
  `alamat_pengiriman` TEXT NOT NULL COMMENT 'Alamat tujuan lengkap',
  `catatan` TEXT DEFAULT NULL COMMENT 'Catatan tambahan dari pembeli',
  `bukti_transfer` VARCHAR(255) DEFAULT NULL COMMENT 'File upload bukti transfer bank',
  `dibuat_pada` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pesanan_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Header Transaksi Pesanan Penjualan';

-- ============================================================
-- TABEL 5: DETAIL_PESANAN (Rincian Item yang Dipesan)
-- Menyimpan HPP satuan pada saat pesanan dibuat agar laporan laba rugi akurat.
-- ============================================================

CREATE TABLE `detail_pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL COMMENT 'Relasi ke tabel pesanan',
  `produk_id` INT NOT NULL COMMENT 'Relasi ke tabel produk',
  `varian_dipilih` VARCHAR(20) NOT NULL DEFAULT 'Isi 6' COMMENT 'Varian isi mochi yang dipilih pembeli',
  `kuantitas` INT NOT NULL DEFAULT 1 COMMENT 'Jumlah box yang dibeli',
  `harga_satuan` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga jual satuan saat transaksi',
  `hpp_satuan` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT
  'Harga Pokok Pembelian satuan saat transaksi (untuk Laba/Rugi)',
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'kuantitas * harga_satuan',
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
  ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Detail Item Produk per Pesanan';

-- ============================================================
-- TABEL 6: MUTASI_STOK (Kartu Stok & Audit Trail Inventaris)
-- Setiap barang masuk, barang keluar, atau penyesuaian opname dicatat di sini.
-- ============================================================

CREATE TABLE `mutasi_stok` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `produk_id` INT NOT NULL COMMENT 'ID produk yang termutasi',
  `jenis_mutasi` ENUM('masuk_restock', 'keluar_penjualan', 'penyesuaian_opname', 'retur_masuk')
  NOT NULL COMMENT 'Jenis perubahan stok',
  `jumlah` INT NOT NULL COMMENT 'Banyaknya barang yang bertambah (+) atau berkurang (-)',
  `stok_sebelum` INT NOT NULL COMMENT 'Jumlah stok sebelum aksi dilakukan',
  `stok_sesudah` INT NOT NULL COMMENT 'Jumlah stok setelah aksi dilakukan',
  `nomor_referensi` VARCHAR(50) DEFAULT NULL COMMENT 'Nomor Pesanan atau No Faktur Supplier',
  `keterangan` TEXT DEFAULT NULL COMMENT 'Catatan alasan mutasi atau nama supplier',
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `pengguna_id` INT DEFAULT NULL COMMENT 'Staf (admin) yang melakukan eksekusi mutasi',
  CONSTRAINT `fk_mutasi_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mutasi_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log Kartu Mutasi Stok Fisik';

-- ============================================================
-- TABEL 7: AKUN_REKENING (Chart of Accounts / Bagan Akun Standar ERP)
-- Standar Akuntansi Keuangan Sederhana untuk Perusahaan Dagang.
-- ============================================================

CREATE TABLE `akun_rekening` (
  `kode_akun` VARCHAR(20) PRIMARY KEY COMMENT 'Kode unik akun (misal: 101, 401, 501)',
  `nama_akun` VARCHAR(100) NOT NULL COMMENT 'Nama akun akuntansi',
  `kelompok_akun` ENUM('Aset', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban_Pokok', 'Beban_Operasional')
  NOT NULL COMMENT 'Klasifikasi laporan keuangan',
  `posisi_normal` ENUM('Debit', 'Kredit') NOT NULL COMMENT 'Saldo normal akun',
  `saldo_awal` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo awal periode',
  `dibuat_pada` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bagan Akun Keuangan (Chart of Accounts)';

-- ============================================================
-- TABEL 8: JURNAL_KEUANGAN (Buku Jurnal Umum Double-Entry)
-- Menampung baris Debit dan Kredit yang harus seimbang (Balance).
-- ============================================================

CREATE TABLE `jurnal_keuangan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_jurnal` VARCHAR(40) NOT NULL COMMENT 'Nomor referensi voucher jurnal (misal: JRN-202609-001)',
  `tanggal` DATE NOT NULL COMMENT 'Tanggal transaksi akuntansi',
  `kode_akun` VARCHAR(20) NOT NULL COMMENT 'Relasi ke tabel akun_rekening',
  `debit` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai mutasi di sisi Debit',
  `kredit` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai mutasi di sisi Kredit',
  `keterangan` VARCHAR(255) NOT NULL COMMENT 'Uraian keterangan transaksi',
  `referensi_transaksi` VARCHAR(50) DEFAULT NULL COMMENT 'No Pesanan atau No Faktur Beban',
  `pengguna_id` INT DEFAULT NULL COMMENT 'Staf (admin) yang membukukan transaksi',
  `dibuat_pada` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_jurnal_akun` FOREIGN KEY (`kode_akun`) REFERENCES `akun_rekening` (`kode_akun`)
  ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_jurnal_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabel Jurnal Umum Keuangan Double-Entry';

-- Kembalikan pengecekan foreign key
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATA DUMMY / SAMPLE DATA UNTUK PEMBELAJARAN & PENGUJIAN MAHASISWA
-- ============================================================

-- 1. Data Akun Pengguna (Password default: 'password123' untuk semua akun demo)
-- Hash password valid dibuat menggunakan password_hash('password123', PASSWORD_BCRYPT)
-- Hash: $2y$10$h6wYCDRN.TkSvdHDKb.lJ.mAJVXewX4wisVkDbWbgI0Lr.3Gj8/Gq
INSERT INTO `pengguna` (`id`, `nama_lengkap`, `email`, `kata_sandi`, `no_telp`, `alamat`, `peran`,
`status_aktif`) VALUES
(1, 'Administrator Toko Mochi', 'admin@mochierp.com',
'$2y$10$h6wYCDRN.TkSvdHDKb.lJ.mAJVXewX4wisVkDbWbgI0Lr.3Gj8/Gq',
'081234567890', 'Gedung Pusat Operasional Mochi Store, Jakarta', 'admin', 1),
(2, 'Budi Santoso', 'budi@gmail.com',
'$2y$10$h6wYCDRN.TkSvdHDKb.lJ.mAJVXewX4wisVkDbWbgI0Lr.3Gj8/Gq',
'085677889900', 'Jl. Merdeka No. 45, Bandung, Jawa Barat', 'user', 1),
(3, 'Siti Rahma', 'siti@gmail.com',
'$2y$10$h6wYCDRN.TkSvdHDKb.lJ.mAJVXewX4wisVkDbWbgI0Lr.3Gj8/Gq',
'087812345678', 'Jl. Malioboro No. 12, Yogyakarta', 'user', 1),
(4, 'Dewi Anggraini', 'dewi@gmail.com',
'$2y$10$h6wYCDRN.TkSvdHDKb.lJ.mAJVXewX4wisVkDbWbgI0Lr.3Gj8/Gq',
'081399887766', 'Jl. Sudirman No. 8, Surabaya', 'user', 1);

-- 2. Data Master Kategori Rasa Mochi
INSERT INTO `kategori` (`id`, `nama_kategori`, `slug`, `deskripsi`) VALUES
(1, 'Original', 'original', 'Mochi klasik dengan kulit kenyal lembut dan rasa gurih manis alami
tanpa perisa tambahan.'),
(2, 'Coklat', 'coklat', 'Mochi dengan isian coklat lumer premium, cocok untuk pecinta rasa manis
yang kaya.'),
(3, 'Strawberry', 'strawberry', 'Mochi dengan isian selai strawberry segar, rasa asam manis yang
menyegarkan.'),
(4, 'Matcha', 'matcha', 'Mochi dengan isian pasta matcha premium khas Jepang, rasa teh hijau yang
sedikit pahit dan elegan.');

-- 3. Data Master Produk Mochi
INSERT INTO `produk` (`id`, `kategori_id`, `kode_produk`, `nama_produk`, `slug`, `deskripsi`,
`varian_isi`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `berat_gram`, `gambar`, `status`) VALUES
(1, 1, 'MCH-ORI-001', 'Mochi Original Klasik', 'mochi-original-klasik',
'Mochi original dengan kulit ketan kenyal lembut dan isian kacang tanah manis gurih khas
resep tradisional.', 'Isi 6, Isi 12, Isi 20', 15000.00, 28000.00, 40, 10, 250,
'mochi_original_klasik.svg', 'aktif'),
(2, 1, 'MCH-ORI-002', 'Mochi Original Premium Wijen', 'mochi-original-premium-wijen',
'Mochi original bertabur wijen sangrai dengan isian pasta kacang merah premium yang lembut
di lidah.', 'Isi 6, Isi 12, Isi 20', 17000.00, 32000.00, 30, 8, 260,
'mochi_original_wijen.svg', 'aktif'),
(3, 2, 'MCH-COK-001', 'Mochi Coklat Lumer', 'mochi-coklat-lumer',
'Mochi dengan isian coklat premium yang lumer saat digigit, dibalut kulit ketan lembut
yang kenyal.', 'Isi 6, Isi 12, Isi 20', 18000.00, 33000.00, 35, 10, 260,
'mochi_coklat_lumer.svg', 'aktif'),
(4, 2, 'MCH-COK-002', 'Mochi Coklat Almond', 'mochi-coklat-almond',
'Perpaduan coklat premium dan taburan almond panggang yang renyah, memberi sensasi tekstur
yang unik.', 'Isi 6, Isi 12', 20000.00, 36000.00, 20, 6, 270,
'mochi_coklat_almond.svg', 'aktif'),
(5, 3, 'MCH-STR-001', 'Mochi Strawberry Fresh', 'mochi-strawberry-fresh',
'Mochi dengan isian selai strawberry asli yang segar dan sedikit asam, cocok dinikmati
dingin dari kulkas.', 'Isi 6, Isi 12, Isi 20', 17000.00, 31000.00, 32, 8, 250,
'mochi_strawberry_fresh.svg', 'aktif'),
(6, 3, 'MCH-STR-002', 'Mochi Strawberry Cream Cheese', 'mochi-strawberry-cream-cheese',
'Mochi isian strawberry dipadukan dengan cream cheese lembut, menghasilkan rasa manis
asam yang creamy.', 'Isi 6, Isi 12', 19000.00, 35000.00, 18, 6, 270,
'mochi_strawberry_cheese.svg', 'aktif'),
(7, 4, 'MCH-MAT-001', 'Mochi Matcha Premium', 'mochi-matcha-premium',
'Mochi dengan isian pasta matcha grade premium langsung dari Jepang, rasa teh hijau yang
autentik dan elegan.', 'Isi 6, Isi 12, Isi 20', 19000.00, 36000.00, 25, 8, 260,
'mochi_matcha_premium.svg', 'aktif'),
(8, 4, 'MCH-MAT-002', 'Mochi Matcha Red Bean', 'mochi-matcha-red-bean',
'Kombinasi kulit matcha dan isian kacang merah manis, perpaduan rasa pahit dan manis yang
seimbang.', 'Isi 6, Isi 12', 20000.00, 37000.00, 6, 8, 270,
'mochi_matcha_redbean.svg', 'aktif');

-- 4. Data Master Bagan Akun (Chart of Accounts / COA)
INSERT INTO `akun_rekening` (`kode_akun`, `nama_akun`, `kelompok_akun`, `posisi_normal`, `saldo_awal`)
VALUES
('101', 'Kas di Tangan / Kas Toko', 'Aset', 'Debit', 10000000.00),
('102', 'Kas di Bank (Rekening Utama)', 'Aset', 'Debit', 25000000.00),
('103', 'Piutang Usaha (COD / Marketplace)', 'Aset', 'Debit', 0.00),
('104', 'Persediaan Barang Dagang (Mochi)', 'Aset', 'Debit', 4520000.00),
('201', 'Utang Usaha (Pemasok Bahan Baku)', 'Kewajiban', 'Kredit', 0.00),
('301', 'Modal Pemilik', 'Ekuitas', 'Kredit', 39520000.00),
('401', 'Pendapatan Penjualan Mochi', 'Pendapatan', 'Kredit', 0.00),
('501', 'Beban Pokok Penjualan (HPP)', 'Beban_Pokok', 'Debit', 0.00),
('601', 'Beban Operasional & Pengiriman', 'Beban_Operasional', 'Debit', 0.00),
('602', 'Beban Listrik, Internet & Utilitas', 'Beban_Operasional', 'Debit', 0.00),
('603', 'Beban Promosi & Iklan Digital', 'Beban_Operasional', 'Debit', 0.00);

-- 5. Data Transaksi Sampel Pesanan (Untuk Dashboard Awal)
INSERT INTO `pesanan` (`id`, `no_pesanan`, `pengguna_id`, `tanggal_pesanan`, `total_belanja`, `ongkir`,
`total_bayar`, `status_pesanan`, `status_pembayaran`, `metode_pembayaran`, `kurir`, `nomor_resi`,
`nama_penerima`, `no_telp_penerima`, `alamat_pengiriman`, `catatan`) VALUES
(1, 'ORD-20260901-001', 2, '2026-09-01 10:30:00', 61000.00, 12000.00, 73000.00, 'selesai', 'lunas',
'transfer_bank', 'JNE Regular', 'JNE8899112233', 'Budi Santoso', '085677889900', 'Jl. Merdeka No. 45,
Bandung, Jawa Barat', 'Mohon dipacking dengan ice pack'),
(2, 'ORD-20260905-002', 3, '2026-09-05 14:15:00', 36000.00, 10000.00, 46000.00, 'diproses', 'lunas',
'transfer_bank', 'SiCepat BEST', NULL, 'Siti Rahma', '087812345678', 'Jl. Malioboro No. 12, Yogyakarta',
'Tolong kirim pagi hari'),
(3, 'ORD-20260910-003', 2, '2026-09-10 09:00:00', 28000.00, 8000.00, 36000.00, 'menunggu_pembayaran',
'belum_lunas', 'transfer_bank', 'J&T Express', NULL, 'Budi Santoso', '085677889900', 'Jl. Merdeka No. 45,
Bandung, Jawa Barat', 'Segera kirim');

-- 6. Data Detail Pesanan Sampel
INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `varian_dipilih`, `kuantitas`,
`harga_satuan`, `hpp_satuan`, `subtotal`) VALUES
(1, 1, 1, 'Isi 12', 1, 28000.00, 15000.00, 28000.00),
(2, 1, 3, 'Isi 12', 1, 33000.00, 18000.00, 33000.00),
(3, 2, 4, 'Isi 6', 1, 36000.00, 20000.00, 36000.00),
(4, 3, 1, 'Isi 12', 1, 28000.00, 15000.00, 28000.00);

-- 7. Data Riwayat Mutasi Stok Sampel
INSERT INTO `mutasi_stok` (`id`, `produk_id`, `jenis_mutasi`, `jumlah`, `stok_sebelum`, `stok_sesudah`,
`nomor_referensi`, `keterangan`, `tanggal`, `pengguna_id`) VALUES
(1, 1, 'masuk_restock', 41, 0, 41, 'PO-SUPP-001', 'Produksi Batch Awal Mochi Original Klasik',
'2026-08-25 08:00:00', 1),
(2, 1, 'keluar_penjualan', -1, 41, 40, 'ORD-20260901-001', 'Pengurangan stok untuk pesanan Budi Santoso',
'2026-09-01 10:35:00', 1),
(3, 3, 'masuk_restock', 36, 0, 36, 'PO-SUPP-001', 'Produksi Batch Awal Mochi Coklat Lumer',
'2026-08-25 08:00:00', 1),
(4, 3, 'keluar_penjualan', -1, 36, 35, 'ORD-20260901-001', 'Pengurangan stok untuk pesanan Budi Santoso',
'2026-09-01 10:35:00', 1),
(5, 4, 'masuk_restock', 21, 0, 21, 'PO-SUPP-002', 'Produksi Batch Mochi Coklat Almond',
'2026-08-28 09:00:00', 1),
(6, 4, 'keluar_penjualan', -1, 21, 20, 'ORD-20260905-002', 'Pengurangan stok untuk pesanan Siti Rahma',
'2026-09-05 14:20:00', 1);

-- 8. Data Jurnal Umum Keuangan Sampel (Double-Entry Balance)
-- Transaksi 1: Penjualan Pesanan ORD-20260901-001 Lunas
-- Debit Kas di Bank Rp 73.000, Kredit Pendapatan Penjualan Rp 61.000, sisanya Ongkir
INSERT INTO `jurnal_keuangan` (`no_jurnal`, `tanggal`, `kode_akun`, `debit`, `kredit`, `keterangan`,
`referensi_transaksi`, `pengguna_id`) VALUES
('JRN-20260901-001', '2026-09-01', '102', 61000.00, 0.00,
'Penerimaan Kas Penjualan Mochi ORD-20260901-001', 'ORD-20260901-001', 1),
('JRN-20260901-001', '2026-09-01', '401', 0.00, 61000.00,
'Pendapatan Penjualan Mochi ORD-20260901-001', 'ORD-20260901-001', 1),
-- Jurnal HPP & Persediaan untuk ORD-20260901-001 (HPP: 15rb + 18rb = 33rb)
('JRN-20260901-002', '2026-09-01', '501', 33000.00, 0.00,
'Pengakuan HPP Beban Pokok Penjualan ORD-20260901-001', 'ORD-20260901-001', 1),
('JRN-20260901-002', '2026-09-01', '104', 0.00, 33000.00,
'Pengurangan Nilai Persediaan Barang Dagang ORD-20260901-001', 'ORD-20260901-001', 1),

-- Transaksi 2: Penjualan Pesanan ORD-20260905-002 Lunas
('JRN-20260905-001', '2026-09-05', '102', 36000.00, 0.00,
'Penerimaan Kas Penjualan Mochi ORD-20260905-002', 'ORD-20260905-002', 1),
('JRN-20260905-001', '2026-09-05', '401', 0.00, 36000.00,
'Pendapatan Penjualan Mochi ORD-20260905-002', 'ORD-20260905-002', 1),
-- Jurnal HPP ORD-20260905-002 (HPP: 20rb)
('JRN-20260905-002', '2026-09-05', '501', 20000.00, 0.00,
'Pengakuan HPP Beban Pokok Penjualan ORD-20260905-002', 'ORD-20260905-002', 1),
('JRN-20260905-002', '2026-09-05', '104', 0.00, 20000.00,
'Pengurangan Nilai Persediaan Barang Dagang ORD-20260905-002', 'ORD-20260905-002', 1),

-- Transaksi 3: Pembayaran Beban Listrik & Internet Toko
('JRN-20260908-001', '2026-09-08', '602', 450000.00, 0.00,
'Pembayaran Tagihan Listrik & Internet Toko Bulan September', 'BBN-OPR-001', 1),
('JRN-20260908-001', '2026-09-08', '101', 0.00, 450000.00,
'Kas Toko Keluar untuk Listrik & Internet', 'BBN-OPR-001', 1);

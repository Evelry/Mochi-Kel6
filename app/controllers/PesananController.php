<?php
class PesananController {
    public function index() {
        require_admin();
        if(is_post()){
            verify_csrf();
            if(($_POST['action']??'')==='update'){
                db()->prepare("UPDATE pesanan SET status_pesanan=?,status_pembayaran=?,nomor_resi=? WHERE id=?")->execute([$_POST['status_pesanan'],$_POST['status_pembayaran'],trim($_POST['nomor_resi']??''),(int)$_POST['id']]);
                flash('success','Status pesanan diperbarui.'); redirect(base_url('index.php?route=pesanan'));
            }
        }
        $rows=db()->query("SELECT p.*,u.nama_lengkap, (SELECT COUNT(*) FROM detail_pesanan d WHERE d.pesanan_id=p.id) item_count FROM pesanan p JOIN pengguna u ON u.id=p.pengguna_id ORDER BY p.tanggal_pesanan DESC")->fetchAll();
        view('pesanan/index',compact('rows'));
    }
}

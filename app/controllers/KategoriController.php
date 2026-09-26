<?php
class KategoriController {
    public function index() {
        require_admin();
        $edit = null;
        if (isset($_GET['edit'])) {
            $s=db()->prepare("SELECT * FROM kategori WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch();
        }
        if (is_post()) {
            verify_csrf();
            $action=$_POST['action'] ?? '';
            if ($action==='save') {
                $id=(int)($_POST['id']??0);
                $name=trim($_POST['nama_kategori']); $slug=trim($_POST['slug']);
                if ($id) $s=db()->prepare("UPDATE kategori SET nama_kategori=?, slug=?, deskripsi=? WHERE id=?")->execute([$name,$slug,$_POST['deskripsi']??'',$id]);
                else $s=db()->prepare("INSERT INTO kategori(nama_kategori,slug,deskripsi) VALUES(?,?,?)")->execute([$name,$slug,$_POST['deskripsi']??'']);
                flash('success','Kategori berhasil disimpan.'); redirect(base_url('index.php?route=kategori'));
            }
            if ($action==='delete') {
                try { db()->prepare("DELETE FROM kategori WHERE id=?")->execute([(int)$_POST['id']]); flash('success','Kategori dihapus.'); }
                catch(Exception $e){ flash('danger','Kategori tidak dapat dihapus karena masih dipakai produk.'); }
                redirect(base_url('index.php?route=kategori'));
            }
        }
        $rows=db()->query("SELECT k.*, COUNT(p.id) jumlah_produk FROM kategori k LEFT JOIN produk p ON p.kategori_id=k.id GROUP BY k.id ORDER BY k.id DESC")->fetchAll();
        view('kategori/index',compact('rows','edit'));
    }
}

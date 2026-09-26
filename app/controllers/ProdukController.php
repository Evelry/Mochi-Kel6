<?php
class ProdukController {
    public function index() {
        require_admin();
        if (is_post()) {
            verify_csrf();
            $action=$_POST['action']??'';
            if ($action==='save') {
                $id=(int)($_POST['id']??0);
                $data=[
                    $_POST['kategori_id'], trim($_POST['kode_produk']), trim($_POST['nama_produk']),
                    trim($_POST['slug']), $_POST['deskripsi']??'', trim($_POST['varian_isi']),
                    (float)$_POST['harga_beli'],(float)$_POST['harga_jual'],(int)$_POST['stok'],
                    (int)$_POST['stok_minimum'],(int)$_POST['berat_gram'],$_POST['status']
                ];
                $gambar=$_POST['gambar_lama']??'default_mochi.jpg';
                if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error']===UPLOAD_ERR_OK) {
                    $ext=strtolower(pathinfo($_FILES['gambar']['name'],PATHINFO_EXTENSION));
                    if (in_array($ext,['jpg','jpeg','png','webp','svg'])) {
                        $gambar=uniqid('mochi_').'.'.$ext;
                        move_uploaded_file($_FILES['gambar']['tmp_name'],__DIR__.'/../../public/uploads/'.$gambar);
                    }
                }
                if ($id) {
                    $sql="UPDATE produk SET kategori_id=?,kode_produk=?,nama_produk=?,slug=?,deskripsi=?,varian_isi=?,harga_beli=?,harga_jual=?,stok=?,stok_minimum=?,berat_gram=?,status=?,gambar=? WHERE id=?";
                    db()->prepare($sql)->execute([...$data,$gambar,$id]);
                } else {
                    $sql="INSERT INTO produk(kategori_id,kode_produk,nama_produk,slug,deskripsi,varian_isi,harga_beli,harga_jual,stok,stok_minimum,berat_gram,status,gambar) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    db()->prepare($sql)->execute([...$data,$gambar]);
                }
                flash('success','Produk berhasil disimpan.'); redirect(base_url('index.php?route=produk'));
            }
            if ($action==='delete') {
                try { db()->prepare("DELETE FROM produk WHERE id=?")->execute([(int)$_POST['id']]); flash('success','Produk dihapus.'); }
                catch(Exception $e){ flash('danger','Produk tidak dapat dihapus karena sudah digunakan pada transaksi.'); }
                redirect(base_url('index.php?route=produk'));
            }
        }
        $edit=null;
        if(isset($_GET['edit'])){$s=db()->prepare("SELECT * FROM produk WHERE id=?");$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
        $rows=db()->query("SELECT p.*,k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.kategori_id ORDER BY p.id DESC")->fetchAll();
        $categories=db()->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
        view('produk/index',compact('rows','categories','edit'));
    }
}

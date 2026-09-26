<?php
class PenggunaController {
    public function index() {
        require_admin();
        if (is_post()) {
            verify_csrf(); $action=$_POST['action']??'';
            if($action==='save'){
                $id=(int)($_POST['id']??0); $pass=$_POST['kata_sandi']??'';
                if($id){
                    $sql="UPDATE pengguna SET nama_lengkap=?,email=?,no_telp=?,alamat=?,peran=?,status_aktif=?".($pass?",kata_sandi=?":"")." WHERE id=?";
                    $args=[trim($_POST['nama_lengkap']),trim($_POST['email']),$_POST['no_telp']??'',$_POST['alamat']??'',$_POST['peran'],(int)$_POST['status_aktif']];
                    if($pass)$args[]=password_hash($pass,PASSWORD_BCRYPT); $args[]=$id;
                }else{
                    $sql="INSERT INTO pengguna(nama_lengkap,email,kata_sandi,no_telp,alamat,peran,status_aktif) VALUES(?,?,?,?,?,?,?)";
                    $args=[trim($_POST['nama_lengkap']),trim($_POST['email']),password_hash($pass?:'password123',PASSWORD_BCRYPT),$_POST['no_telp']??'',$_POST['alamat']??'',$_POST['peran'],(int)$_POST['status_aktif']];
                }
                db()->prepare($sql)->execute($args); flash('success','Pengguna berhasil disimpan.'); redirect(base_url('index.php?route=pengguna'));
            }
            if($action==='delete'){
                if((int)$_POST['id'] === (int)auth()['id']) flash('danger','Akun yang sedang login tidak dapat dihapus.');
                else { db()->prepare("DELETE FROM pengguna WHERE id=?")->execute([(int)$_POST['id']]); flash('success','Pengguna dihapus.');}
                redirect(base_url('index.php?route=pengguna'));
            }
        }
        $edit=null;if(isset($_GET['edit'])){$s=db()->prepare("SELECT * FROM pengguna WHERE id=?");$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
        $rows=db()->query("SELECT * FROM pengguna ORDER BY id DESC")->fetchAll();
        view('pengguna/index',compact('rows','edit'));
    }
}

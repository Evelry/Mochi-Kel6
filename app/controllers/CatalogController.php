<?php
class CatalogController {
    public function index() {
        $rows=db()->query("SELECT p.*,k.nama_kategori FROM produk p JOIN kategori k ON k.id=p.kategori_id WHERE p.status='aktif' ORDER BY p.id DESC")->fetchAll();
        view('catalog/index',compact('rows'));
    }
}

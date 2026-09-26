<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title??'Mochi ERP')?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{--pink:#ff6b9d;--dark:#241b2f;--cream:#fff9f4}
body{background:#f7f7fb;color:#282433;font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif}
.sidebar{width:250px;min-height:100vh;background:linear-gradient(180deg,#251b32,#3b294b);position:fixed;left:0;top:0;color:#fff;padding:22px 14px;z-index:10}
.brand{font-weight:800;font-size:1.35rem;padding:8px 12px 22px}.brand span{color:#ff8fb4}
.nav-link{color:#ddd3e5;border-radius:12px;margin:4px 0;padding:10px 12px}.nav-link:hover,.nav-link.active{background:#ff6b9d;color:#fff}
.main{margin-left:250px;min-height:100vh}.topbar{height:72px;background:#fff;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;padding:0 30px}
.content{padding:28px}.card{border:0;border-radius:18px;box-shadow:0 5px 24px rgba(35,25,45,.06)}.stat{padding:20px}.stat-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:#fff0f5;color:#e54d84;font-size:1.3rem}
.table> :not(caption)>*>*{padding:13px 12px}.badge{font-weight:600}.product-img{width:52px;height:52px;object-fit:cover;border-radius:12px;background:#fff0f5}
.login-wrap{min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#fff0f5,#f5efff)}.login-card{max-width:430px;width:100%}
.btn-pink{background:#ff6b9d;border-color:#ff6b9d;color:#fff}.btn-pink:hover{background:#e95589;border-color:#e95589;color:#fff}
.menu-card{border-radius:20px;overflow:hidden;transition:transform .15s}.menu-card:hover{transform:translateY(-3px)}
.menu-img{height:170px;background:linear-gradient(135deg,#fff0f5,#f5efff);position:relative}.menu-img img{max-height:100%;max-width:100%;object-fit:cover;position:relative;z-index:1}.menu-emoji{position:absolute;font-size:3.5rem;opacity:.5}
.menu-thumb{width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#fff0f5,#f5efff);position:relative;flex-shrink:0}.menu-thumb img{width:100%;height:100%;object-fit:cover;border-radius:14px;position:relative;z-index:1}.menu-emoji-sm{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:1.4rem;opacity:.5}
@media(max-width:900px){.sidebar{position:static;width:100%;min-height:auto}.main{margin-left:0}.nav{display:flex;overflow:auto}.nav-link{white-space:nowrap}.topbar{padding:0 16px}.content{padding:18px}}
</style>
</head><body>
<?php if(auth()): ?>
<aside class="sidebar">
<div class="brand">🍡 Mochi<span>ERP</span></div>
<nav class="nav flex-column">
<a class="nav-link <?=($_GET['route']??'home')==='home'?'active':''?>" href="<?=base_url('index.php?route=home')?>"><i class="bi bi-<?=auth()['peran']==='admin'?'grid':'shop'?> me-2"></i><?=auth()['peran']==='admin'?'Dashboard':'Menu'?></a>
<?php if(auth()['peran']!=='admin'): ?>
<a class="nav-link <?=($_GET['route']??'')==='keranjang'?'active':''?>" href="<?=base_url('index.php?route=keranjang')?>"><i class="bi bi-cart3 me-2"></i>Keranjang<?php $ck=cart_count(); if($ck>0): ?> <span class="badge rounded-pill text-bg-light ms-1"><?=$ck?></span><?php endif; ?></a>
<?php endif; ?>
<?php if(auth()['peran']==='admin'): ?>
<div class="text-uppercase small text-white-50 px-2 mt-3 mb-1">Master</div>
<a class="nav-link" href="<?=base_url('index.php?route=produk')?>"><i class="bi bi-box-seam me-2"></i>Produk</a>
<a class="nav-link" href="<?=base_url('index.php?route=kategori')?>"><i class="bi bi-tags me-2"></i>Kategori</a>
<a class="nav-link" href="<?=base_url('index.php?route=pengguna')?>"><i class="bi bi-people me-2"></i>Pengguna</a>
<div class="text-uppercase small text-white-50 px-2 mt-3 mb-1">Transaksi</div>
<a class="nav-link" href="<?=base_url('index.php?route=pesanan')?>"><i class="bi bi-receipt me-2"></i>Pesanan</a>
<?php endif; ?>
</nav>
</aside>
<div class="main"><header class="topbar"><div><strong><?=e($title??'Dashboard')?></strong></div><div class="d-flex align-items-center gap-3"><span class="small text-muted"><?=e(auth()['nama_lengkap'])?></span><a class="btn btn-sm btn-outline-secondary" href="<?=base_url('index.php?route=logout')?>">Keluar</a></div></header>
<main class="content">
<?php if($f=flash()): ?><div class="alert alert-<?=$f['type']?> alert-dismissible fade show"><?=$f['message']?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php endif; ?>

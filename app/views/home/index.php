<?php $title='Dashboard'; require __DIR__.'/../partials/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><h3 class="fw-bold mb-1">Dashboard</h3><div class="text-muted">Ringkasan operasional Toko Mochi</div></div></div>
<div class="row g-3 mb-4">
<?php foreach([['produk','Produk','bi-box-seam'],['pesanan','Pesanan','bi-receipt'],['pengguna','Pengguna','bi-people']] as $s): ?>
<div class="col-6 col-xl-4"><div class="card stat"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small"><?=$s[1]?></div><div class="fs-3 fw-bold"><?=$stats[$s[0]]?></div></div><div class="stat-icon"><i class="bi <?=$s[2]?>"></i></div></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-4">
<div class="col-lg-12"><div class="card p-3"><div class="d-flex justify-content-between"><h5 class="fw-bold">Pesanan Terbaru</h5><a href="<?=base_url('index.php?route=pesanan')?>" class="small">Lihat semua</a></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach($orders as $o): ?><tr><td><strong><?=e($o['no_pesanan'])?></strong><div class="small text-muted"><?=date('d M Y H:i',strtotime($o['tanggal_pesanan']))?></div></td><td><?=e($o['nama_lengkap'])?></td><td><?=rupiah($o['total_bayar'])?></td><td><span class="badge text-bg-light"><?=str_replace('_',' ',e($o['status_pesanan']))?></span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>
<div class="card p-4 mt-4"><div class="text-muted">Omzet pesanan lunas</div><div class="fs-2 fw-bold"><?=rupiah($stats['omzet'])?></div></div>
<?php require __DIR__.'/../partials/footer.php'; ?>
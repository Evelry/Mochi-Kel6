<?php $title='Menu'; require __DIR__.'/../partials/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <div>
    <h3 class="fw-bold mb-1">Halo, <?=e(explode(' ', auth()['nama_lengkap'])[0])?> 👋</h3>
    <div class="text-muted">Yuk pilih mochi favoritmu hari ini.</div>
  </div>
</div>

<?php if(empty($rows)): ?>
<div class="card p-5 text-center text-muted"><i class="bi bi-emoji-frown fs-1 d-block mb-2"></i>Menu tidak ditemukan.</div>
<?php else: ?>
<div class="row g-4">
<?php foreach($rows as $p):
  $opsi = array_map('trim', explode(',', $p['varian_isi']));
  $habis = $p['stok'] <= 0;
?>
<div class="col-md-6 col-xl-4">
<div class="card h-100 menu-card">
  <div class="menu-img d-flex align-items-center justify-content-center">
    <img src="<?=base_url('uploads/'.e($p['gambar']))?>" alt="" onerror="this.style.display='none'">
    <span class="menu-emoji">🍡</span>
  </div>
  <div class="card-body d-flex flex-column">
    <div class="small text-muted"><?=e($p['nama_kategori'])?></div>
    <h5 class="fw-bold mb-1"><?=e($p['nama_produk'])?></h5>
    <p class="small text-muted flex-grow-1"><?=e(mb_strimwidth($p['deskripsi']??'',0,90,'...'))?></p>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="fw-bold fs-5 text-danger-emphasis"><?=rupiah($p['harga_jual'])?></div>
      <?php if($habis): ?><span class="badge text-bg-secondary">Stok habis</span>
      <?php elseif($p['stok']<=$p['stok_minimum']): ?><span class="badge text-bg-warning">Sisa <?=$p['stok']?></span>
      <?php else: ?><span class="badge text-bg-light">Stok <?=$p['stok']?></span><?php endif; ?>
    </div>
    <?php if(!$habis): ?>
    <form method="post" action="<?=base_url('index.php?route=keranjang')?>" class="d-flex gap-2">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="tambah">
      <input type="hidden" name="produk_id" value="<?=$p['id']?>">
      <select class="form-select form-select-sm" name="varian" style="max-width:120px">
        <?php foreach($opsi as $o): ?><option value="<?=e($o)?>"><?=e($o)?></option><?php endforeach; ?>
      </select>
      <input class="form-control form-control-sm" style="max-width:64px" type="number" name="qty" value="1" min="1" max="<?=$p['stok']?>">
      <button class="btn btn-sm btn-pink flex-grow-1"><i class="bi bi-cart-plus"></i> Tambah</button>
    </form>
    <?php else: ?>
    <button class="btn btn-sm btn-outline-secondary" disabled>Stok Habis</button>
    <?php endif; ?>
  </div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/footer.php'; ?>

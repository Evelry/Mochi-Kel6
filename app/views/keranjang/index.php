<?php $title='Keranjang'; require __DIR__.'/../partials/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h3 class="fw-bold mb-1">Keranjang Saya</h3><div class="text-muted">Periksa kembali pesananmu sebelum checkout.</div></div>
  <a class="btn btn-outline-dark" href="<?=base_url('index.php?route=home')?>"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
</div>

<?php if(empty($items)): ?>
<div class="card p-5 text-center text-muted">
  <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
  Keranjang kamu masih kosong.
  <div class="mt-3"><a class="btn btn-pink" href="<?=base_url('index.php?route=home')?>">Lihat Menu</a></div>
</div>
<?php else: ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card p-3">
    <?php foreach($items as $key => $it): ?>
      <div class="d-flex align-items-center gap-3 border-bottom py-3">
        <div class="menu-thumb d-flex align-items-center justify-content-center">
          <img src="<?=base_url('uploads/'.e($it['gambar']))?>" onerror="this.style.display='none'"><span class="menu-emoji-sm">🍡</span>
        </div>
        <div class="flex-grow-1">
          <strong><?=e($it['nama_produk'])?></strong>
          <div class="small text-muted">Varian: <?=e($it['varian'])?></div>
          <div class="fw-bold"><?=rupiah($it['harga'])?></div>
        </div>
        <form method="post" action="<?=base_url('index.php?route=keranjang')?>" class="d-flex align-items-center gap-2">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="key" value="<?=e($key)?>">
          <input class="form-control form-control-sm" style="width:64px" type="number" name="qty" value="<?=(int)$it['qty']?>" min="1" max="<?=(int)$it['stok_tersedia']?>" onchange="this.form.submit()">
        </form>
        <div class="fw-bold" style="min-width:110px;text-align:right"><?=rupiah($it['harga']*$it['qty'])?></div>
        <form method="post" action="<?=base_url('index.php?route=keranjang')?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="hapus">
          <input type="hidden" name="key" value="<?=e($key)?>">
          <button class="btn btn-sm btn-outline-danger" data-confirm="Hapus item ini?"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-4">
      <h5 class="fw-bold mb-3">Ringkasan</h5>
      <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><strong><?=rupiah($subtotal)?></strong></div>
      <div class="small text-muted mb-3">Ongkos kirim dihitung di halaman checkout.</div>
      <a class="btn btn-pink w-100" href="<?=base_url('index.php?route=checkout')?>">Checkout <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__.'/../partials/footer.php'; ?>

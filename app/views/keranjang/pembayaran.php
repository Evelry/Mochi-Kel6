<?php $title='Pembayaran'; require __DIR__.'/../partials/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1">Pembayaran Pesanan</h3>
    <div class="text-muted">No. Pesanan: <strong><?=e($pesanan['no_pesanan'])?></strong></div>
  </div>
  <a class="btn btn-outline-dark" href="<?=base_url('index.php?route=home')?>"><i class="bi bi-shop"></i> Kembali ke Menu</a>
</div>

<div class="row g-4">
  <div class="col-lg-7">

    <?php if($pesanan['status_pembayaran']==='lunas'): ?>
    <div class="card p-4 mb-4 text-center">
      <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
      <h4 class="fw-bold mt-2">Pembayaran Diterima</h4>
      <p class="text-muted mb-0">Pesananmu sedang diproses. Terima kasih! 🍡</p>
    </div>

    <?php elseif($pesanan['metode_pembayaran']==='transfer_bank'): ?>
    <div class="card p-4 mb-4">
      <h5 class="fw-bold mb-3"><i class="bi bi-bank me-1"></i> Transfer Bank</h5>
      <p class="text-muted">Silakan transfer sejumlah tepat ke salah satu rekening berikut, lalu unggah bukti transfer di bawah.</p>
      <div class="border rounded-3 p-3 mb-2 d-flex justify-content-between align-items-center">
        <div><div class="small text-muted">Bank BCA</div><strong>1234 5678 90</strong><div class="small">a.n. Mochi Store Indonesia</div></div>
        <span class="badge text-bg-light">Disarankan</span>
      </div>
      <div class="border rounded-3 p-3 mb-3">
        <div class="small text-muted">Bank Mandiri</div><strong>0987 6543 21</strong><div class="small">a.n. Mochi Store Indonesia</div>
      </div>
      <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-3">
        <span class="text-muted">Total yang harus dibayar</span>
        <span class="fs-4 fw-bold text-danger-emphasis"><?=rupiah($pesanan['total_bayar'])?></span>
      </div>
    </div>

    <div class="card p-4">
      <h5 class="fw-bold mb-3">Unggah Bukti Transfer</h5>
      <?php if($pesanan['bukti_transfer']): ?>
        <div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>Bukti sudah diunggah, menunggu verifikasi admin. Kamu bisa mengunggah ulang jika perlu.</div>
      <?php endif; ?>
      <form method="post" action="<?=base_url('index.php?route=pembayaran&id='.$pesanan['id'])?>" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="upload_bukti">
        <input class="form-control" style="max-width:320px" type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
        <button class="btn btn-pink">Kirim Bukti</button>
      </form>
    </div>

    <?php else: ?>
    <div class="card p-4">
      <h5 class="fw-bold mb-3"><i class="bi bi-cash-coin me-1"></i> Bayar di Tempat (COD)</h5>
      <p class="text-muted">Siapkan uang tunai sejumlah total pesanan saat kurir mengantarkan pesananmu.</p>
      <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-3 mb-3">
        <span class="text-muted">Total yang harus dibayar</span>
        <span class="fs-4 fw-bold text-danger-emphasis"><?=rupiah($pesanan['total_bayar'])?></span>
      </div>
      <?php if($pesanan['status_pesanan']==='menunggu_pembayaran'): ?>
      <form method="post" action="<?=base_url('index.php?route=pembayaran&id='.$pesanan['id'])?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="konfirmasi_cod">
        <button class="btn btn-pink">Konfirmasi Pesanan COD</button>
      </form>
      <?php else: ?>
      <div class="alert alert-success small mb-0"><i class="bi bi-check-circle me-1"></i>Pesanan dikonfirmasi, menunggu pengiriman.</div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>

  <div class="col-lg-5">
    <div class="card p-4">
      <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>
      <?php foreach($items as $it): ?>
      <div class="d-flex justify-content-between small mb-2">
        <span><?=e($it['nama_produk'])?> (<?=e($it['varian_dipilih'])?>) x<?=$it['kuantitas']?></span>
        <strong><?=rupiah($it['subtotal'])?></strong>
      </div>
      <?php endforeach; ?>
      <hr>
      <div class="d-flex justify-content-between mb-1"><span class="text-muted">Subtotal</span><span><?=rupiah($pesanan['total_belanja'])?></span></div>
      <div class="d-flex justify-content-between mb-1"><span class="text-muted">Ongkir</span><span><?=$pesanan['ongkir']==0?'Gratis':rupiah($pesanan['ongkir'])?></span></div>
      <hr>
      <div class="d-flex justify-content-between mb-3 fs-5"><strong>Total</strong><strong><?=rupiah($pesanan['total_bayar'])?></strong></div>
      <div class="small text-muted mb-1">Status Pesanan</div>
      <span class="badge text-bg-light mb-3"><?=str_replace('_',' ',e($pesanan['status_pesanan']))?></span>
      <div class="small text-muted">Dikirim ke</div>
      <div class="fw-semibold"><?=e($pesanan['nama_penerima'])?></div>
      <div class="small"><?=e($pesanan['alamat_pengiriman'])?></div>
    </div>
  </div>
</div>
<?php require __DIR__.'/../partials/footer.php'; ?>

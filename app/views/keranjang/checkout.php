<?php $title='Checkout'; require __DIR__.'/../partials/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h3 class="fw-bold mb-1">Checkout</h3><div class="text-muted">Lengkapi data pengiriman dan pilih metode pembayaran.</div></div>
  <a class="btn btn-outline-dark" href="<?=base_url('index.php?route=keranjang')?>"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form method="post" action="<?=base_url('index.php?route=checkout')?>">
<?php echo csrf_field(); ?>
<div class="row g-4">
  <div class="col-lg-7">
    <div class="card p-4 mb-4">
      <h5 class="fw-bold mb-3">Alamat Pengiriman</h5>
      <div class="mb-3"><label class="form-label">Nama Penerima</label><input class="form-control" name="nama_penerima" required value="<?=e($u['nama_lengkap'])?>"></div>
      <div class="mb-3"><label class="form-label">No. Telepon</label><input class="form-control" name="no_telp_penerima" required value="<?=e($u['no_telp'])?>"></div>
      <div class="mb-3"><label class="form-label">Alamat Lengkap</label><textarea class="form-control" name="alamat_pengiriman" rows="3" required><?=e($u['alamat'])?></textarea></div>
      <div class="mb-3">
        <label class="form-label">Kurir</label>
        <select class="form-select" name="kurir">
          <?php foreach(['JNE Regular','J&T Express','SiCepat BEST','AnterAja'] as $k): ?>
          <option value="<?=$k?>"><?=$k?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-1"><label class="form-label">Catatan (opsional)</label><textarea class="form-control" name="catatan" rows="2" placeholder="Contoh: titip di pos satpam"></textarea></div>
    </div>

    <div class="card p-4">
      <h5 class="fw-bold mb-3">Metode Pembayaran</h5>
      <div class="form-check border rounded-3 p-3 mb-2">
        <input class="form-check-input" type="radio" name="metode_pembayaran" id="mtb" value="transfer_bank" checked>
        <label class="form-check-label w-100" for="mtb"><strong>Transfer Bank</strong><div class="small text-muted">Transfer ke rekening toko lalu unggah bukti pembayaran.</div></label>
      </div>
      <div class="form-check border rounded-3 p-3">
        <input class="form-check-input" type="radio" name="metode_pembayaran" id="mcod" value="cod">
        <label class="form-check-label w-100" for="mcod"><strong>COD (Bayar di Tempat)</strong><div class="small text-muted">Bayar tunai saat pesanan diterima kurir.</div></label>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card p-4">
      <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>
      <?php foreach($items as $it): ?>
      <div class="d-flex justify-content-between small mb-2">
        <span><?=e($it['nama_produk'])?> (<?=e($it['varian'])?>) x<?=$it['qty']?></span>
        <strong><?=rupiah($it['harga']*$it['qty'])?></strong>
      </div>
      <?php endforeach; ?>
      <hr>
      <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><span><?=rupiah($subtotal)?></span></div>
      <div class="d-flex justify-content-between mb-2"><span class="text-muted">Ongkos Kirim</span><span><?=$ongkir==0?'Gratis':rupiah($ongkir)?></span></div>
      <hr>
      <div class="d-flex justify-content-between mb-4 fs-5"><strong>Total</strong><strong class="text-danger-emphasis"><?=rupiah($total)?></strong></div>
      <button type="button" class="btn btn-pink w-100 btn-lg">Buat Pesanan</button>
    </div>
  </div>
</div>
</form>
<?php require __DIR__.'/../partials/footer.php'; ?>

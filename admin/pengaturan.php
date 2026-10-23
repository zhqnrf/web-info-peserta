<?php
defined('APP') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $warna = $_POST['warna'] ?? '';
        $new = [
            'site_name' => trim($_POST['site_name'] ?? '') ?: 'Info Peserta',
            'tagline' => trim($_POST['tagline'] ?? ''),
            'warna' => preg_match('/^#[0-9a-fA-F]{6}$/', $warna) ? $warna : S('warna'),
            'tujuan' => trim($_POST['tujuan'] ?? ''),
            'tanggal' => trim($_POST['tanggal'] ?? ''),
            'mulai' => trim($_POST['mulai'] ?? ''),
            'kumpul' => trim($_POST['kumpul'] ?? ''),
            'maps' => preg_match('#^https?://#i', trim($_POST['maps'] ?? '')) ? trim($_POST['maps']) : '',
            'sambutan' => trim(str_replace("\r", '', $_POST['sambutan'] ?? '')),
            'tampil_countdown' => !empty($_POST['tampil_countdown']),
            'tampil_penempatan' => !empty($_POST['tampil_penempatan']),
            'label_kamar' => trim($_POST['label_kamar'] ?? '') ?: 'Kamar',
            'label_bus' => trim($_POST['label_bus'] ?? '') ?: 'Bus',
        ];
        if (!empty($_POST['hapus_logo'])) { delete_upload(S('logo')); $new['logo'] = ''; }
        if ($logo = upload_image('logo')) { delete_upload(S('logo')); $new['logo'] = $logo; }
        save_settings($new);
        flash('Pengaturan tersimpan.');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'err');
    }
    redirect('admin.php?p=pengaturan');
}

$swatches = ['#0f766e', '#0369a1', '#4338ca', '#7e22ce', '#be123c', '#c2410c', '#a16207', '#15803d', '#334155'];
$GLOBALS['head_actions'] = '<a class="btn secondary" href="index.php" target="_blank">' . icon('eye', 18) . ' Lihat website</a>';
?>
<form method="post" enctype="multipart/form-data" class="stack">
  <?= csrf_field() ?>

  <div class="card">
    <h2><?= icon('palette', 18) ?> Identitas & tampilan</h2>
    <p class="muted">Nama, logo, dan warna yang dilihat peserta.</p>
    <div class="grid-form">
      <label for="site_name">Nama acara / website<input id="site_name" name="site_name" value="<?= e(S('site_name')) ?>" required></label>
      <label for="tagline">Tagline<input id="tagline" name="tagline" value="<?= e(S('tagline')) ?>"></label>
      <div>
        <label for="warna">Warna utama</label>
        <div class="color-row" style="margin-top:6px"><input id="warna" type="color" name="warna" value="<?= e(S('warna')) ?>" style="margin:0">
          <div class="swatches" style="margin:0">
            <?php foreach ($swatches as $s): ?><button type="button" style="background:<?= $s ?>" aria-label="Pakai warna <?= $s ?>" onclick="document.getElementById('warna').value='<?= $s ?>'"></button><?php endforeach; ?>
          </div>
        </div>
      </div>
      <div>
        <label for="logo">Logo <span class="opt">(opsional, persegi)</span></label>
        <div class="logo-row">
          <?php if (S('logo')): ?><img src="<?= e(S('logo')) ?>" alt="Logo"><?php endif; ?>
          <div class="grow">
            <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" style="margin:0">
            <?php if (S('logo')): ?><label class="check" style="margin-top:8px"><input type="checkbox" name="hapus_logo" value="1"> Hapus logo</label><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <h2><?= icon('plane', 18) ?> Detail acara</h2>
    <p class="muted">Tampil di kartu utama halaman Beranda.</p>
    <div class="grid-form">
      <label for="tujuan">Tujuan<input id="tujuan" name="tujuan" value="<?= e(S('tujuan')) ?>"></label>
      <label for="tanggal">Tanggal (teks)<input id="tanggal" name="tanggal" value="<?= e(S('tanggal')) ?>"><span class="help">Contoh: 14 – 16 November 2026</span></label>
      <label for="mulai">Waktu keberangkatan<input id="mulai" type="datetime-local" name="mulai" value="<?= e(S('mulai')) ?>"><span class="help">Untuk hitung mundur di Beranda.</span></label>
      <label for="kumpul">Titik kumpul<input id="kumpul" name="kumpul" value="<?= e(S('kumpul')) ?>"></label>
      <label for="maps" class="full">Link Google Maps titik kumpul<input id="maps" type="url" name="maps" value="<?= e(S('maps')) ?>" placeholder="https://maps.app.goo.gl/..."><span class="help">Buka Google Maps, pilih lokasi, tekan Bagikan, lalu salin link ke sini.</span></label>
      <label for="sambutan" class="full">Kata sambutan<textarea id="sambutan" name="sambutan" rows="3"><?= e(S('sambutan')) ?></textarea></label>
    </div>
  </div>

  <div class="card">
    <h2><?= icon('settings', 18) ?> Bagian halaman peserta</h2>
    <p class="muted">Atur bagian yang tampil di Beranda.</p>
    <div class="grid-form">
      <label class="full"><span class="check"><input type="checkbox" name="tampil_countdown" value="1" <?= S('tampil_countdown') ? 'checked' : '' ?>> Tampilkan hitung mundur keberangkatan</span></label>
      <label class="full"><span class="check"><input type="checkbox" name="tampil_penempatan" value="1" <?= S('tampil_penempatan') ? 'checked' : '' ?>> Tampilkan kartu penempatan (kamar & bus) untuk tiap peserta</span></label>
      <label for="label_kamar">Nama penempatan 1<input id="label_kamar" name="label_kamar" value="<?= e(S('label_kamar')) ?>"><span class="help">Contoh: Kamar, Tenda, Meja</span></label>
      <label for="label_bus">Nama penempatan 2<input id="label_bus" name="label_bus" value="<?= e(S('label_bus')) ?>"><span class="help">Contoh: Bus, Kelompok, Tim</span></label>
    </div>
  </div>

  <div class="actions"><button class="btn"><?= icon('check', 18) ?> Simpan pengaturan</button></div>
</form>

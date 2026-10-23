<?php
defined('APP') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'password') {
        $new = (string)($_POST['new'] ?? '');
        if (DEMO_MODE) flash('Ganti password dinonaktifkan di mode demo.', 'err');
        elseif (!admin_verify((string)($_POST['old'] ?? ''))) flash('Password lama salah.', 'err');
        elseif (strlen($new) < 8) flash('Password baru minimal 8 karakter.', 'err');
        elseif ($new !== ($_POST['confirm'] ?? '')) flash('Konfirmasi password tidak sama.', 'err');
        else { data_put('auth', ['hash' => password_hash($new, PASSWORD_DEFAULT)]); flash('Password berhasil diganti.'); }
    }

    if ($action === 'clear_peserta' && ($_POST['ketik'] ?? '') === 'HAPUS') {
        save_peserta([]);
        flash('Semua data peserta dihapus.');
    } elseif ($action === 'clear_peserta') {
        flash('Ketik HAPUS untuk konfirmasi.', 'err');
    }

    if ($action === 'reset' && DEMO_MODE) {
        seed_data(true);
        flash('Data demo dikembalikan seperti semula.');
    }
    redirect('admin.php?p=akun');
}
?>
<div class="grid-2">
  <form method="post" class="card">
    <?= csrf_field() ?>
    <h2><?= icon('lock', 18) ?> Ganti password admin</h2>
    <?php if (DEMO_MODE): ?>
      <p class="muted">Dinonaktifkan di mode demo. Ubah <code>DEMO_MODE</code> menjadi <code>false</code> di <code>config.php</code> untuk mengaktifkan.</p>
    <?php else: ?>
      <p class="muted">Minimal 8 karakter.</p>
    <?php endif; ?>
    <div class="form">
      <label for="old">Password lama<input id="old" type="password" name="old" autocomplete="current-password" <?= DEMO_MODE ? 'disabled' : 'required' ?>></label>
      <label for="new">Password baru<input id="new" type="password" name="new" minlength="8" autocomplete="new-password" <?= DEMO_MODE ? 'disabled' : 'required' ?>></label>
      <label for="confirm">Ulangi password baru<input id="confirm" type="password" name="confirm" autocomplete="new-password" <?= DEMO_MODE ? 'disabled' : 'required' ?>></label>
      <div><button class="btn" name="action" value="password" <?= DEMO_MODE ? 'disabled' : '' ?>><?= icon('check', 18) ?> Ganti password</button></div>
    </div>
  </form>

  <div class="stack">
    <div class="card">
      <h2><?= icon('database', 18) ?> Backup data</h2>
      <p class="muted">Unduh semua pengaturan, konten, dan data peserta dalam satu file.</p>
      <div class="actions">
        <a class="btn secondary" href="?p=akun&backup=1"><?= icon('download', 18) ?> Backup (JSON)</a>
        <a class="btn secondary" href="?p=peserta&export=1"><?= icon('download', 18) ?> Peserta (Excel)</a>
      </div>
    </div>

    <?php if (DEMO_MODE): ?>
    <form method="post" class="card">
      <?= csrf_field() ?>
      <h2><?= icon('refresh', 18) ?> Reset data demo</h2>
      <p class="muted">Kembalikan semua konten, pengaturan, dan peserta contoh seperti awal.</p>
      <button class="btn secondary" name="action" value="reset" data-confirm="Kembalikan semua data demo seperti semula?"><?= icon('refresh', 18) ?> Reset demo</button>
    </form>
    <?php endif; ?>

    <form method="post" class="card">
      <?= csrf_field() ?>
      <h2><?= icon('trash', 18) ?> Kosongkan data peserta</h2>
      <p class="muted">Hapus semua peserta, misalnya setelah acara selesai atau sebelum acara baru. Konten tidak ikut terhapus. Backup dulu jika perlu.</p>
      <div class="row">
        <input name="ketik" placeholder="Ketik HAPUS" aria-label="Ketik HAPUS untuk konfirmasi" style="margin:0">
        <button class="btn danger" name="action" value="clear_peserta"><?= icon('trash', 18) ?> Hapus</button>
      </div>
    </form>
  </div>
</div>

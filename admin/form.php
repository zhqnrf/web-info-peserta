<?php
defined('APP') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['col'] ?? '') === 'fields') collection_handle('fields');

    if (($_POST['action'] ?? '') === 'settings') {
        $dept = lines(str_replace("\r", '', $_POST['departemen'] ?? ''));
        if (!$dept) {
            flash('Isi minimal satu pilihan ' . strtolower(S('label_departemen')) . '.', 'err');
        } else {
            save_settings([
                'registrasi_buka' => !empty($_POST['registrasi_buka']),
                'pesan_tutup' => trim($_POST['pesan_tutup'] ?? ''),
                'label_departemen' => trim($_POST['label_departemen'] ?? '') ?: 'Departemen',
                'departemen' => array_values(array_unique($dept)),
            ]);
            flash('Pengaturan registrasi tersimpan.');
        }
        redirect('admin.php?p=form');
    }
}
$GLOBALS['head_actions'] = '<a class="btn secondary" href="index.php" target="_blank">' . icon('eye', 18) . ' Lihat form</a>';
?>
<div class="stack">
  <form method="post" class="card">
    <?= csrf_field() ?>
    <h2>Pengaturan registrasi</h2>
    <p class="muted">Nama dan No. HP selalu diminta. Atur pilihan lain di bawah.</p>
    <div class="grid-form">
      <label class="full"><span class="check"><input type="checkbox" name="registrasi_buka" value="1" <?= S('registrasi_buka') ? 'checked' : '' ?>> Registrasi dibuka (matikan untuk menutup pendaftaran baru)</span></label>
      <label for="pesan_tutup" class="full">Pesan saat registrasi ditutup<input id="pesan_tutup" name="pesan_tutup" value="<?= e(S('pesan_tutup')) ?>"></label>
      <label for="label_departemen">Nama isian kelompok<input id="label_departemen" name="label_departemen" value="<?= e(S('label_departemen')) ?>"><span class="help">Contoh: Departemen, Divisi, Unit, Cabang, Kelas</span></label>
      <label for="departemen">Pilihan <?= e(strtolower(S('label_departemen'))) ?><textarea id="departemen" name="departemen" rows="8"><?= e(implode("\n", S('departemen'))) ?></textarea><span class="help">Satu pilihan per baris.</span></label>
    </div>
    <div class="actions" style="margin-top:16px"><button class="btn" name="action" value="settings"><?= icon('check', 18) ?> Simpan</button></div>
  </form>

  <div>
    <div class="card-head"><div><h2 style="font-size:16px">Field tambahan</h2><p class="muted">Pertanyaan tambahan di form registrasi, misalnya ukuran kaos, NIK, atau nama pendamping. Jawaban ikut ter-export ke Excel.</p></div></div>
    <?= collection_editor('fields') ?>
  </div>
</div>
<?= collection_script() ?>

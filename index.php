<?php
require_once __DIR__ . '/lib.php';

if (current_peserta()) redirect('home.php');

$buka = !empty(S('registrasi_buka'));
$mode = (($_GET['mode'] ?? '') === 'masuk' || !$buka) ? 'masuk' : 'daftar';
$fields = K('fields');
$departemen = S('departemen');
$error = '';
$old = ['nama' => '', 'departemen' => '', 'hp' => '', 'extra' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hp = normalize_hp($_POST['hp'] ?? '');

    if ($mode === 'masuk') {
        $old['hp'] = $_POST['hp'] ?? '';
        $p = find_by('hp', $hp);
        if ($p) { login_peserta($p); redirect('home.php'); }
        $error = $buka ? 'Nomor HP belum terdaftar. Silakan registrasi dulu.' : 'Nomor HP tidak ditemukan. Hubungi panitia.';
    } else {
        $old = [
            'nama' => trim($_POST['nama'] ?? ''),
            'departemen' => trim($_POST['departemen'] ?? ''),
            'hp' => $_POST['hp'] ?? '',
        ];
        if (mb_strlen($old['nama']) < 3) $error = 'Nama minimal 3 huruf.';
        elseif (!in_array($old['departemen'], $departemen, true)) $error = 'Pilih ' . strtolower(S('label_departemen')) . '.';
        elseif (!valid_hp($hp)) $error = 'Nomor HP tidak valid. Contoh: 081234567890.';
        $old['extra'] = collect_extra($fields, $error);

        if (!$error) {
            $p = find_by('hp', $hp);
            if (!$p) {
                $p = [
                    'id' => uid(),
                    'token' => bin2hex(random_bytes(16)),
                    'nama' => mb_substr($old['nama'], 0, 100),
                    'departemen' => $old['departemen'],
                    'hp' => $hp,
                    'kamar' => '', 'bus' => '', 'catatan' => '',
                    'extra' => $old['extra'],
                    'waktu' => date('Y-m-d H:i:s'),
                ];
                $list = load_peserta();
                $list[] = $p;
                save_peserta($list);
            }
            login_peserta($p);
            redirect('home.php');
        }
    }
}

page_head(S('site_name'));
?>
<div class="app app-auth">
  <header class="hero">
    <div class="hero-mark"><?= brand_mark(28) ?></div>
    <h1><?= e(S('site_name')) ?></h1>
    <p><?= e(S('tagline')) ?></p>
    <div class="hero-meta">
      <span><?= icon('pin', 15) ?><?= e(S('tujuan')) ?></span>
      <span><?= icon('calendar', 15) ?><?= e(S('tanggal')) ?></span>
    </div>
  </header>

  <main class="card form-card">
    <?php if ($buka): ?>
      <nav class="segmented" aria-label="Pilih">
        <a href="index.php" class="<?= $mode === 'daftar' ? 'active' : '' ?>">Registrasi</a>
        <a href="?mode=masuk" class="<?= $mode === 'masuk' ? 'active' : '' ?>">Sudah daftar</a>
      </nav>
    <?php else: ?>
      <div class="alert alert-warn"><?= icon('lock', 18) ?><span><?= e(S('pesan_tutup')) ?></span></div>
    <?php endif; ?>

    <h2><?= $mode === 'daftar' ? 'Isi data diri' : 'Masuk dengan No. HP' ?></h2>
    <p class="muted"><?= $mode === 'daftar' ? 'Setelah registrasi, info perjalanan langsung bisa kamu lihat.' : 'Gunakan nomor HP yang dipakai saat registrasi.' ?></p>

    <?php if ($error): ?><div class="alert alert-err" role="alert"><?= icon('alert', 18) ?><span><?= e($error) ?></span></div><?php endif; ?>

    <form method="post" class="form" autocomplete="on">
      <?php if ($mode === 'daftar'): ?>
        <label for="nama">Nama lengkap
          <input id="nama" type="text" name="nama" value="<?= e($old['nama']) ?>" required minlength="3" maxlength="100" placeholder="Sesuai ID karyawan" autocomplete="name">
        </label>
        <label for="departemen"><?= e(S('label_departemen')) ?>
          <select id="departemen" name="departemen" required>
            <option value="">Pilih <?= e(strtolower(S('label_departemen'))) ?></option>
            <?php foreach ($departemen as $d): ?>
              <option <?= $old['departemen'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      <?php endif; ?>
      <label for="hp">No. HP / WhatsApp
        <input id="hp" type="tel" name="hp" value="<?= e($old['hp']) ?>" required inputmode="numeric" placeholder="081234567890" autocomplete="tel">
      </label>
      <?php if ($mode === 'daftar') foreach ($fields as $f) echo custom_field_input($f, $old['extra'][$f['id']] ?? ''); ?>
      <button class="btn btn-block" type="submit"><?= $mode === 'daftar' ? 'Daftar & lihat info' : 'Masuk' ?> <?= icon('chevron', 18) ?></button>
    </form>
  </main>

  <p class="foot">Data kamu hanya dipakai panitia untuk keperluan acara.</p>
</div>
<?php page_foot();

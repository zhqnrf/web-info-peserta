<?php
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/admin/collections.php';

$PAGES = [
    'dashboard'  => ['Dashboard', 'grid', 'Ringkasan registrasi peserta'],
    'peserta'    => ['Peserta', 'users', 'Data peserta, penempatan kamar & bus'],
    'konten'     => ['Konten', 'list', 'Pengumuman, jadwal, info, kontak & galeri'],
    'form'       => ['Form Registrasi', 'form', 'Isian yang diminta saat peserta mendaftar'],
    'pengaturan' => ['Pengaturan', 'settings', 'Identitas, tampilan & detail acara'],
    'akun'       => ['Akun & Data', 'lock', 'Password admin, backup & reset'],
];
$page = $_GET['p'] ?? 'dashboard';
if (!isset($PAGES[$page])) $page = 'dashboard';

// ---------- Login / logout ----------
if (isset($_GET['logout'])) { unset($_SESSION['admin']); redirect('admin.php'); }

if (!is_admin()) {
    $error = '';
    if (isset($_POST['password'])) {
        if (admin_verify((string)$_POST['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            redirect('admin.php');
        }
        sleep(1); // perlambat tebak-tebakan password
        $error = 'Password salah.';
    }
    page_head('Login Admin · ' . S('site_name'), 'admin');
    ?>
    <main class="admin-login">
      <span class="brand-dot"><?= brand_mark(24) ?></span>
      <h1>Panel Admin</h1>
      <p class="muted"><?= e(S('site_name')) ?></p>
      <div class="card">
        <?php if ($error): ?><div class="alert alert-err"><?= icon('alert', 18) ?><span><?= e($error) ?></span></div><?php endif; ?>
        <form method="post" class="form">
          <label for="password">Password<input id="password" type="password" name="password" required autofocus autocomplete="current-password"></label>
          <button class="btn btn-block"><?= icon('lock', 18) ?> Masuk</button>
        </form>
      </div>
      <?php if (DEMO_MODE): ?><p class="hint">Mode demo · password: <code><?= e(DEFAULT_ADMIN_PASSWORD) ?></code></p><?php endif; ?>
      <p class="hint"><a href="index.php">Kembali ke website</a></p>
    </main>
    <?php
    page_foot();
    exit;
}

// ---------- Proteksi CSRF untuk semua aksi admin ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok()) {
    flash('Sesi kedaluwarsa. Silakan ulangi.', 'err');
    redirect($_SERVER['REQUEST_URI']);
}

// ---------- Download (sebelum ada output HTML) ----------
if ($page === 'peserta' && isset($_GET['export'])) {
    $fields = K('fields');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="peserta-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
    fputcsv($out, array_merge(['No', 'Nama', S('label_departemen'), 'No HP', S('label_kamar'), S('label_bus')], array_column($fields, 'label'), ['Catatan', 'Waktu Daftar']), ';');
    foreach (load_peserta() as $i => $p) {
        $extra = array_map(fn($f) => $p['extra'][$f['id']] ?? '', $fields);
        // ="08xx" agar Excel tidak membuang angka 0 di depan
        fputcsv($out, array_merge([$i + 1, $p['nama'], $p['departemen'], '="' . $p['hp'] . '"',$p['kamar'] ?? '', $p['bus'] ?? ''], $extra, [$p['catatan'] ?? '', $p['waktu']]), ';');
    }
    exit;
}
if ($page === 'akun' && isset($_GET['backup'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="backup-' . date('Ymd-His') . '.json"');
    echo json_encode(['settings' => S(), 'konten' => data_get('konten'), 'peserta' => load_peserta()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------- Halaman ----------
ob_start();
include __DIR__ . "/admin/$page.php";
$body = ob_get_clean();

[$title, , $subtitle] = $PAGES[$page];
page_head($title . ' · Admin', 'admin');
?>
<div class="shell">
  <aside class="sidebar">
    <div class="side-brand">
      <span class="brand-dot"><?= brand_mark(18) ?></span>
      <div><strong><?= e(S('site_name')) ?></strong><small>Panel admin<?= DEMO_MODE ? ' · demo' : '' ?></small></div>
    </div>
    <nav class="side-nav">
      <?php foreach ($PAGES as $key => [$label, $ic]): ?>
        <a href="?p=<?= $key ?>" class="<?= $key === $page ? 'active' : '' ?>"><?= icon($ic, 18) ?> <?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="side-nav side-foot">
      <a href="index.php" target="_blank"><?= icon('external', 18) ?> <span class="hide-sm">Lihat website</span></a>
      <a href="?logout=1"><?= icon('logout', 18) ?> <span class="hide-sm">Keluar</span></a>
    </div>
  </aside>

  <main class="main">
    <div class="page-head">
      <div><h1><?= e($title) ?></h1><p><?= e($subtitle) ?></p></div>
      <?= $GLOBALS['head_actions'] ?? '' ?>
    </div>
    <?= flash_html() ?>
    <?= $body ?>
  </main>
</div>
<script>
// Konfirmasi sebelum hapus
document.addEventListener('submit', e => {
  const b = e.submitter;
  if (b && b.dataset.confirm && !confirm(b.dataset.confirm)) e.preventDefault();
});
// Di HP: geser menu agar yang aktif terlihat
document.querySelectorAll('.side-nav a.active, .tabs a.active').forEach(a => a.scrollIntoView({ inline: 'center', block: 'nearest' }));
</script>
<?php page_foot();

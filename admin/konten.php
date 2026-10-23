<?php
defined('APP') or exit;

$tabs = ['pengumuman', 'jadwal', 'info', 'kontak', 'galeri'];
$c = in_array($_GET['c'] ?? '', $tabs, true) ? $_GET['c'] : 'pengumuman';
$defs = collections();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['col'] ?? '') === $c) collection_handle($c);

$notes = [
    'pengumuman' => 'Tampil di halaman Beranda peserta. Pengumuman bertanda "Penting" selalu di paling atas.',
    'jadwal' => 'Satu kartu untuk satu hari. Urutan bisa diatur dengan tombol naik/turun.',
    'info' => 'Tampil sebagai daftar yang bisa dibuka-tutup di menu Info.',
    'kontak' => 'Peserta bisa langsung telepon atau chat WhatsApp dari menu Kontak.',
    'galeri' => 'Menu Galeri otomatis muncul jika ada minimal satu foto.',
];
$GLOBALS['head_actions'] = '<a class="btn secondary" href="home.php#' . ($c === 'pengumuman' ? 'beranda' : $c) . '" target="_blank">' . icon('eye', 18) . ' Lihat hasil</a>';
?>
<nav class="tabs">
  <?php foreach ($tabs as $t): ?>
    <a href="?p=konten&c=<?= $t ?>" class="<?= $t === $c ? 'active' : '' ?>"><?= icon($defs[$t]['icon'], 16) ?> <?= $defs[$t]['label'] ?> <span class="count"><?= count(K($t)) ?></span></a>
  <?php endforeach; ?>
</nav>
<p class="muted" style="margin-bottom:14px"><?= e($notes[$c]) ?></p>
<?= collection_editor($c) ?>
<?= collection_script() ?>

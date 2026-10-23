<?php
defined('APP') or exit;

$list = load_peserta();
$today = date('Y-m-d');
$hariIni = count(array_filter($list, fn($p) => strpos($p['waktu'], $today) === 0));
$perDept = array_count_values(array_column($list, 'departemen'));
arsort($perDept);
$max = max($perDept ?: [1]);
$belumKamar = count(array_filter($list, fn($p) => ($p['kamar'] ?? '') === ''));
$terbaru = array_slice(array_reverse($list), 0, 6);

$GLOBALS['head_actions'] = '<div class="actions"><a class="btn secondary" href="?p=peserta&export=1">' . icon('download', 18) . ' Export Excel</a>'
    . '<a class="btn" href="?p=konten&c=pengumuman">' . icon('plus', 18) . ' Buat pengumuman</a></div>';
?>
<div class="stack">
  <div class="stats">
    <div class="card stat"><span class="ic-box"><?= icon('users') ?></span><b><?= count($list) ?></b><small>Total peserta</small></div>
    <div class="card stat"><span class="ic-box"><?= icon('clock') ?></span><b><?= $hariIni ?></b><small>Daftar hari ini</small></div>
    <div class="card stat"><span class="ic-box"><?= icon('bed') ?></span><b><?= $belumKamar ?></b><small>Belum dapat <?= e(strtolower(S('label_kamar'))) ?></small></div>
    <div class="card stat"><span class="ic-box"><?= icon(S('registrasi_buka') ? 'check' : 'lock') ?></span>
      <b style="font-size:18px"><?= S('registrasi_buka') ? 'Dibuka' : 'Ditutup' ?></b><small>Status registrasi · <a href="?p=form">ubah</a></small></div>
  </div>

  <div class="grid-2">
    <div class="card">
      <div class="card-head"><h2>Per <?= e(strtolower(S('label_departemen'))) ?></h2></div>
      <?php if ($perDept): ?>
        <div class="bars">
          <?php foreach ($perDept as $d => $n): ?>
            <div class="bar-row"><span title="<?= e($d) ?>"><?= e($d) ?></span><div class="bar"><i style="width: <?= round($n / $max * 100) ?>%"></i></div><b><?= $n ?></b></div>
          <?php endforeach; ?>
        </div>
      <?php else: ?><p class="empty">Belum ada peserta.</p><?php endif; ?>
    </div>

    <div class="card">
      <div class="card-head"><h2>Registrasi terbaru</h2><a class="btn-sm" href="?p=peserta">Lihat semua</a></div>
      <?php if ($terbaru): ?>
        <ul class="list-plain">
          <?php foreach ($terbaru as $p): ?>
            <li><span class="avatar" style="width:36px;height:36px;font-size:14px"><?= e(mb_strtoupper(mb_substr($p['nama'], 0, 1))) ?></span>
              <div class="grow"><b><?= e($p['nama']) ?></b><small><?= e($p['departemen']) ?></small></div>
              <small><?= e(date('d M, H:i', strtotime($p['waktu']))) ?></small></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?><p class="empty">Belum ada peserta.</p><?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h2>Bagikan ke peserta</h2>
    <p class="muted">Kirim link ini ke grup WhatsApp peserta. Mereka cukup buka, registrasi, lalu semua info langsung tampil.</p>
    <?php $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/'; ?>
    <div class="row">
      <input type="text" readonly value="<?= e($url) ?>" id="share-url" style="margin:0">
      <button class="btn secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('share-url').value).then(() => this.textContent = 'Tersalin')">Salin</button>
    </div>
  </div>
</div>

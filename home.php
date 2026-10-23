<?php
require_once __DIR__ . '/lib.php';

$me = require_peserta();
$first = explode(' ', $me['nama'])[0];
$mulai = strtotime(S('mulai')) ?: 0;

$tabs = ['beranda' => ['Beranda', 'home'], 'jadwal' => ['Jadwal', 'calendar'], 'info' => ['Info', 'info']];
if (K('galeri')) $tabs['galeri'] = ['Galeri', 'image'];
$tabs['kontak'] = ['Kontak', 'phone'];

// Pengumuman penting tampil paling atas
$pengumuman = K('pengumuman');
usort($pengumuman, fn($a, $b) => (int)!empty($b['penting']) - (int)!empty($a['penting']));

page_head(S('site_name'));
?>
<div class="app">
  <header class="topbar">
    <div class="topbar-brand">
      <span class="brand-dot"><?= brand_mark(18) ?></span>
      <div><small>Halo,</small><strong><?= e($first) ?></strong></div>
    </div>
    <a class="chip" href="logout.php"><?= icon('logout', 14) ?> Keluar</a>
  </header>

  <main class="content">
    <!-- BERANDA -->
    <section id="beranda" class="tab active">
      <div class="trip">
        <small>Tujuan</small>
        <h2><?= e(S('tujuan')) ?></h2>
        <p class="trip-date"><?= icon('calendar', 15) ?> <?= e(S('tanggal')) ?></p>
        <?php if (S('tampil_countdown') && $mulai > time()): ?>
          <div class="countdown" data-ts="<?= $mulai * 1000 ?>">
            <div><b data-cd="d">0</b><span>hari</span></div>
            <div><b data-cd="h">0</b><span>jam</span></div>
            <div><b data-cd="m">0</b><span>menit</span></div>
          </div>
        <?php elseif ($mulai && $mulai <= time()): ?>
          <p class="trip-live"><span class="pulse"></span> Perjalanan sedang berlangsung</p>
        <?php endif; ?>
      </div>

      <?php if (S('sambutan')): ?><p class="welcome"><?= nl2br(e(S('sambutan'))) ?></p><?php endif; ?>

      <?php if (S('tampil_penempatan')): ?>
        <div class="card placement">
          <h3 class="card-title">Penempatan kamu</h3>
          <div class="placement-grid">
            <div><span class="ic-box"><?= icon('bed') ?></span><div><small><?= e(S('label_kamar')) ?></small><b><?= $me['kamar'] !== '' ? e($me['kamar']) : '<i>Belum ditentukan</i>' ?></b></div></div>
            <div><span class="ic-box"><?= icon('bus') ?></span><div><small><?= e(S('label_bus')) ?></small><b><?= $me['bus'] !== '' ? e($me['bus']) : '<i>Belum ditentukan</i>' ?></b></div></div>
          </div>
        </div>
      <?php endif; ?>

      <div class="card row">
        <span class="ic-box"><?= icon('pin') ?></span>
        <div class="grow"><small>Titik kumpul</small><p><?= e(S('kumpul')) ?></p></div>
        <?php if (S('maps')): ?><a class="btn-sm" href="<?= e(S('maps')) ?>" target="_blank" rel="noopener"><?= icon('map', 15) ?> Maps</a><?php endif; ?>
      </div>

      <?php if ($pengumuman): ?>
        <h3 class="section-title"><?= icon('bell', 18) ?> Pengumuman</h3>
        <?php foreach ($pengumuman as $p): ?>
          <article class="card news <?= !empty($p['penting']) ? 'is-important' : '' ?>">
            <div class="news-meta">
              <?php if (!empty($p['penting'])): ?><span class="badge"><?= icon('star', 12) ?> Penting</span><?php endif; ?>
              <small><?= e($p['tgl']) ?></small>
            </div>
            <h4><?= e($p['judul']) ?></h4>
            <p><?= nl2br(e($p['isi'])) ?></p>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="card me">
        <h3 class="card-title"><?= icon('user', 16) ?> Data kamu</h3>
        <dl>
          <dt>Nama</dt><dd><?= e($me['nama']) ?></dd>
          <dt><?= e(S('label_departemen')) ?></dt><dd><?= e($me['departemen']) ?></dd>
          <dt>No. HP</dt><dd><?= e($me['hp']) ?></dd>
          <?php foreach (K('fields') as $f): if (($me['extra'][$f['id']] ?? '') === '') continue; ?>
            <dt><?= e($f['label']) ?></dt><dd><?= e($me['extra'][$f['id']]) ?></dd>
          <?php endforeach; ?>
        </dl>
      </div>
    </section>

    <!-- JADWAL -->
    <section id="jadwal" class="tab">
      <h3 class="section-title"><?= icon('calendar', 18) ?> Itinerary perjalanan</h3>
      <?php foreach (K('jadwal') as $hari): ?>
        <div class="card day">
          <h4><?= e($hari['hari']) ?></h4>
          <ol class="timeline">
            <?php foreach (parse_jadwal($hari['items']) as [$jam, $kegiatan]): ?>
              <li><time><?= e($jam) ?></time><span><?= e($kegiatan) ?></span></li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endforeach; ?>
      <?php if (!K('jadwal')): ?><p class="empty">Jadwal belum tersedia.</p><?php endif; ?>
    </section>

    <!-- INFO -->
    <section id="info" class="tab">
      <h3 class="section-title"><?= icon('info', 18) ?> Info penting</h3>
      <?php foreach (K('info') as $i => $info): ?>
        <details class="card acc" <?= $i === 0 ? 'open' : '' ?>>
          <summary><span class="ic-box"><?= icon($info['ikon']) ?></span><?= e($info['judul']) ?><span class="chev"><?= icon('chevron', 18) ?></span></summary>
          <ul><?php foreach (lines($info['isi']) as $baris): ?><li><?= e($baris) ?></li><?php endforeach; ?></ul>
        </details>
      <?php endforeach; ?>
      <?php if (!K('info')): ?><p class="empty">Belum ada info.</p><?php endif; ?>
    </section>

    <!-- GALERI -->
    <?php if (isset($tabs['galeri'])): ?>
    <section id="galeri" class="tab">
      <h3 class="section-title"><?= icon('image', 18) ?> Galeri</h3>
      <div class="gallery">
        <?php foreach (K('galeri') as $g): ?>
          <figure>
            <button type="button" class="gallery-item" data-src="<?= e($g['gambar']) ?>" data-caption="<?= e($g['caption']) ?>">
              <img src="<?= e($g['gambar']) ?>" alt="<?= e($g['caption']) ?>" loading="lazy">
            </button>
            <?php if ($g['caption']): ?><figcaption><?= e($g['caption']) ?></figcaption><?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
      <dialog class="lightbox" id="lightbox">
        <button type="button" class="lightbox-close" aria-label="Tutup"><?= icon('x', 22) ?></button>
        <img src="" alt="">
        <p></p>
      </dialog>
    </section>
    <?php endif; ?>

    <!-- KONTAK -->
    <section id="kontak" class="tab">
      <h3 class="section-title"><?= icon('phone', 18) ?> Kontak panitia</h3>
      <?php foreach (K('kontak') as $k): ?>
        <div class="card contact">
          <span class="avatar"><?= e(mb_strtoupper(mb_substr($k['nama'], 0, 1))) ?></span>
          <div class="grow"><b><?= e($k['nama']) ?></b><small><?= e($k['peran']) ?></small><small><?= e($k['hp']) ?></small></div>
          <a class="icon-btn" href="tel:<?= e(normalize_hp($k['hp'])) ?>" aria-label="Telepon <?= e($k['nama']) ?>"><?= icon('phone', 18) ?></a>
          <a class="icon-btn wa" href="<?= e(wa_link($k['hp'])) ?>" target="_blank" rel="noopener" aria-label="WhatsApp <?= e($k['nama']) ?>"><?= icon('chat', 18) ?></a>
        </div>
      <?php endforeach; ?>
      <?php if (!K('kontak')): ?><p class="empty">Belum ada kontak.</p><?php endif; ?>
    </section>
  </main>

  <nav class="bottomnav" style="--n: <?= count($tabs) ?>">
    <?php foreach ($tabs as $id => [$label, $ic]): ?>
      <a href="#<?= $id ?>"><?= icon($ic, 22) ?><span><?= $label ?></span></a>
    <?php endforeach; ?>
  </nav>
</div>

<script>
// Tab berbasis hash (#jadwal, #info, ...)
function show(id) {
  if (!document.getElementById(id)) id = 'beranda';
  document.querySelectorAll('.tab').forEach(s => s.classList.toggle('active', s.id === id));
  document.querySelectorAll('.bottomnav a').forEach(a => a.classList.toggle('active', a.hash === '#' + id));
}
window.addEventListener('hashchange', () => { show(location.hash.slice(1)); window.scrollTo(0, 0); });
show(location.hash.slice(1));

// Hitung mundur
const cd = document.querySelector('.countdown');
if (cd) {
  const tick = () => {
    const s = Math.max(0, (cd.dataset.ts - Date.now()) / 1000);
    const v = { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60) };
    cd.querySelectorAll('[data-cd]').forEach(b => b.textContent = v[b.dataset.cd]);
  };
  tick(); setInterval(tick, 30000);
}

// Lightbox galeri
const lb = document.getElementById('lightbox');
if (lb) {
  document.querySelectorAll('.gallery-item').forEach(b => b.addEventListener('click', () => {
    lb.querySelector('img').src = b.dataset.src;
    lb.querySelector('p').textContent = b.dataset.caption;
    lb.showModal();
  }));
  lb.addEventListener('click', e => { if (e.target === lb || e.target.closest('.lightbox-close')) lb.close(); });
}
</script>
<?php page_foot();

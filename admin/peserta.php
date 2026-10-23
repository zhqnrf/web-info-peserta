<?php
defined('APP') or exit;

$list = load_peserta();
$fields = K('fields');
$depts = S('departemen');

function peserta_index($list, $id) {
    foreach ($list as $i => $p) if ($p['id'] === $id) return $i;
    return null;
}

// ---------- Aksi ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (string)($_POST['id'] ?? '');
    $idx = peserta_index($list, $id);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && $idx !== null) {
        $nama = $list[$idx]['nama'];
        array_splice($list, $idx, 1);
        save_peserta($list);
        flash("Peserta $nama dihapus.");
        redirect('admin.php?p=peserta');
    }

    if ($action === 'save') {
        $error = '';
        $hp = normalize_hp($_POST['hp'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        if (mb_strlen($nama) < 2) $error = 'Nama minimal 2 huruf.';
        elseif (!valid_hp($hp)) $error = 'Nomor HP tidak valid.';
        else foreach ($list as $i => $p) if ($p['hp'] === $hp && $i !== $idx) $error = "No. HP sudah dipakai oleh {$p['nama']}.";
        $extra = collect_extra($fields, $error, true); // admin boleh mengosongkan field wajib

        if ($error) {
            flash($error, 'err');
            redirect('admin.php?p=peserta&edit=' . ($idx !== null ? urlencode($id) : 'new'));
        }
        $p = $idx !== null ? $list[$idx] : ['id' => uid(), 'token' => bin2hex(random_bytes(16)), 'waktu' => date('Y-m-d H:i:s')];
        $p = array_merge($p, [
            'nama' => mb_substr($nama, 0, 100),
            'departemen' => trim($_POST['departemen'] ?? ''),
            'hp' => $hp,
            'kamar' => trim($_POST['kamar'] ?? ''),
            'bus' => trim($_POST['bus'] ?? ''),
            'catatan' => trim($_POST['catatan'] ?? ''),
            'extra' => $extra,
        ]);
        if ($idx !== null) $list[$idx] = $p; else $list[] = $p;
        save_peserta($list);
        flash('Data ' . $p['nama'] . ' tersimpan.');
        redirect('admin.php?p=peserta');
    }

    // Simpan penempatan cepat dari tabel
    if ($action === 'quick') {
        foreach ((array)($_POST['kamar'] ?? []) as $pid => $v) {
            $i = peserta_index($list, (string)$pid);
            if ($i !== null) { $list[$i]['kamar'] = trim($v); $list[$i]['bus'] = trim($_POST['bus'][$pid] ?? ''); }
        }
        save_peserta($list);
        flash('Penempatan ' . strtolower(S('label_kamar')) . ' & ' . strtolower(S('label_bus')) . ' tersimpan.');
        redirect($_SERVER['REQUEST_URI']);
    }
}

// ---------- Form tambah / edit ----------
if (isset($_GET['edit'])) {
    $idx = peserta_index($list, (string)$_GET['edit']);
    $p = $idx !== null ? $list[$idx] : ['id' => '', 'nama' => '', 'departemen' => '', 'hp' => '', 'kamar' => '', 'bus' => '', 'catatan' => '', 'extra' => []];
    $deptOptions = $depts;
    if ($p['departemen'] !== '' && !in_array($p['departemen'], $deptOptions, true)) $deptOptions[] = $p['departemen'];
    $GLOBALS['head_actions'] = '<a class="btn secondary" href="?p=peserta">' . icon('x', 18) . ' Batal</a>';
    ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e($p['id']) ?>">
      <div class="card">
        <h2><?= $idx !== null ? 'Edit peserta' : 'Tambah peserta' ?></h2>
        <p class="muted"><?= $idx !== null ? 'Terdaftar ' . e(date('d M Y, H:i', strtotime($p['waktu']))) : 'Peserta yang ditambahkan admin bisa langsung masuk dengan No. HP.' ?></p>
        <div class="grid-form">
          <label for="nama">Nama lengkap<input id="nama" name="nama" value="<?= e($p['nama']) ?>" required></label>
          <label for="hp">No. HP<input id="hp" type="tel" name="hp" value="<?= e($p['hp']) ?>" required></label>
          <label for="departemen"><?= e(S('label_departemen')) ?>
            <select id="departemen" name="departemen">
              <?php foreach ($deptOptions as $d): ?><option <?= $p['departemen'] === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
            </select></label>
          <label for="kamar"><?= e(S('label_kamar')) ?><input id="kamar" name="kamar" value="<?= e($p['kamar'] ?? '') ?>" placeholder="Contoh: 203"></label>
          <label for="bus"><?= e(S('label_bus')) ?><input id="bus" name="bus" value="<?= e($p['bus'] ?? '') ?>" placeholder="Contoh: 1"></label>
          <?php foreach ($fields as $f): ?><div><?= custom_field_input($f, $p['extra'][$f['id']] ?? '', true) ?></div><?php endforeach; ?>
          <label for="catatan" class="full">Catatan admin <span class="opt">(tidak terlihat peserta)</span><textarea id="catatan" name="catatan" rows="2"><?= e($p['catatan'] ?? '') ?></textarea></label>
        </div>
      </div>
      <div class="actions">
        <button class="btn" name="action" value="save"><?= icon('check', 18) ?> Simpan</button>
        <?php if ($idx !== null): ?>
          <button class="btn danger" name="action" value="delete" formnovalidate data-confirm="Hapus <?= e($p['nama']) ?>?"><?= icon('trash', 18) ?> Hapus peserta</button>
        <?php endif; ?>
      </div>
    </form>
    <?php
    return;
}

// ---------- Daftar ----------
$q = trim($_GET['q'] ?? '');
$d = $_GET['d'] ?? '';
$tampil = array_filter($list, function ($p) use ($q, $d) {
    if ($d !== '' && $p['departemen'] !== $d) return false;
    if ($q === '') return true;
    return stripos($p['nama'] . ' ' . $p['hp'] . ' ' . ($p['kamar'] ?? '') . ' ' . ($p['bus'] ?? ''), $q) !== false;
});
$tampil = array_reverse($tampil, true);

$GLOBALS['head_actions'] = '<div class="actions"><a class="btn secondary" href="?p=peserta&export=1">' . icon('download', 18) . ' Export Excel</a>'
    . '<a class="btn" href="?p=peserta&edit=new">' . icon('plus', 18) . ' Tambah peserta</a></div>';
?>
<div class="card">
  <form class="toolbar" method="get">
    <input type="hidden" name="p" value="peserta">
    <div class="search"><?= icon('search', 18) ?><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama, No. HP, kamar…" aria-label="Cari"></div>
    <select name="d" onchange="this.form.submit()" aria-label="Filter <?= e(S('label_departemen')) ?>">
      <option value="">Semua <?= e(strtolower(S('label_departemen'))) ?></option>
      <?php foreach ($depts as $x): ?><option <?= $d === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?>
    </select>
  </form>

  <p class="muted" style="margin-bottom:10px"><?= count($tampil) ?> dari <?= count($list) ?> peserta. Isi kolom <?= e(strtolower(S('label_kamar'))) ?> & <?= e(strtolower(S('label_bus'))) ?> langsung di tabel lalu klik Simpan penempatan.</p>

  <?php if ($tampil): ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Nama</th><th class="hide-sm"><?= e(S('label_departemen')) ?></th><th><?= e(S('label_kamar')) ?></th><th><?= e(S('label_bus')) ?></th><th class="hide-sm">Daftar</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tampil as $p): ?>
          <tr>
            <td><b><?= e($p['nama']) ?></b><small><?= e($p['hp']) ?><span class="show-sm"> · <?= e($p['departemen']) ?></span></small></td>
            <td class="hide-sm"><?= e($p['departemen']) ?></td>
            <td><input name="kamar[<?= e($p['id']) ?>]" value="<?= e($p['kamar'] ?? '') ?>" style="margin:0;width:80px;padding:8px 10px;font-size:14px" aria-label="<?= e(S('label_kamar')) ?> <?= e($p['nama']) ?>"></td>
            <td><input name="bus[<?= e($p['id']) ?>]" value="<?= e($p['bus'] ?? '') ?>" style="margin:0;width:64px;padding:8px 10px;font-size:14px" aria-label="<?= e(S('label_bus')) ?> <?= e($p['nama']) ?>"></td>
            <td class="hide-sm"><small><?= e(date('d M, H:i', strtotime($p['waktu']))) ?></small></td>
            <td class="t-actions"><a class="btn small secondary" href="?p=peserta&edit=<?= urlencode($p['id']) ?>"><?= icon('edit', 15) ?> <span class="hide-sm">Detail</span></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="actions" style="margin-top:14px"><button class="btn" name="action" value="quick"><?= icon('check', 18) ?> Simpan penempatan</button></div>
  </form>
  <?php else: ?>
    <p class="empty">Tidak ada peserta yang cocok.</p>
  <?php endif; ?>
</div>

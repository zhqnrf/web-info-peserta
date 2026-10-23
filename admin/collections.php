<?php
// Editor generik untuk konten berbentuk daftar (pengumuman, jadwal, info, kontak, galeri, field form).
// Tambah kolom baru cukup dengan menambah definisi field di sini.
defined('APP') or exit;

const FIELD_TYPES = [
    'text' => 'Teks singkat', 'textarea' => 'Teks panjang', 'select' => 'Pilihan (dropdown)',
    'number' => 'Angka', 'date' => 'Tanggal',
];

function collections() {
    return [
        'pengumuman' => ['label' => 'Pengumuman', 'icon' => 'bell', 'title' => 'judul', 'fields' => [
            ['name' => 'judul', 'label' => 'Judul', 'type' => 'text', 'required' => true],
            ['name' => 'tgl', 'label' => 'Tanggal tampil', 'type' => 'text', 'help' => 'Contoh: 23 Okt 2026'],
            ['name' => 'isi', 'label' => 'Isi pengumuman', 'type' => 'textarea', 'required' => true, 'full' => true],
            ['name' => 'penting', 'label' => 'Tandai penting (tampil paling atas dengan label "Penting")', 'type' => 'checkbox', 'full' => true],
        ]],
        'jadwal' => ['label' => 'Jadwal', 'icon' => 'calendar', 'title' => 'hari', 'fields' => [
            ['name' => 'hari', 'label' => 'Judul hari', 'type' => 'text', 'required' => true, 'full' => true, 'help' => 'Contoh: Hari 1 · Sabtu, 14 Nov'],
            ['name' => 'items', 'label' => 'Daftar kegiatan', 'type' => 'textarea', 'rows' => 8, 'required' => true, 'full' => true,
             'help' => 'Satu kegiatan per baris dengan format: jam | kegiatan. Contoh: 08.00 | Sarapan di hotel'],
        ]],
        'info' => ['label' => 'Info', 'icon' => 'info', 'title' => 'judul', 'fields' => [
            ['name' => 'judul', 'label' => 'Judul', 'type' => 'text', 'required' => true, 'full' => true],
            ['name' => 'ikon', 'label' => 'Ikon', 'type' => 'icon', 'full' => true],
            ['name' => 'isi', 'label' => 'Isi', 'type' => 'textarea', 'rows' => 6, 'full' => true, 'help' => 'Satu poin per baris.'],
        ]],
        'kontak' => ['label' => 'Kontak', 'icon' => 'phone', 'title' => 'nama', 'fields' => [
            ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'required' => true],
            ['name' => 'peran', 'label' => 'Peran / jabatan', 'type' => 'text'],
            ['name' => 'hp', 'label' => 'No. HP / WhatsApp', 'type' => 'tel', 'required' => true],
        ]],
        'galeri' => ['label' => 'Galeri', 'icon' => 'image', 'title' => 'caption', 'fields' => [
            ['name' => 'gambar', 'label' => 'Foto', 'type' => 'image', 'required' => true],
            ['name' => 'caption', 'label' => 'Keterangan', 'type' => 'text'],
        ]],
        'fields' => ['label' => 'Field tambahan', 'icon' => 'form', 'title' => 'label', 'fields' => [
            ['name' => 'label', 'label' => 'Pertanyaan / label', 'type' => 'text', 'required' => true, 'help' => 'Contoh: Ukuran kaos, NIK, Nama pendamping'],
            ['name' => 'tipe', 'label' => 'Jenis isian', 'type' => 'select', 'options' => FIELD_TYPES],
            ['name' => 'opsi', 'label' => 'Pilihan jawaban', 'type' => 'textarea', 'help' => 'Hanya untuk jenis "Pilihan". Satu pilihan per baris.'],
            ['name' => 'placeholder', 'label' => 'Teks contoh di kotak isian', 'type' => 'text'],
            ['name' => 'wajib', 'label' => 'Wajib diisi', 'type' => 'checkbox', 'full' => true],
        ]],
    ];
}

// Proses simpan / hapus / urutkan. Dipanggil saat POST dengan col = nama koleksi.
function collection_handle($col) {
    $def = collections()[$col];
    $items = K($col);
    $action = $_POST['action'] ?? '';
    $id = (string)($_POST['id'] ?? '');
    $idx = null;
    foreach ($items as $i => $it) if ($it['id'] === $id) $idx = $i;
    $anchor = '';

    try {
        if ($action === 'save') {
            $item = $idx !== null ? $items[$idx] : ['id' => uid()];
            foreach ($def['fields'] as $f) {
                $n = $f['name'];
                if ($f['type'] === 'checkbox') { $item[$n] = !empty($_POST[$n]); continue; }
                if ($f['type'] === 'image') {
                    $new = upload_image($n);
                    if ($new) { delete_upload($item[$n] ?? ''); $item[$n] = $new; }
                    if (!empty($f['required']) && empty($item[$n])) throw new RuntimeException($f['label'] . ' wajib diisi.');
                    continue;
                }
                $v = trim(str_replace("\r", '', (string)($_POST[$n] ?? '')));
                if (!empty($f['required']) && $v === '') throw new RuntimeException($f['label'] . ' wajib diisi.');
                if ($f['type'] === 'select' && !isset($f['options'][$v])) $v = array_key_first($f['options']);
                if ($f['type'] === 'icon' && !isset(ICON_CHOICES[$v])) $v = 'info';
                $item[$n] = $v;
            }
            if ($idx !== null) $items[$idx] = $item; else $items[] = $item;
            $anchor = '#i-' . $item['id'];
            flash($def['label'] . ' tersimpan.');
        } elseif ($action === 'delete' && $idx !== null) {
            foreach ($def['fields'] as $f) if ($f['type'] === 'image') delete_upload($items[$idx][$f['name']] ?? '');
            array_splice($items, $idx, 1);
            flash($def['label'] . ' dihapus.');
        } elseif (($action === 'up' || $action === 'down') && $idx !== null) {
            $j = $action === 'up' ? $idx - 1 : $idx + 1;
            if (isset($items[$j])) [$items[$idx], $items[$j]] = [$items[$j], $items[$idx]];
        }
        save_konten($col, $items);
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'err');
    }
    redirect($_SERVER['REQUEST_URI'] . $anchor);
}

function collection_input($f, $item) {
    $n = $f['name'];
    $v = $item[$n] ?? '';
    $id = 'f-' . e($item['id'] ?? 'new') . '-' . $n;
    $req = !empty($f['required']) ? ' required' : '';
    $help = !empty($f['help']) ? '<span class="help">' . e($f['help']) . '</span>' : '';
    $cls = !empty($f['full']) ? ' class="full"' : '';

    switch ($f['type']) {
        case 'checkbox':
            return '<label' . $cls . '><span class="check"><input type="checkbox" name="' . $n . '" value="1"' . ($v ? ' checked' : '') . '> ' . e($f['label']) . '</span></label>';
        case 'textarea':
            return '<label for="' . $id . '"' . $cls . '>' . e($f['label']) . '<textarea id="' . $id . '" name="' . $n . '" rows="' . ($f['rows'] ?? 3) . '"' . $req . '>' . e($v) . '</textarea>' . $help . '</label>';
        case 'select':
            $h = '<label for="' . $id . '"' . $cls . '>' . e($f['label']) . '<select id="' . $id . '" name="' . $n . '">';
            foreach ($f['options'] as $k => $lbl) $h .= '<option value="' . e($k) . '"' . ($v === $k ? ' selected' : '') . '>' . e($lbl) . '</option>';
            return $h . '</select>' . $help . '</label>';
        case 'icon':
            $v = $v ?: 'info';
            $h = '<div' . $cls . '><label>' . e($f['label']) . '</label><div class="icon-pick">';
            foreach (ICON_CHOICES as $k => $lbl) {
                $h .= '<label title="' . e($lbl) . '"><input type="radio" name="' . $n . '" value="' . $k . '"' . ($v === $k ? ' checked' : '') . ' aria-label="' . e($lbl) . '"><span>' . icon($k) . '</span></label>';
            }
            return $h . '</div></div>';
        case 'image':
            $prev = $v ? '<img class="img-preview" src="' . e($v) . '" alt="">' : '';
            return '<label for="' . $id . '" class="full">' . e($f['label']) . $prev . '<input id="' . $id . '" type="file" name="' . $n . '" accept="image/jpeg,image/png,image/webp,image/gif"' . ($v ? '' : $req) . '><span class="help">JPG, PNG, WEBP atau GIF, maksimal ' . MAX_UPLOAD_MB . ' MB.' . ($v ? ' Kosongkan jika tidak ingin mengganti.' : '') . '</span></label>';
        default:
            $type = $f['type'] === 'tel' ? 'tel' : 'text';
            return '<label for="' . $id . '"' . $cls . '>' . e($f['label']) . '<input id="' . $id . '" type="' . $type . '" name="' . $n . '" value="' . e($v) . '"' . $req . '>' . $help . '</label>';
    }
}

function collection_form($col, $item, $isNew) {
    $def = collections()[$col];
    $h = '<form method="post" enctype="multipart/form-data" class="form">' . csrf_field()
       . '<input type="hidden" name="col" value="' . $col . '"><input type="hidden" name="id" value="' . e($item['id'] ?? '') . '">'
       . '<div class="grid-form">';
    foreach ($def['fields'] as $f) $h .= collection_input($f, $item);
    $h .= '</div><div class="ed-actions">'
        . '<button class="btn small" name="action" value="save">' . icon('check', 16) . ($isNew ? ' Tambahkan' : ' Simpan') . '</button>';
    if (!$isNew) {
        $h .= '<button class="btn small secondary" name="action" value="up" formnovalidate title="Naikkan urutan">' . icon('up', 16) . '</button>'
            . '<button class="btn small secondary" name="action" value="down" formnovalidate title="Turunkan urutan">' . icon('down', 16) . '</button>'
            . '<span class="spacer"></span>'
            . '<button class="btn small danger" name="action" value="delete" formnovalidate data-confirm="Hapus item ini?">' . icon('trash', 16) . ' Hapus</button>';
    }
    return $h . '</div></form>';
}

function collection_editor($col) {
    $def = collections()[$col];
    $items = K($col);
    $h = '<div class="ed-list">';
    foreach ($items as $i => $it) {
        $title = trim((string)($it[$def['title']] ?? '')) ?: '(tanpa judul)';
        $lead = isset($it['gambar']) ? '<img class="ed-thumb" src="' . e($it['gambar']) . '" alt="">' : '<span class="ed-num">' . ($i + 1) . '</span>';
        $badge = !empty($it['penting']) ? ' <span class="badge">Penting</span>' : '';
        if ($col === 'fields') $badge = ' <span class="badge muted">' . e(FIELD_TYPES[$it['tipe']] ?? '') . '</span>' . (!empty($it['wajib']) ? ' <span class="badge ok">Wajib</span>' : '');
        $h .= '<details class="card ed-item" id="i-' . e($it['id']) . '"><summary>' . $lead . '<span class="ed-title">' . e($title) . '</span>' . $badge
            . '<span class="chev">' . icon('chevron', 18) . '</span></summary><div class="ed-body">' . collection_form($col, $it, false) . '</div></details>';
    }
    if (!$items) $h .= '<p class="empty">Belum ada ' . strtolower($def['label']) . '.</p>';
    $h .= '<details class="card ed-item ed-new"' . (!$items ? ' open' : '') . '><summary><span class="ic-box">' . icon('plus', 18) . '</span>Tambah ' . strtolower($def['label'])
        . '<span class="chev">' . icon('chevron', 18) . '</span></summary><div class="ed-body">' . collection_form($col, ['id' => ''], true) . '</div></details>';
    return $h . '</div>';
}

// Buka item yang baru disimpan (dari #i-xxx di URL)
function collection_script() {
    return '<script>const t = location.hash && document.querySelector(location.hash); if (t && t.tagName === "DETAILS") { t.open = true; t.scrollIntoView({block: "center"}); }</script>';
}

<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/defaults.php';

// ---------- Umum ----------
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function uid() { return bin2hex(random_bytes(5)); }
function redirect($url) { header('Location: ' . $url); exit; }

// Pecah teks per baris, buang baris kosong
function lines($text) {
    return array_values(array_filter(array_map('trim', explode("\n", (string)$text)), 'strlen'));
}

// "08.00 | Sarapan" -> ['08.00', 'Sarapan']
function parse_jadwal($text) {
    return array_map(function ($l) {
        $parts = array_map('trim', explode('|', $l, 2));
        return count($parts) === 2 ? $parts : ['', $parts[0]];
    }, lines($text));
}

// ---------- Penyimpanan JSON (tanpa database) ----------
// Disimpan sebagai .php yang diawali baris "exit", jadi kalau dibuka lewat browser
// isinya tidak pernah tampil, walau server tidak membaca .htaccess (mis. Nginx).
const DATA_GUARD = "<?php http_response_code(404); exit; ?>\n";

function data_get($name, $default = []) {
    $f = DATA_DIR . "/$name.php";
    if (!is_file($f)) return $default;
    $raw = file_get_contents($f);
    if (strpos($raw, DATA_GUARD) === 0) $raw = substr($raw, strlen(DATA_GUARD));
    $d = json_decode($raw, true);
    return is_array($d) ? $d : $default;
}

function data_put($name, $value) {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (file_put_contents(DATA_DIR . "/$name.php", DATA_GUARD . $json, LOCK_EX) === false) {
        http_response_code(500);
        exit('Folder data/ tidak bisa ditulis. Ubah pemilik/permission folder data/ dan uploads/ agar bisa ditulis PHP.');
    }
}

function seed_data($force = false) {
    $marker = DATA_DIR . '/seeded_at.txt';
    // Server demo publik: kembalikan data contoh otomatis setiap N jam
    if (DEMO_MODE && DEMO_AUTO_RESET_HOURS > 0 && is_file($marker) && time() - filemtime($marker) > DEMO_AUTO_RESET_HOURS * 3600) $force = true;
    if (!$force && is_file(DATA_DIR . '/settings.php')) return;

    data_put('settings', default_settings());
    data_put('konten', default_konten());
    data_put('peserta', DEMO_MODE ? demo_peserta() : []);
    if (DEMO_MODE) {
        // buang foto yang diupload pengunjung demo
        foreach (glob(UPLOAD_DIR . '/*') ?: [] as $f) {
            if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $f)) unlink($f);
        }
        @unlink(DATA_DIR . '/auth.php');
    }
    file_put_contents($marker, date('c'));
    unset($GLOBALS['_S'], $GLOBALS['_K']);
}
seed_data();

// Pengaturan: S() = semua, S('key') = satu nilai
function S($key = null) {
    if (!isset($GLOBALS['_S'])) $GLOBALS['_S'] = array_merge(default_settings(), data_get('settings'));
    return $key === null ? $GLOBALS['_S'] : ($GLOBALS['_S'][$key] ?? null);
}
function save_settings($new) {
    $all = array_merge(S(), $new);
    data_put('settings', $all);
    $GLOBALS['_S'] = $all;
}

// Konten: K('jadwal'), K('info'), dst.
function K($col) {
    if (!isset($GLOBALS['_K'])) $GLOBALS['_K'] = data_get('konten');
    return $GLOBALS['_K'][$col] ?? [];
}
function save_konten($col, $items) {
    $all = data_get('konten');
    $all[$col] = array_values($items);
    data_put('konten', $all);
    $GLOBALS['_K'] = $all;
}

// ---------- Peserta ----------
function load_peserta() { return data_get('peserta'); }
function save_peserta($list) { data_put('peserta', array_values($list)); }

// 08xx / +628xx / 628xx -> 08xx
function normalize_hp($hp) {
    $hp = preg_replace('/\D/', '', (string)$hp);
    if (strpos($hp, '62') === 0) $hp = '0' . substr($hp, 2);
    return $hp;
}
function valid_hp($hp) { return (bool)preg_match('/^08\d{8,11}$/', $hp); }

function find_by($key, $val) {
    foreach (load_peserta() as $p) if (($p[$key] ?? null) === $val) return $p;
    return null;
}

function login_peserta($p) {
    session_regenerate_id(true);
    $_SESSION['pid'] = $p['token'];
    setcookie('pid', $p['token'], ['expires' => time() + COOKIE_DAYS * 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}

function current_peserta() {
    $token = $_SESSION['pid'] ?? ($_COOKIE['pid'] ?? null);
    if (!$token || !is_string($token)) return null;
    $p = find_by('token', $token);
    if ($p) $_SESSION['pid'] = $token;
    return $p;
}

function require_peserta() {
    $p = current_peserta();
    if (!$p) redirect('index.php');
    return array_merge(['kamar' => '', 'bus' => '', 'catatan' => '', 'extra' => []], $p);
}

function wa_link($hp) { return 'https://wa.me/62' . substr(normalize_hp($hp), 1); }

// ---------- Admin ----------
function is_admin() { return !empty($_SESSION['admin']); }

function admin_verify($pw) {
    $auth = data_get('auth');
    return !empty($auth['hash']) ? password_verify($pw, $auth['hash']) : hash_equals(DEFAULT_ADMIN_PASSWORD, $pw);
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_ok() { return hash_equals(csrf_token(), (string)($_POST['csrf'] ?? '')); }

function flash($msg, $type = 'ok') { $_SESSION['flash'][] = [$type, $msg]; }
function flash_html() {
    $h = '';
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) {
        $h .= '<div class="alert alert-' . $t . '" role="status">' . icon($t === 'ok' ? 'check' : 'alert', 18) . '<span>' . e($m) . '</span></div>';
    }
    unset($_SESSION['flash']);
    return $h;
}

// ---------- Upload gambar ----------
function upload_image($field) {
    $f = $_FILES[$field] ?? null;
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload gagal. Ukuran file mungkin terlalu besar.');
    if ($f['size'] > MAX_UPLOAD_MB * 1024 * 1024) throw new RuntimeException('Ukuran gambar maksimal ' . MAX_UPLOAD_MB . ' MB.');
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$info['mime'] ?? ''] ?? null;
    if (!$ext) throw new RuntimeException('File harus berupa gambar JPG, PNG, WEBP, atau GIF.');
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = date('Ymd') . '-' . uid() . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) throw new RuntimeException('Gagal menyimpan gambar.');
    return 'uploads/' . $name;
}

// Hanya menghapus file hasil upload (bukan gambar bawaan di assets/)
function delete_upload($path) {
    if (preg_match('#^uploads/[\w.-]+$#', (string)$path) && is_file(__DIR__ . '/' . $path)) unlink(__DIR__ . '/' . $path);
}

// ---------- Field tambahan registrasi ----------
// Input untuk satu field tambahan (dipakai di form registrasi & form edit peserta admin)
function custom_field_input($f, $value, $forceOptional = false) {
    $name = 'extra[' . e($f['id']) . ']';
    $id = 'x-' . e($f['id']);
    $req = !empty($f['wajib']) && !$forceOptional ? ' required' : '';
    $ph = ($f['placeholder'] ?? '') !== '' ? ' placeholder="' . e($f['placeholder']) . '"' : '';
    $label = '<label for="' . $id . '">' . e($f['label']) . (!empty($f['wajib']) ? '' : ' <span class="opt">(opsional)</span>');
    switch ($f['tipe']) {
        case 'textarea':
            return $label . '<textarea id="' . $id . '" name="' . $name . '" rows="3"' . $req . $ph . '>' . e($value) . '</textarea></label>';
        case 'select':
            $h = $label . '<select id="' . $id . '" name="' . $name . '"' . $req . '><option value="">Pilih</option>';
            foreach (lines($f['opsi']) as $o) $h .= '<option' . ($value === $o ? ' selected' : '') . '>' . e($o) . '</option>';
            return $h . '</select></label>';
        default:
            $type = in_array($f['tipe'], ['number', 'date'], true) ? $f['tipe'] : 'text';
            return $label . '<input id="' . $id . '" type="' . $type . '" name="' . $name . '" value="' . e($value) . '"' . $req . $ph . '></label>';
    }
}

// Validasi & ambil jawaban field tambahan dari $_POST['extra']
function collect_extra($fields, &$error, $forceOptional = false) {
    $in = $_POST['extra'] ?? [];
    $out = [];
    foreach ($fields as $f) {
        $v = trim((string)($in[$f['id']] ?? ''));
        if ($v === '' && !empty($f['wajib']) && !$forceOptional && !$error) $error = $f['label'] . ' wajib diisi.';
        if ($v !== '' && $f['tipe'] === 'select' && !in_array($v, lines($f['opsi']), true) && !$error) $error = 'Pilihan ' . $f['label'] . ' tidak valid.';
        $out[$f['id']] = mb_substr($v, 0, 500);
    }
    return $out;
}

// ---------- Tampilan ----------
function brand_mark($size = 26) {
    $logo = S('logo');
    return $logo ? '<img src="' . e($logo) . '" alt="">' : icon('plane', $size);
}

function page_head($title, $class = 'peserta') {
    $warna = preg_match('/^#[0-9a-fA-F]{6}$/', (string)S('warna')) ? S('warna') : '#0f766e';
    ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="<?= $warna ?>">
<title><?= e($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="assets/style.css?v=3">
<style>:root { --primary: <?= $warna ?>; }</style>
</head>
<body class="<?= e($class) ?>">
<?php if (DEMO_MODE && $class === 'peserta'): ?>
<div class="demo-bar"><span><?= icon('eye', 14) ?> Mode demo</span><a href="admin.php">Coba panel admin <?= icon('chevron', 14) ?></a></div>
<?php endif;
}

function page_foot() { ?>
</body>
</html>
<?php }

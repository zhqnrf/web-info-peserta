<?php
// ================== PENGATURAN SERVER ==================
// Semua isi website (nama acara, jadwal, warna, dll.) diatur dari panel admin: /admin.php
// File ini hanya untuk pengaturan teknis.

define('APP', true);

// true  = mode demo: tampil banner demo, petunjuk password admin, tombol reset data,
//         dan ganti password dinonaktifkan. Pakai untuk server demo.
// false = mode produksi untuk acara sungguhan.
define('DEMO_MODE', true);

// Khusus mode demo: kembalikan data contoh otomatis setiap N jam (0 = tidak pernah)
define('DEMO_AUTO_RESET_HOURS', 24);

// Password admin awal (dipakai sampai diganti dari menu Akun di panel admin)
define('DEFAULT_ADMIN_PASSWORD', 'admin123');

define('DATA_DIR', __DIR__ . '/data');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_MB', 3);
define('COOKIE_DAYS', 60); // lama peserta tetap login di HP-nya

date_default_timezone_set('Asia/Jakarta');
session_start();

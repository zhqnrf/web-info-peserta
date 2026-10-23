<?php
// Isi awal website. Dipakai saat pertama kali dijalankan dan saat "Reset data demo".
defined('APP') or exit;

function default_settings() {
    return [
        // Identitas
        'site_name' => 'Family Gathering 2026',
        'tagline'   => 'Info resmi untuk seluruh peserta',
        'warna'     => '#0f766e',
        'logo'      => '',
        // Acara
        'tujuan'    => 'Bali',
        'tanggal'   => '14 – 16 November 2026',
        'mulai'     => '2026-11-14T05:00',
        'kumpul'    => 'Terminal 3 Bandara Soekarno-Hatta, pukul 04.30 WIB',
        'maps'      => 'https://www.google.com/maps/search/?api=1&query=Terminal+3+Bandara+Soekarno-Hatta',
        'sambutan'  => 'Selamat datang! Semua info perjalanan ada di sini. Cek halaman ini secara berkala untuk pengumuman terbaru dari panitia.',
        // Registrasi
        'registrasi_buka'  => true,
        'pesan_tutup'      => 'Registrasi sudah ditutup. Peserta yang sudah terdaftar tetap bisa masuk dengan No. HP.',
        'label_departemen' => 'Departemen',
        'departemen'       => ['Finance', 'HRD', 'IT', 'Marketing', 'Operasional', 'Produksi', 'Sales', 'Lainnya'],
        // Tampilan halaman peserta
        'tampil_countdown'  => true,
        'tampil_penempatan' => true,
        'label_kamar'       => 'Kamar',
        'label_bus'         => 'Bus',
    ];
}

function default_konten() {
    return [
        'pengumuman' => [
            ['id' => 'p1', 'judul' => 'Pembagian kamar & bus sudah keluar', 'tgl' => '23 Okt 2026', 'isi' => 'Cek kartu "Penempatan kamu" di halaman Beranda. Hubungi panitia jika ada perubahan.', 'penting' => true],
            ['id' => 'p2', 'judul' => 'Batas registrasi 7 November', 'tgl' => '20 Okt 2026', 'isi' => 'Peserta yang belum registrasi sampai tanggal tersebut dianggap tidak ikut.', 'penting' => false],
        ],
        'jadwal' => [
            ['id' => 'j1', 'hari' => 'Hari 1 · Sabtu, 14 Nov', 'items' => "04.30 | Kumpul di Terminal 3 Soekarno-Hatta\n06.10 | Penerbangan ke Denpasar\n09.00 | Tiba di Bali, naik bus sesuai pembagian\n12.00 | Makan siang & check-in hotel\n16.00 | Sunset di Pantai Kuta\n19.00 | Makan malam bersama"],
            ['id' => 'j2', 'hari' => 'Hari 2 · Minggu, 15 Nov', 'items' => "07.00 | Sarapan di hotel\n08.30 | Tour Ubud & Tegallalang\n13.00 | Makan siang\n15.00 | Outbound & games antar departemen\n19.00 | Gala dinner & doorprize"],
            ['id' => 'j3', 'hari' => 'Hari 3 · Senin, 16 Nov', 'items' => "07.00 | Sarapan & check-out\n09.00 | Belanja oleh-oleh\n13.30 | Penerbangan pulang"],
        ],
        'info' => [
            ['id' => 'i1', 'ikon' => 'bag',     'judul' => 'Barang yang wajib dibawa', 'isi' => "KTP asli\nPakaian 3 hari + baju renang\nObat pribadi\nPower bank & charger\nKaos seragam (dibagikan saat kumpul)"],
            ['id' => 'i2', 'ikon' => 'hotel',   'judul' => 'Hotel', 'isi' => "Hotel tepi pantai di Kuta, Bali\nCheck-in 14.00 · Check-out 12.00\nNomor kamar ada di halaman Beranda"],
            ['id' => 'i3', 'ikon' => 'shirt',   'judul' => 'Dress code', 'isi' => "Hari 1: Kaos seragam\nHari 2: Bebas rapi, sepatu olahraga\nGala dinner: Nuansa putih"],
            ['id' => 'i4', 'ikon' => 'heart',   'judul' => 'Kesehatan', 'isi' => "Tim medis ikut di setiap bus\nInfokan alergi/penyakit saat registrasi\nBawa obat pribadi secukupnya"],
            ['id' => 'i5', 'ikon' => 'alert',   'judul' => 'Ketentuan', 'isi' => "Tepat waktu sesuai jadwal\nTidak membawa keluarga tanpa konfirmasi panitia\nIkuti arahan panitia dan tour guide"],
        ],
        'kontak' => [
            ['id' => 'k1', 'nama' => 'Budi Hartono', 'peran' => 'Ketua Panitia', 'hp' => '081200000001'],
            ['id' => 'k2', 'nama' => 'Sari Wulandari', 'peran' => 'Konsumsi & Kamar', 'hp' => '081200000002'],
            ['id' => 'k3', 'nama' => 'Made Arya', 'peran' => 'Tour Guide', 'hp' => '081200000003'],
        ],
        'galeri' => [
            ['id' => 'g1', 'gambar' => 'assets/demo/pantai.svg', 'caption' => 'Sunset di Pantai Kuta'],
            ['id' => 'g2', 'gambar' => 'assets/demo/sawah.svg',  'caption' => 'Terasering Tegallalang'],
            ['id' => 'g3', 'gambar' => 'assets/demo/outbound.svg', 'caption' => 'Outbound antar departemen'],
            ['id' => 'g4', 'gambar' => 'assets/demo/gala.svg',   'caption' => 'Gala dinner'],
        ],
        // Field tambahan di form registrasi
        'fields' => [
            ['id' => 'f1', 'label' => 'Ukuran kaos', 'tipe' => 'select', 'opsi' => "S\nM\nL\nXL\nXXL", 'placeholder' => '', 'wajib' => true],
            ['id' => 'f2', 'label' => 'Alergi / riwayat penyakit', 'tipe' => 'textarea', 'opsi' => '', 'placeholder' => 'Kosongkan jika tidak ada', 'wajib' => false],
        ],
    ];
}

function demo_peserta() {
    $rows = [
        ['Rina Wulandari', 'Finance', '081311110001', '203', '1', 'M'],
        ['Dimas Saputra', 'IT', '081311110002', '205', '1', 'L'],
        ['Ayu Lestari', 'Marketing', '081311110003', '203', '1', 'S'],
        ['Fajar Nugroho', 'Produksi', '081311110004', '207', '2', 'XL'],
        ['Putri Maharani', 'HRD', '081311110005', '204', '1', 'M'],
        ['Yoga Pratama', 'Sales', '081311110006', '207', '2', 'L'],
        ['Nadia Kusuma', 'Operasional', '081311110007', '204', '2', 'M'],
        ['Agus Setiawan', 'Produksi', '081311110008', '209', '2', 'XXL'],
        ['Citra Anggraini', 'Finance', '081311110009', '', '', 'S'],
    ];
    $list = [];
    foreach ($rows as $i => [$nama, $dept, $hp, $kamar, $bus, $kaos]) {
        $list[] = [
            'id' => 'd' . ($i + 1),
            'token' => bin2hex(random_bytes(16)),
            'nama' => $nama, 'departemen' => $dept, 'hp' => $hp,
            'kamar' => $kamar, 'bus' => $bus, 'catatan' => '',
            'extra' => ['f1' => $kaos, 'f2' => ''],
            'waktu' => date('Y-m-d H:i:s', strtotime('-' . (9 - $i) * 7 . ' hours')),
        ];
    }
    return $list;
}

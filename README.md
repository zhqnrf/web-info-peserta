# Web Info Peserta

Website informasi untuk peserta acara/perjalanan (family gathering, outing, study tour, dll.).
Tampilan khusus HP dan ringan. Dibuat dengan PHP murni, **tanpa database dan tanpa framework**.

Alur peserta: buka link → registrasi (nama, departemen, No. HP + field tambahan) → semua info acara langsung tampil.

## Fitur

**Halaman peserta (mobile)**
- Registrasi dengan validasi, lalu login tersimpan di HP (60 hari). Kalau ganti HP, cukup masuk lagi dengan No. HP.
- Beranda: tujuan, tanggal, hitung mundur keberangkatan, kata sambutan, titik kumpul + tombol Google Maps, pengumuman (bisa ditandai *Penting*).
- Kartu **penempatan pribadi** untuk tiap peserta (misalnya Kamar & Bus, labelnya bisa diganti).
- Jadwal / itinerary per hari dalam bentuk timeline.
- Info penting yang bisa dibuka-tutup, dengan ikon yang bisa dipilih.
- Galeri foto dengan tampilan layar penuh.
- Kontak panitia dengan tombol telepon & WhatsApp.

**Panel admin** (`/admin.php`, bisa dipakai di HP maupun laptop)
- Dashboard: total peserta, pendaftar hari ini, rekap per departemen, registrasi terbaru, link untuk dibagikan.
- Peserta: cari & filter, tambah/edit/hapus, isi kamar & bus langsung di tabel, export ke Excel (CSV).
- Konten: kelola pengumuman, jadwal, info, kontak, dan galeri (tambah, edit, hapus, atur urutan, upload foto).
- Form registrasi: buka/tutup pendaftaran, ganti label & pilihan departemen, tambah **field kustom** (teks, pilihan, angka, tanggal, wajib/opsional).
- Pengaturan: nama acara, tagline, logo, **warna tema**, detail acara, tampilkan/sembunyikan bagian halaman.
- Akun & data: ganti password, backup semua data (JSON), kosongkan data peserta.

## Instalasi

Syarat: PHP 7.4 atau lebih baru (shared hosting / cPanel biasa sudah cukup).

1. Upload semua file ke folder website (misalnya `public_html/` atau subfolder).
2. Pastikan folder `data/` dan `uploads/` bisa ditulis oleh PHP (permission 755 atau 775).
3. Buka `config.php` dan atur:
   - `DEMO_MODE` → `true` untuk server demo, `false` untuk acara sungguhan.
   - `DEFAULT_ADMIN_PASSWORD` → password admin awal.
4. Buka website. Data awal dibuat otomatis saat pertama kali dibuka.
5. Masuk ke `/admin.php`, lalu ganti isi, warna, dan password sesuai kebutuhan.

Coba di komputer sendiri:

```bash
php -S localhost:8000
```

## Mode demo

Dengan `DEMO_MODE = true`:
- Ada banner "Mode demo" dan petunjuk password di halaman login admin.
- Tersedia tombol **Reset demo** di menu Akun & Data.
- Data kembali ke contoh awal otomatis setiap `DEMO_AUTO_RESET_HOURS` jam (default 24), dan foto yang diupload pengunjung ikut dihapus.
- Ganti password admin dinonaktifkan agar pengunjung demo tidak mengunci admin.

Untuk acara sungguhan, ubah ke `false` lalu ganti password dari panel admin.

## Struktur

```
index.php        Registrasi & masuk peserta
home.php         Halaman info peserta
admin.php        Panel admin (router)
admin/           Halaman-halaman admin
  collections.php  Editor konten generik (tambah kolom/jenis konten di sini)
config.php       Pengaturan teknis
defaults.php     Isi awal / contoh
lib.php          Fungsi bersama (penyimpanan, login, upload, dll.)
icons.php        Ikon SVG
assets/          CSS & gambar contoh
data/            Data JSON (dibuat otomatis, dilindungi .htaccess)
uploads/         Foto & logo yang diupload
```

## Keamanan

- Folder `data/` diblokir dari akses langsung lewat `.htaccess` (Apache/LiteSpeed). Di **Nginx**, tambahkan:
  ```nginx
  location ~ ^/(data|admin)/ { deny all; }
  location ~ ^/uploads/.*\.php$ { deny all; }
  ```
- Upload hanya menerima gambar asli (JPG/PNG/WEBP/GIF, maks 3 MB) dan file diberi nama acak.
- Semua aksi admin dilindungi token CSRF. Password admin disimpan dalam bentuk hash.
- Gunakan HTTPS di server produksi.

# Sistem Perbandingan Produk

Sistem Perbandingan Produk adalah aplikasi web untuk menampilkan katalog, membandingkan produk, mencatat rating dan komentar pengguna, serta membantu pengelola melihat produk yang paling diminati. Aplikasi menyediakan dashboard admin untuk mengelola produk, kategori, spesifikasi, diskon, slider, statistik penjualan, pengguna, role, dan permission.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Navbar Website Publik](#navbar-website-publik)
- [Dashboard dan Sidebar Admin](#dashboard-dan-sidebar-admin)
- [Panduan dan Format Input Data](#panduan-dan-format-input-data)
- [Urutan Pengisian Data](#urutan-pengisian-data)
- [Hak Akses](#hak-akses)
- [Teknologi](#teknologi)
- [Instalasi](#instalasi)
- [Struktur Proyek](#struktur-proyek)
- [Keamanan dan Validasi](#keamanan-dan-validasi)
- [Catatan Implementasi](#catatan-implementasi)

## Fitur Utama

### Untuk pengunjung

- Melihat halaman utama dan produk unggulan.
- Melihat galeri/katalog dan detail produk.
- Membandingkan produk berdasarkan spesifikasi dan harga.
- Menyaring produk berdasarkan rentang harga dan tanggal serta mengurutkannya.
- Memberikan rating 1–5 dan komentar setelah login.
- Membuka halaman kontak.
- Login, registrasi, verifikasi email, pengaturan profil, dan keamanan akun.

### Untuk pengelola

- Melihat analitik kunjungan dan klik produk.
- Mengelola produk, kategori, spesifikasi, diskon, slider, dan statistik penjualan.
- Melihat peringkat produk terlaris.
- Memoderasi komentar.
- Mengelola pengguna, role, permission, dan bobot penilaian.

## Navbar Website Publik

Navbar publik tampil pada halaman yang dapat dikunjungi pengguna umum.

| Menu | Fungsi |
|---|---|
| **Logo** | Kembali ke halaman utama. |
| **Home** | Menampilkan halaman utama, promosi/headline, serta ringkasan produk. |
| **Product** | Membuka galeri atau katalog seluruh produk. |
| **Compare** | Membandingkan produk menggunakan harga, spesifikasi, dan data penilaian yang tersedia. |
| **Contact** | Membuka halaman informasi kontak atau “Hubungi Kami”. |
| **Login** | Masuk ke akun yang sudah terdaftar. Hanya tampil sebelum pengguna login. |
| **Register** | Membuat akun baru. Hanya tampil sebelum pengguna login. |
| **Dashboard** | Masuk ke dashboard pengelola. Pada navbar publik saat ini tombol ditampilkan untuk role `admin` atau `super admin`. |
| **Profil pengguna** | Menampilkan nama, email, role, tautan dashboard (jika berhak), dan tombol logout. |

Saat halaman dibuka, sistem mencatat page view. Ketika produk diklik, sistem mencatat klik produk. Data ini dipakai oleh dashboard analitik.

## Dashboard dan Sidebar Admin

Dashboard hanya dapat diakses oleh pengguna yang sudah login, memiliki email terverifikasi, dan lolos pemeriksaan sesi Jetstream. Isi sidebar ditampilkan berdasarkan permission pengguna.

### Dashboard

Halaman **Dashboard** merangkum:

- **Total Visits**: jumlah seluruh kunjungan yang tercatat.
- **Unique Visitors**: jumlah alamat IP unik yang pernah berkunjung.
- **Product Clicks**: jumlah klik pada produk.
- **Daily Visits (Last 30 Days)**: grafik kunjungan harian selama 30 hari terakhir.
- **Top Product Clicks**: grafik 10 produk dengan klik terbanyak.

Hasil analitik di-cache selama 5 menit untuk mengurangi query database berulang.

### Main Menu

| Menu | Isi dan fungsi |
|---|---|
| **Dashboard** | Melihat KPI dan grafik aktivitas pengunjung. |
| **Products** | Menambah, mengubah, menghapus, dan melihat produk beserta kategori dan spesifikasinya. |
| **Category** | Mengelompokkan produk, misalnya Laptop, Smartphone, atau Kamera. |
| **Comment** | Melihat komentar serta menyetujui, menolak, mengubah, atau menghapus komentar. |

### User Management

| Menu | Isi dan fungsi |
|---|---|
| **Users** | Mengelola akun pengguna, status aktif, password, dan role. |
| **Roles** | Membuat kelompok akses seperti `owner`, `admin`, dan `staff`, lalu memasangkan permission. |
| **Permissions** | Mengelola izin tindakan yang digunakan untuk menampilkan menu dan tombol tertentu. |

### Specification

| Menu | Isi dan fungsi |
|---|---|
| **Specification Group** | Membuat kelompok spesifikasi, misalnya Performa, Layar, Kamera, atau Baterai. |
| **Specification** | Membuat atribut dalam suatu grup, menentukan tipe data, dan satuannya. |
| **Product Specifications** | Memasangkan sebuah produk dengan spesifikasi dan nilai aktualnya. |
| **Score Weights** | Menentukan bobot komponen perhitungan, misalnya `specification`, `click`, dan `sales`. |

### Content

| Menu | Isi dan fungsi |
|---|---|
| **Discount** | Menentukan diskon produk dan periode berlakunya. |
| **Headline Slider** | Mengelola banner/slide promosi pada website. |
| **Top Product** | Menampilkan produk terlaris berdasarkan bulan dan tahun. |
| **Sales Statistics** | Memasukkan jumlah penjualan setiap produk per bulan dan tahun. |

### Bagian Profil

- **Profile** digunakan untuk memperbarui nama, email, foto profil, password, autentikasi dua faktor, dan sesi browser.
- **Logout** digunakan untuk mengakhiri sesi dengan aman.

## Panduan dan Format Input Data

### 1. Produk

| Kolom | Wajib | Format yang diterima | Contoh |
|---|---:|---|---|
| Judul | Ya | Teks, maksimal 255 karakter | `Laptop Pro 14` |
| Harga | Ya | Angka minimal 0, tanpa simbol `Rp` | `12500000` |
| Stok | Ya | Bilangan bulat minimal 0 | `25` |
| Kategori | Ya | Pilih kategori yang sudah tersimpan | `Laptop` |
| Tahun produksi | Tidak | Bilangan bulat dari 1900 sampai tahun berjalan | `2026` |
| Gambar | Tidak | JPG, JPEG, PNG, atau WebP; maksimal 2 MB | `laptop-pro.webp` |
| Deskripsi | Saat edit, tidak wajib | Teks bebas | `Laptop ringan untuk pekerjaan profesional.` |
| Spesifikasi | Tidak | Nilai berupa teks sesuai atribut yang dibuat | `16`, `Ya`, atau `OLED` |

Produk baru disimpan dengan status `disable`. Ketika stok diedit, status otomatis menjadi `active` jika stok lebih dari 0 dan `disable` jika stok 0.

> Masukkan harga sebagai angka mentah. Pemisah ribuan dan simbol mata uang sebaiknya hanya ditambahkan saat tampilan, bukan pada data input.

### 2. Kategori

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Nama | Ya | Teks unik, maksimal 100 karakter | `Smartphone` |
| Status aktif | Ya | Boolean/pilihan aktif atau nonaktif | `Aktif` |

### 3. Grup Spesifikasi

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Nama grup | Ya | Teks unik, maksimal 100 karakter | `Layar` |

Grup tidak dapat dihapus jika masih memiliki spesifikasi.

### 4. Spesifikasi

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Nama | Ya | Teks, maksimal 255 karakter | `Ukuran layar` |
| Grup | Ya | Pilih grup spesifikasi yang sudah ada | `Layar` |
| Tipe data | Ya | `numeric`, `boolean`, atau `text` | `numeric` |
| Satuan | Tidak | Teks, maksimal 50 karakter | `inci` |

Gunakan `numeric` untuk angka, `boolean` untuk nilai ya/tidak, dan `text` untuk keterangan. Contoh: RAM (`numeric`, `GB`), NFC (`boolean`, tanpa satuan), dan panel (`text`, tanpa satuan).

### 5. Spesifikasi Produk

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Produk | Ya | Produk harus sudah tersimpan | `Laptop Pro 14` |
| Spesifikasi | Ya | Sudah tersimpan; pasangan produk–spesifikasi harus unik | `RAM` |
| Nilai | Ya | Teks maksimal 255 karakter | `16` |

Jika spesifikasi RAM sudah memiliki satuan `GB`, cukup masukkan `16`, bukan `16 GB`.

### 6. Bobot Nilai

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Key | Ya | Teks unik, maksimal 255 karakter | `specification` |
| Weight | Ya | Angka antara 0 dan 1 | `0.500` |

Input desimal dengan koma diubah menjadi titik saat membuat data. Total bobot tidak boleh lebih dari `1.000`; jika kurang, sistem memberi peringatan. Contoh: spesifikasi `0.500`, klik `0.200`, dan penjualan `0.300`.

### 7. Diskon

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Produk | Ya | Pilih produk yang tersedia | `Laptop Pro 14` |
| Persentase | Ya | Bilangan bulat 1–100, tanpa `%` | `15` |
| Tanggal mulai | Ya | Tanggal valid | `2026-09-01` |
| Tanggal selesai | Ya | Sama atau setelah tanggal mulai | `2026-09-30` |

### 8. Headline Slider

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Judul | Ya | Teks maksimal 255 karakter | `Promo Akhir Tahun` |
| Subjudul | Ya saat membuat | Teks | `Diskon produk pilihan hingga 30%` |
| Link | Tidak | URL lengkap dan valid | `https://contoh.com/gallery` |
| Gambar | Ya saat membuat | File gambar maksimal 2 MB | `promo.jpg` |
| Posisi | Ya | Bilangan bulat minimal 1 | `1` |
| Status aktif | Ya | Aktif/nonaktif | `Aktif` |

Posisi lebih kecil ditampilkan lebih dahulu. Saat mengedit, gambar boleh dikosongkan agar gambar lama tetap digunakan.

### 9. Statistik Penjualan

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Produk | Ya | Pilih produk berstatus aktif | `Laptop Pro 14` |
| Bulan | Ya | Bilangan bulat 1–12 | `9` |
| Tahun | Ya | 2000 sampai tahun berjalan | `2026` |
| Total terjual | Ya | Bilangan bulat minimal 0 | `120` |

Satu produk hanya mempunyai satu data untuk kombinasi bulan dan tahun yang sama. Menyimpan kombinasi yang sama akan memperbarui jumlah yang sudah ada.

### 10. Top Product

Menu ini membaca statistik penjualan dan menampilkan produk terlaris. Filter menerima bulan 1–12, tahun 2000 sampai tahun berjalan, dan batas hasil 1–50 (nilai awal 8).

### 11. Pengguna

| Kolom | Wajib | Aturan | Contoh |
|---|---:|---|---|
| Nama | Ya | Teks maksimal 100 karakter | `Budi Admin` |
| Email | Ya | Alamat email valid dan unik | `budi@example.com` |
| Password | Ya saat membuat | Minimal 6 karakter; saat edit boleh kosong | `rahasia123` |
| Role | Ya | Pilih role yang sudah ada | `admin` |
| Status aktif | Ya | Aktif/nonaktif | `Aktif` |

Password disimpan sebagai hash, bukan teks biasa.

### 12. Role dan Permission

- **Role name** wajib berupa teks unik, maksimal 50 karakter.
- **Permissions** opsional dan harus dipilih dari permission yang telah terdaftar.
- Nama permission wajib unik. Gunakan pola seperti `view.product`, `create.product`, `edit.product`, dan `delete.product`.
- Role yang masih digunakan pengguna tidak dapat dihapus.

### 13. Komentar dan Rating

Pengguna memasukkan komentar maksimal 2.000 karakter dan rating berupa bilangan bulat 1–5. Komentar baru berstatus `pending` sampai pengelola mengubahnya menjadi `approved` atau `rejected`. Satu pengguna dapat memperbarui ratingnya untuk produk yang sama.

## Urutan Pengisian Data

1. Buat **Category**.
2. Buat **Specification Group**.
3. Buat **Specification** beserta tipe data dan satuan.
4. Buat **Product**, pilih kategori, lalu isi nilai spesifikasinya.
5. Pastikan stok produk lebih dari 0 agar produk menjadi aktif setelah diperbarui.
6. Atur **Score Weights** jika perhitungan bobot digunakan.
7. Tambahkan **Discount** dan **Headline Slider** untuk promosi.
8. Masukkan **Sales Statistics** untuk menghasilkan daftar **Top Product**.
9. Pantau klik dan kunjungan melalui **Dashboard**.
10. Moderasi komentar melalui menu **Comment**.

## Hak Akses

Aplikasi menggunakan Spatie Laravel Permission. Seeder menyediakan tiga role awal:

| Role | Akses awal |
|---|---|
| `owner` | Seluruh permission dashboard, user management, produk, kategori, konten, penjualan, dan spesifikasi yang didefinisikan seeder. |
| `admin` | Hampir seluruh permission operasional dan user management. |
| `staff` | Melihat dashboard, melihat/mengedit produk, serta melihat kategori, diskon, dan top product. |

Sidebar hanya menampilkan menu jika pengguna memiliki permission terkait. Pengamanan tidak boleh hanya mengandalkan menu yang disembunyikan; route/controller juga perlu middleware atau policy permission untuk mencegah akses langsung melalui URL.

Seeder membuat akun demonstrasi:

| Role | Email | Password awal |
|---|---|---|
| Owner | `owner@gmail.com` | `password123` |
| Admin | `admin@gmail.com` | `password123` |
| Staff | `staff@gmail.com` | `password123` |

> Ganti atau hapus seluruh kredensial bawaan sebelum aplikasi digunakan di production.

## Teknologi

- PHP 8.2+, Laravel 12, MySQL
- Laravel Jetstream, Fortify, Sanctum, dan Livewire
- Spatie Laravel Permission
- Blade, Tailwind CSS, Alpine.js, dan Vite
- Chart.js untuk grafik dashboard
- Pest/PHPUnit untuk pengujian

## Instalasi

### Prasyarat

Siapkan PHP 8.2+, Composer, Node.js/NPM, MySQL, dan ekstensi PHP yang dibutuhkan Laravel.

### Menjalankan proyek

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Sesuaikan koneksi pada `.env`:

```env
APP_NAME="Sistem Perbandingan Produk"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=system_perbandingan_produk
DB_USERNAME=root
DB_PASSWORD=
```

Buat tabel, data awal, dan tautan penyimpanan:

```bash
php artisan migrate --seed
php artisan storage:link
```

Pasang dan bangun aset frontend:

```bash
npm install
npm run build
```

Jalankan aplikasi:

```bash
composer run dev
```

Alternatif terminal terpisah:

```bash
php artisan serve
npm run dev
```

Buka `http://127.0.0.1:8000`. Jalankan pengujian dengan `composer test`.

Untuk production, gunakan `APP_ENV=production`, `APP_DEBUG=false`, password database yang kuat, HTTPS, konfigurasi mail yang benar, dan cache konfigurasi Laravel.

## Struktur Proyek

```text
app/
├── Http/Controllers/     # Logika halaman, CRUD, validasi, dan analitik
├── Models/               # Model Eloquent dan relasi database
├── Services/             # Layanan perbandingan produk
└── Helpers/              # Helper perhitungan/perbandingan
database/
├── migrations/           # Struktur tabel database
└── seeders/              # Akun, role, dan permission awal
resources/
├── views/                # Blade website publik dan dashboard
├── css/                  # Gaya Tailwind
└── js/                   # JavaScript aplikasi
routes/                   # Route web dan API
public/                   # Entry point dan aset publik
tests/                    # Feature test dan unit test
```

## Keamanan dan Validasi

- Form Laravel menggunakan CSRF token untuk mencegah CSRF.
- Dashboard memakai `auth:sanctum`, sesi Jetstream, dan verifikasi email.
- Input divalidasi di server, termasuk tipe angka, batas nilai, relasi `exists`, keunikan, dan ukuran file.
- Eloquent/Query Builder menggunakan parameter binding sehingga input tidak dirangkai langsung menjadi SQL dan risiko SQL Injection berkurang.
- Sintaks Blade `{{ }}` melakukan escaping untuk mengurangi XSS. Hindari `{!! !!}` untuk data pengguna yang belum disanitasi.
- Password di-hash menggunakan mekanisme Laravel.
- Penyimpanan beberapa tabel terkait menggunakan transaksi database.
- Upload dibatasi sebagai gambar maksimal 2 MB. Di production, tetap periksa MIME, batasi akses storage, dan jangan mengizinkan eksekusi file upload.
- Rating hanya dapat diubah atau dihapus oleh pemiliknya berdasarkan `user_id`.

## Catatan Implementasi

- Menu **Comment** membutuhkan permission `comment.view`, tetapi permission itu belum dibuat oleh `PermissionSeeder`. Tambahkan dan pasangkan ke role yang sesuai agar menu muncul.
- Tombol dashboard pada navbar publik memeriksa role `admin` atau `super admin`, sedangkan seeder membuat `owner`, `admin`, dan `staff`. Role `owner` memiliki permission dashboard tetapi tombol tidak muncul sampai kondisinya diselaraskan.
- Route CRUD dashboard dilindungi autentikasi dan verifikasi email, tetapi belum terlihat middleware permission per route. Tambahkan middleware seperti `permission:view.product` atau policy agar pembatasan sidebar tidak dapat dilewati lewat URL langsung.
- Controller dan route skor spesifikasi/skor akhir tersedia, tetapi tidak ditampilkan pada sidebar baru. Selaraskan alurnya jika akan dipakai operator.
- Gambar memakai disk `public`; jalankan `php artisan storage:link` agar dapat ditampilkan.
- Chart.js dimuat dari CDN. Lingkungan tanpa internet perlu menyediakannya secara lokal.

## Lisensi

Proyek ini dibangun menggunakan Laravel. Tentukan lisensi proyek sesuai kebutuhan pemilik sebelum didistribusikan.

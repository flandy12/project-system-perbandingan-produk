# Sistem Perbandingan Produk

Sistem Perbandingan Produk adalah aplikasi web untuk menampilkan katalog, membandingkan produk, mencatat rating dan komentar pengguna, serta membantu pengelola melihat produk yang paling diminati. Aplikasi menyediakan dashboard admin untuk mengelola produk, kategori, spesifikasi, diskon, slider, statistik penjualan, pengguna, role, dan permission.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Navbar Website Publik](#navbar-website-publik)
- [Dashboard dan Sidebar Admin](#dashboard-dan-sidebar-admin)
- [Panduan dan Format Input Data](#panduan-dan-format-input-data)
- [Cara Compare Menampilkan Grafik Diagram](#cara-compare-menampilkan-grafik-diagram)
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

## Cara Compare Menampilkan Grafik Diagram

Halaman Compare menggunakan **radar chart** dari Chart.js. Diagram memperlihatkan perbandingan dua produk pada empat sumbu:

1. **Kualitas**
2. **Daya**
3. **Fitur**
4. **Konektivitas**

Setiap sumbu memiliki nilai 0–100. Semakin jauh area grafik dari titik tengah, semakin tinggi skor produk pada kategori tersebut.

### Alur dari pemilihan produk sampai grafik

```mermaid
flowchart LR
    A[Pengguna memilih produk pertama] --> B[ID disimpan di localStorage]
    B --> C[Pengguna memilih produk kedua]
    C --> D[/compare?ids=ID_A,ID_B]
    D --> E[HomeController memvalidasi dan mengambil kedua produk]
    E --> F[CompareService menormalisasi spesifikasi]
    F --> G[Blade mengirim metricsA dan metricsB ke Chart.js]
    G --> H[Radar chart 0–100 ditampilkan]
```

Proses detailnya adalah sebagai berikut:

1. Tombol **Bandingkan** menyimpan ID produk di browser melalui `localStorage` dengan key `compare_items`.
2. Pengguna hanya dapat memilih maksimal dua produk.
3. Setelah produk kedua dipilih, browser diarahkan ke URL seperti:

   ```text
   /compare?ids=12,25
   ```

4. Controller membersihkan ID nonnumerik, menghapus ID duplikat, dan hanya mengambil dua ID pertama.
5. Compare hanya dilanjutkan jika tepat dua produk ditemukan. Jika tidak, halaman menampilkan pesan kesalahan.
6. `CompareService` mencari spesifikasi yang sama-sama dimiliki kedua produk.
7. Setiap nilai dinormalisasi, dikelompokkan ke salah satu dari empat kategori, lalu dirata-ratakan menjadi skor 0–100.
8. Controller mengirim `metricsA`, `metricsB`, `percentA`, `percentB`, dan `reasons` ke Blade.
9. Chart.js menggambar dua dataset pada elemen `<canvas id="radarChart">`.

### Struktur data yang dibutuhkan mesin Compare

Mesin Compare di `CompareService` dirancang membaca struktur berikut:

| Tabel/data | Kolom penting | Fungsi |
|---|---|---|
| `spec_categories` | `name` | Menentukan sumbu grafik. Nama harus cocok dengan `kualitas`, `daya`, `fitur`, atau `konektivitas`. |
| `spec_keys` | `name`, `category_id`, `unit`, `is_higher_better` | Mendefinisikan atribut numerik seperti RAM, berat, baterai, atau versi Bluetooth. |
| `product_specs` | `product_id`, `spec_key_id`, `value` | Menyimpan nilai numerik atribut untuk setiap produk. |
| `products` | `id`, `title`, `image`, `price` | Menyediakan identitas dan informasi ringkas produk yang dibandingkan. |

Satu pasangan `product_id` dan `spec_key_id` hanya boleh tersimpan satu kali. Kolom `value` pada `product_specs` berupa desimal, sehingga nilai grafik harus dapat diubah menjadi angka.

Contoh data kategori:

| ID | Nama |
|---:|---|
| 1 | `kualitas` |
| 2 | `daya` |
| 3 | `fitur` |
| 4 | `konektivitas` |

Contoh definisi key:

| Nama key | Kategori | Satuan | Higher is better? |
|---|---|---|---:|
| Resolusi kamera | `kualitas` | `MP` | Ya |
| Kapasitas baterai | `daya` | `mAh` | Ya |
| Jumlah fitur | `fitur` | fitur | Ya |
| Versi Bluetooth | `konektivitas` | versi | Ya |
| Berat | `kualitas` | `gram` | Tidak |

`is_higher_better = true` berarti angka lebih besar dianggap lebih baik. Gunakan `false` untuk atribut seperti berat, konsumsi daya, atau waktu respons, yaitu saat angka lebih kecil lebih baik.

Contoh nilai dua produk:

| Produk | Key | Value |
|---|---|---:|
| Produk A | Kapasitas baterai | 4000 |
| Produk B | Kapasitas baterai | 5000 |
| Produk A | Berat | 180 |
| Produk B | Berat | 200 |

### Rumus normalisasi

Untuk setiap spesifikasi yang dimiliki kedua produk, sistem memakai rumus:

```text
nilai_normal = (nilai_produk - nilai_minimum) / (nilai_maksimum - nilai_minimum)
```

Hasilnya berada pada rentang 0–1. Jika `is_higher_better` bernilai `false`, skor dibalik:

```text
nilai_normal = 1 - nilai_normal
```

Setelah itu seluruh skor dalam kategori yang sama dirata-ratakan dan dikali 100:

```text
skor_kategori = rata_rata(nilai_normal_spesifikasi) × 100
```

Contoh baterai 4.000 mAh dibandingkan 5.000 mAh, dengan nilai lebih besar lebih baik:

```text
Produk A = (4000 - 4000) / (5000 - 4000) = 0   → 0 poin
Produk B = (5000 - 4000) / (5000 - 4000) = 1   → 100 poin
```

Contoh berat 180 gram dibandingkan 200 gram, dengan nilai lebih kecil lebih baik:

```text
Sebelum dibalik: Produk A = 0 dan Produk B = 1
Setelah dibalik: Produk A = 1 dan Produk B = 0
Hasil kategori: Produk A = 100 dan Produk B = 0
```

Jika kedua nilai sama, fungsi saat ini menghasilkan 0 untuk keduanya karena selisih maksimum dan minimum adalah nol.

### Perhitungan poin keseluruhan

Skor semua kategori dijumlahkan:

```text
scoreA = kualitasA + dayaA + fiturA + konektivitasA
scoreB = kualitasB + dayaB + fiturB + konektivitasB
```

Kemudian diubah menjadi persentase relatif:

```text
percentA = scoreA / (scoreA + scoreB) × 100
percentB = scoreB / (scoreA + scoreB) × 100
```

Nilai tersebut ditampilkan sebagai **Poin** dan progress bar. Jika seluruh skor nol, pembagi diamankan minimal menjadi 1 sehingga kedua poin menjadi 0.

### Data yang benar agar grafik muncul

- Harus ada tepat dua produk dengan ID berbeda.
- Kedua produk harus mempunyai `spec_key_id` yang sama agar atribut dapat dibandingkan.
- Nilai spesifikasi harus numerik, bukan teks seperti `16 GB`; simpan `16` dan letakkan `GB` pada kolom unit.
- Nama kategori harus sama persis secara logis dengan empat kategori grafik. Service mengubah nama menjadi huruf kecil, tetapi hindari spasi tambahan.
- Key harus mempunyai relasi ke kategori.
- Nilai `is_higher_better` harus benar agar arah penilaian tidak terbalik.
- Jika salah satu produk tidak memiliki sebuah key, key tersebut dilewati.
- Jika tidak ada key yang sama dalam suatu kategori, skor kategori pada grafik menjadi 0.

### Contoh data yang siap dibandingkan

```text
spec_categories
- kualitas
- daya
- fitur
- konektivitas

spec_keys
- RAM             → kategori: kualitas, unit: GB, higher: true
- Baterai         → kategori: daya, unit: mAh, higher: true
- Berat           → kategori: kualitas, unit: gram, higher: false
- Bluetooth       → kategori: konektivitas, unit: versi, higher: true

product_specs Produk A
- RAM: 8
- Baterai: 4000
- Berat: 180
- Bluetooth: 5.2

product_specs Produk B
- RAM: 12
- Baterai: 5000
- Berat: 200
- Bluetooth: 5.3
```

### Alasan “produk lebih baik”

Selain grafik, service membuat daftar alasan dengan membandingkan setiap key yang sama. Implementasi saat ini hanya memasukkan alasan ketika **Produk B** menang, sesuai judul tampilan “Mengapa Produk B lebih baik?”. Contoh:

```text
Ram lebih baik (12GB vs 8GB)
Baterai lebih baik (5000mAh vs 4000mAh)
```

Jika Produk B tidak menang pada key mana pun, sistem menampilkan pesan bahwa perbedaan tidak signifikan berdasarkan spesifikasi utama.

### Pemecahan masalah grafik

| Masalah | Kemungkinan penyebab | Solusi |
|---|---|---|
| Semua sumbu bernilai 0 | Produk tidak mempunyai key yang sama atau nama kategori tidak cocok | Pastikan kedua produk memakai `spec_key_id` yang sama dan kategori termasuk empat nama yang didukung. |
| Hanya beberapa sumbu muncul | Tidak ada data bersama pada kategori lainnya | Lengkapi nilai spesifikasi kedua produk. |
| Produk lebih ringan mendapat skor buruk | `is_higher_better` salah | Set menjadi `false` untuk berat. |
| Pesan “Pilih tepat 2 produk” | Parameter `ids` kosong, duplikat, atau bukan dua angka | Pilih dua produk berbeda atau gunakan `/compare?ids=1,2`. |
| Pesan “Produk tidak ditemukan” | Salah satu ID tidak ada di tabel produk | Gunakan ID produk yang masih tersedia. |
| Area grafik tidak tampil | Chart.js gagal dimuat dari CDN | Pastikan koneksi internet tersedia atau pasang Chart.js secara lokal. |

> **Penting:** dashboard sekarang mengelola tabel `product_specifications` dengan model spesifikasi lama, sedangkan `CompareService` mengharapkan `product_specs`, `spec_keys`, dan relasi `key`. Model/relasi dan form input untuk skema Compare belum tersambung secara lengkap. Sebelum diagram dapat diandalkan, satukan skema tersebut atau tambahkan model `ProductSpec`, relasi `Product::specs()` ke `product_specs`, relasi `ProductSpec::key()`, serta form pengelolaannya.

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

- Branch pembaruan STB membawa katalog bersumber, grafik perbandingan SVG, tautan resmi produk, impor penjualan, dan analisis K-Means.
- Jalankan `composer install`, `npm ci`, `npm run build`, `php artisan migrate`, dan `php artisan storage:link` setelah konfigurasi lingkungan lokal selesai.
- Jalankan `php artisan db:seed --class=SalesFoundationSeeder` pada database pengembangan untuk menyiapkan izin Admin, Owner, dan Pelanggan.
- Database lokal pengembang tidak ikut dikirim. Pada database yang sudah memiliki katalog STB bersumber, `php artisan db:seed --class=StbSpecificationsRefreshSeeder` menerapkan pembaruan spesifikasi. Seeder katalog penuh memakai ID produk lama; periksa pemetaan ID sebelum menggunakannya.
- Foto referensi katalog dan template impor kosong disertakan. Akun, riwayat analisis, ulasan, dan laporan penjualan dari database lokal tidak disalin.
- Pengujian: `php artisan test`. Lihat [catatan pembaruan spesifikasi](docs/PEMBARUAN-SPESIFIKASI-PERBANDINGAN-2026-11-06.md) dan [panduan impor penjualan](docs/FONDASI-DATA-DAN-IMPOR-PENJUALAN.md).
- Controller dan route skor spesifikasi/skor akhir tersedia, tetapi tidak ditampilkan pada sidebar baru. Selaraskan alurnya jika akan dipakai operator.
- Gambar memakai disk `public`; jalankan `php artisan storage:link` agar dapat ditampilkan.
- Grafik perbandingan dan analisis STB dirender sebagai SVG. Grafik kunjungan dashboard masih memakai Chart.js dari CDN.

## Lisensi

Proyek ini dibangun menggunakan Laravel. Tentukan lisensi proyek sesuai kebutuhan pemilik sebelum didistribusikan.

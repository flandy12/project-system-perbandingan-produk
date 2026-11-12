# Pembaruan spesifikasi dan grafik perbandingan — 6 November 2026

## Hasil yang diterapkan

- Halaman resmi tujuh produk Matrix dan lima model Nex diperiksa ulang. Pemeriksaan halaman bukan pernyataan bahwa semua angka lama telah diverifikasi ulang. Perubahan per nilai dicatat dalam docs/data/stb-specifications-refresh-2026-11-06.json.
- Sebanyak 17 entri fakta diterapkan tanpa konflik: jumlah USB dan dukungan mirroring Apple Kuning; EPG, teleteks dan takarir pada Kuning/DVB2IP/Burger/Garuda; GPU serta inframerah Android Box; format audio pada Nex Combo Kuning. Total spesifikasi naik dari 356 menjadi 372 (16 baris baru, satu pembaruan bukti pada baris yang sudah ada).
- Apple Kuning: halaman model resmi menyebut USB 1x Front. Nilai 1 port memakai acuan aplikasi 2 port; bukti katalog lama yang berbeda disimpan dalam evidence_history. Deskripsi model menyebut mirroring sehingga dukungan berbagi layar dicatat, tanpa menyimpulkan protokol MeeCast.
- GPU/EPG/teleteks/takarir/inframerah/format audio tambahan ditampilkan sebagai fakta tanpa menambahkan bobot skor. Tidak memberi skor pada nama chipset, satuan memori ambigu, istilah audio produsen, harga, atau klaim performa.
- Indeks fitur lengkap meningkat dari 4 menjadi 5 produk. Kelengkapan lainnya tetap ditahan sampai bukti yang sesuai tersedia.

## Penyebab grafik terlihat kosong

1. Semua kelompok spesifikasi sebelumnya masuk ke sumbu radar meskipun tidak memiliki nilai bersama.
2. Perubahan spesifikasi pada CMS belum memperbarui tabel skor lama; grafik dapat kehilangan data baru atau membaca skor kedaluwarsa.
3. Beberapa model lama memang hanya memiliki bukti kemasan terbatas, dan model berbeda jenis sering hanya memiliki satu kemampuan bersama.

## Perbaikan perhitungan dan tampilan

VerifiedComparisonScores membentuk skor saat dibaca dari spesifikasi saat ini, nilai yang diverifikasi, sumber, serta acuan positif yang tercatat. Data berbeda dari bukti tidak digunakan; reference:null yang secara eksplisit mengecualikan kriteria tetap dihormati. Bukti baru dari CMS dapat memakai acuan rubrik konfigurasi bila metadata tidak memiliki kolom reference. Skor cache lama tidak menjadi dasar perbandingan produk yang tersimpan.

Data nonpublik juga dikecualikan. Akibatnya kelompok Teknologi bisa hilang dari radar bila sebelumnya terisi skor jumlah keluarga sinyal yang sudah ditandai public:false. Ini koreksi kelayakan data, bukan penghapusan spesifikasi.

Grafik SVG dibuat di server: radar untuk setidaknya tiga kelompok bersama, batang untuk satu atau dua kelompok. Sumbu kosong tidak digambar; kelompok yang tidak dihitung disebutkan di bawah grafik. Tidak memakai Chart.js/CDN pada halaman perbandingan. Semua data tabel dasar skor dan skor pasangan tetap tersedia. Nilai nol terverifikasi dipertahankan dan berbeda dari belum diketahui.

Nilai 100 pada dua kriteria dasar bukan bukti bahwa seluruh kemampuan produk sempurna. Produk sejenis masih dapat kekurangan dokumentasi; perbandingan lintas jenis tidak menentukan pemenang. Skor pasangan bukan indeks fitur tetap K-Means.

## Sumber yang digunakan

Halaman berikut dibaca melalui web pada tanggal pemeriksaan; sebagian respons dapat berasal dari indeks/cache penyedia web. Harga serta stok tidak diperbarui dan tidak diperlakukan sebagai observasi langsung baru.

- Apple Kuning: https://www.matrixparabola.com/product/matrix-set-top-box-apple-kuning-dvbt2-hd-stb-tv-digital
- Apple Merah HD: https://www.matrixparabola.com/product/matrix-set-top-box-apple-merah-dvbt2-hd-stb-tv-digital
- Apple Merah FM: https://www.matrixparabola.com/product/matrix-set-top-box-apple-merah-dvbt2-radio-fm-stb-tv-digital
- Apple DVB2IP: https://www.matrixparabola.com/product/matrix-set-top-box-apple-merah-dvbt2ip-stb-tv-digital
- Burger: https://www.matrixparabola.com/product/matrix-set-top-box-burger-hijau-dvb-t2-mt-stb-tv-digital
- Garuda: https://www.matrixparabola.com/product/matrix-set-top-box-garuda-dvbt2-hd-stb-tv-digital
- Android: https://www.matrixparabola.com/product/matrix-apple-android-tv-box-android-10-ram-2gb-wifi-android-tv
- Lima model Nex (Tipe MATRIX/Biru/Kuning, Combo Kuning/Merah): https://www.mynex.co.id/receiver

Pencarian sumber resmi tambahan untuk Silver dan model Mola lama belum menghasilkan sumber yang cocok. Spesifikasi generasi baru tidak disalin ke model lama. Dukungan WiFi, HEVC, jumlah port, kapasitas memori dan tanggal produksi tidak disimpulkan dari model lain atau ketidakadaan teks pada halaman.

## Verifikasi dan pemeliharaan

- 96 tes lulus, 3581 assertion; 7 dilewati sesuai fitur tidak aktif. Tiga tes baru memeriksa skor langsung dari bukti terbaru, cache kedaluwarsa, bukti kosong, nilai nol, pengecualian eksplisit, impor berulang dan perlindungan perubahan Admin.
- Build aset, kompilasi Blade dan pemeriksaan whitespace lulus.
- Seluruh 171 pasangan dari 19 produk database lokal diperiksa tanpa kegagalan: 123 lintas jenis, 11 dengan pemenang pada data bersama, 37 seri. Pemeriksaan ini tidak membuktikan kelengkapan spesifikasi tiap model.
- Browser /compare?ids=4,8: radar tiga kelompok, 10 kriteria bersama, skor 90,3 vs 94,4; keunggulan yang dijelaskan adalah 2 port USB Burger vs 1 port Apple Kuning.
- Browser /compare?ids=10,11: dua kelompok bersama ditampilkan sebagai grafik batang, tanpa sumbu kosong atau ketergantungan CDN.
- Katalog lokal tetap 19 produk; harga/stok/foto/penjualan/tautan Admin tidak diubah. Snapshot analisis historis tidak dihitung ulang atau ditimpa.
- Penambahan bukti dapat membuat publikasi analisis lama tidak lagi sesuai fingerprint; jalankan analisis baru sebelum menerbitkan kembali hasil nyata.
- Pembaruan dapat diulang dengan seeder StbSpecificationsRefreshSeeder. Seeder katalog penuh tidak dijalankan karena dapat menimpa perubahan Admin. Konflik nilai dicatat dan dilewati.

Saran lanjutan: prioritaskan kelengkapan pada model sejenis yang sering dibandingkan, tambahkan sumber dan tanggal per fakta melalui CMS, lalu gunakan panduan/manual khusus model atau konfirmasi produsen untuk varian lama yang belum terdokumentasi. Laporan penjualan bulanan tetap diperlukan untuk K-Means nyata; spesifikasi internet tidak menggantikannya.

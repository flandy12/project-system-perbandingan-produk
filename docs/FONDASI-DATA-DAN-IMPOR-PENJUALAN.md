# Fondasi data dan impor penjualan

Diterapkan pada 6 Oktober 2026. Mesin K-Means belum diaktifkan.

## Yang sudah tersedia

- Menu **Kesiapan Data & Impor** di panel pengelola: `/sales-import`.
- Kode produk stabil untuk 19 produk lama. Produk baru memperoleh kode otomatis; kode tidak berubah ketika judul diperbarui.
- Catatan penjualan memiliki sumber, referensi laporan, status verifikasi, penanda simulasi, dan asal batch impor. Lima catatan lama dipertahankan sebagai belum diverifikasi.
- Pratinjau Excel, kesalahan per baris, penyimpanan dalam transaksi, histori batch milik pengunggah, dan pencegahan penerapan batch dua kali.
- Duplikat produk–tahun–bulan dilewati, tanpa menimpa atau menjumlahkan catatan yang ada. Gunakan Edit penjualan manual untuk koreksi yang disengaja. Koreksi manual melepas tautan batch pada catatan aktif; snapshot batch tetap tersimpan.
- Pembatasan pengelolaan pada server berdasarkan izin. Admin dan peran pengelola-data mempunyai izin sesuai fungsi. Pelanggan tidak dapat membuka pengelolaan produk, penjualan, atau akun. Akses pelanggan untuk mengirim ulasan produk tetap tersedia. Peran owner/staff lama dan penugasan akun tidak dihapus atau dipindahkan otomatis.
- Scope `ProductSalesStat::verifiedReal()` disediakan untuk analisis berikutnya: hanya catatan terverifikasi, bukan simulasi, dengan sumber dan referensi terisi. Rekomendasi publik lama belum diganti dengan K-Means.

## Mengisi template

1. Buka menu Kesiapan Data & Impor, lalu **Unduh template Excel**.
2. Lembar ReferensiProduk memuat 19 produk nyata dari snapshot katalog, harga dasar yang tersedia, tanggal, status, dan URL sumber harga. Harga historis/stok habis bukan penawaran aktif. Dua harga yang belum terbukti tetap kosong. File ini adalah snapshot 6 Oktober 2026; referensi perlu diperbarui bila katalog bertambah.
3. Di lembar Penjualan, pilih kode produk. Isi tahun, bulan, dan unit terjual dari laporan yang sama periodenya. Nol diisi hanya jika laporan menyatakan tidak ada penjualan. Hapus baris produk yang belum memiliki laporan; jangan isi nol sebagai pengganti data kosong.
4. Isi sumber, misalnya nama laporan toko, serta referensi, misalnya nomor dokumen atau lokasi laporan yang dapat diperiksa. Jangan memakai URL spesifikasi sebagai bukti penjualan.
5. Pilih `verified` hanya setelah jumlah dicocokkan dengan laporan. Pilih `unverified` jika pemeriksaan belum selesai. `is_simulasi=0` untuk laporan nyata, `1` untuk pengujian. Pilihan ini menyatakan hasil pemeriksaan pengelola; aplikasi tidak memverifikasi dokumen secara otomatis.
6. Unggah `.xlsx` (maksimal 5 MB). Header wajib pada A6:H6; isi mulai baris 7. Formula ditolak, jumlah harus bulat tidak negatif, periode masa depan ditolak. Batas 5.000 baris dan 30 MB isi setelah ekstraksi.
7. Baca pratinjau. Bila ada kesalahan, perbaiki file dan unggah kembali. Data tidak disimpan sebagian. Jika semua baris valid, tekan **Simpan data valid**.

Kolom: kode_produk, tahun, bulan, unit_terjual, sumber, referensi_sumber, status_verifikasi, is_simulasi.

Tidak ada angka penjualan baru yang dibuat untuk memenuhi kelengkapan. Data penjualan asli perlu berasal dari laporan toko/perusahaan. Nilai terjual pada marketplace memiliki cakupan penjual dan periode tersendiri; jangan diperlakukan sebagai penjualan toko atau seluruh produk.

## Instalasi dan verifikasi

Pembaca `.xlsx` memakai PhpSpreadsheet di backend PHP. Migrasi `2026_10_06_000003_add_sales_provenance_and_imports` dan `SalesFoundationSeeder` sudah diterapkan pada database lokal. Seeder ini idempotent dan tidak mengisi unit penjualan.

Pemeriksaan meliputi nol/kosong, kode tidak dikenal, periode masa depan, formula, duplikat dalam file, konflik catatan lama, batch berulang, atomisitas, pembatasan aktor, akses batch milik pengguna lain, serta format template yang diserahkan. Pengujian basis data memakai SQLite terpisah, bukan database katalog lokal.

Batas tahap ini: identitas revisi perangkat, rubrik fitur tetap, snapshot dataset, dan perhitungan K-Means masih mengikuti peta pengembangan tahap berikutnya. Jangan menganggap tabel kesiapan harga/penjualan sebagai penilaian kelayakan fitur atau hasil cluster.

Hasil akhir: 10 pengujian fitur impor/akses lulus. Pengujian regresi katalog, perbandingan, rekomendasi, dan diskon juga lulus. Tampilan Admin diperiksa langsung di browser. Template dirender dan diperiksa secara visual; layout serta penjualan kosong dibaca ulang melalui pembaca Excel aplikasi. Database lokal tetap memiliki 19 produk berkode, 5 catatan penjualan, 0 penjualan nyata terverifikasi, dan 0 batch impor baru.

Dokumentasi pembaca Excel: https://phpspreadsheet.readthedocs.io/en/latest/

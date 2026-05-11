# Sistem_POS_Toko_Roti
## Deskripsi Aplikasi
Sistem POS TokoRoti adalah aplikasi manajemen penjualan berbasis web yang dikembangkan menggunakan PHP Native dan database MySQL untuk membantu operasional harian toko roti secara terintegrasi. Aplikasi ini memfasilitasi peran Manajer dalam mengelola inventori produk melalui fitur CRUD lengkap, serta manajemen akun karyawan. Di sisi operasional, fitur Kasir memungkinkan pemrosesan transaksi secara efisien dengan pencarian menu real-time, fleksibilitas metode pembayaran (Cash, QRIS, Transfer), hingga pencetakan struk belanja otomatis. Dilengkapi dengan dashboard analisis dan fitur ekspor laporan ke format CSV, sistem ini memastikan seluruh aktivitas bisnis terdokumentasi dengan rapi dan mudah dievaluasi. 

## Struktur Tabel Utama

## Cara Menjalankan Aplikasi
1. Pastikan folder **TokoRoti** sudah berada di dalam direktori server lokal, misalnya `C:\laragon\www\TokoRoti` (pada Laragon) atau `C:\xampp\htdocs\TokoRoti` (pada XAMPP).
2. Buka **phpMyAdmin**, buat database baru bernama `tokoroti`.
3. Pilih menu **Import**, lalu unggah file database `tokoroti (4).sql`.
4. Buka file `config/database.php` di VS Code dan pastikan pengaturan *host*, *dbname*, *username*, dan *password* sudah sesuai dengan kredensial database lokal.
5. Aktifkan layanan **Apache** dan **MySQL** pada panel kontrol Laragon atau XAMPP.
6. Buka halaman `localhost/TokoRoti/public/index.php` untuk mengakses halaman login.
7. Gunakan salah satu akun berikut untuk masuk ke dalam sistem:
   * **Akses Manajer**: Username `paul.mescal` | Password `paul123`.
   * **Akses Kasir**: Username `pedro.pascal` | Password `pedro123`.

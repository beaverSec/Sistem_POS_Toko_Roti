# Sistem_POS_Toko_Roti
## Deskripsi Aplikasi
Sistem POS TokoRoti adalah aplikasi manajemen penjualan berbasis web yang dikembangkan menggunakan PHP Native dan database MySQL untuk membantu operasional harian toko roti secara terintegrasi. Aplikasi ini memfasilitasi peran Manajer dalam mengelola inventori produk melalui fitur CRUD lengkap, serta manajemen akun karyawan. Di sisi operasional, fitur Kasir memungkinkan pemrosesan transaksi secara efisien dengan pencarian menu real-time, fleksibilitas metode pembayaran (Cash, QRIS, Transfer), hingga pencetakan struk belanja otomatis. Dilengkapi dengan dashboard analisis dan fitur ekspor laporan ke format CSV, sistem ini memastikan seluruh aktivitas bisnis terdokumentasi dengan rapi dan mudah dievaluasi. 

Fitur yang sistem ini miliki yaitu:
### Manajer
- Login & logout dengan session
- Dashboard, yaitu ringkasan pendapatan, transaksi, stok menipis, menu terlaris
- Inventori, yaitu fitur di mana manajer/admin dapat melakukan tambah, edit, hapus menu (soft delete), upload foto menu
- Laporan transaksi, yaitu filter berdasarkan tanggal dan kasir
- Manajemen karyawan, yaitu fitur di mana manajer/admin dapat melakukan tambah, edit, nonaktifkan akun kasir

### Kasir
- Login & logout
- Halaman kasir (POS) yang memungkinkan kasir memilih menu, memasukkan menu ke keranjang belanja, & hitung kembalian otomatis
- Proses transaksi, yaitu simpan ke database dengan multi-item sekaligus
- Popup struk setelah transaksi berhasil
- Cetak struk / simpan sebagai PDF via browser

## Struktur Tabel Utama
### `karyawan`
Menyimpan data pegawai yang dapat login ke sistem.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_karyawan` | varchar(10) | Primary key, contoh: K001 |
| `nama_karyawan` | varchar(50) | Nama lengkap |
| `jabatan` | varchar(50) | `Kasir` atau `Manajer` |
| `username` | varchar(50) | Username untuk login |
| `password` | varchar(255) | Password ter-hash (MD5) |
| `is_active` | tinyint(1) | 1 = aktif, 0 = nonaktif |

### `kategori`
Kategori produk yang tersedia di toko.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_kategori` | varchar(10) | Primary key, contoh: KTG01 |
| `nama_kategori` | varchar(50) | Nama kategori, contoh: Pastry |
| `icon_kategori` | varchar(50) | Emoji/icon opsional |

### `menu`
Daftar produk yang dijual.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_menu` | varchar(10) | Primary key, contoh: MN01 |
| `nama_menu` | varchar(50) | Nama produk |
| `stok` | int | Jumlah stok saat ini |
| `harga` | int | Harga satuan (Rupiah) |
| `gambar` | varchar(255) | Nama file foto di `assets/uploads/` |
| `id_kategori` | varchar(10) | Foreign key → `kategori` |
| `is_deleted` | tinyint(1) | 0 = aktif, 1 = dihapus (soft delete) |

### `transaksi`
Header transaksi — satu baris per transaksi.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_transaksi` | varchar(10) | Primary key, contoh: TR0011 |
| `waktu_transaksi` | datetime | Waktu transaksi dibuat |
| `total_bayar` | int | Total harga semua item |
| `uang_bayar` | int | Uang yang diberikan pelanggan |
| `kembalian` | int | Kembalian (`uang_bayar - total_bayar`) |
| `metode_bayar` | varchar(20) | `Cash`, `QRIS`, atau `Transfer` |
| `status` | varchar(20) | `Selesai` atau `Batal` |
| `id_karyawan` | varchar(10) | Foreign key → `karyawan` |

### `detail_transaksi`
Detail item per transaksi — satu baris per item yang dibeli.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id_detailTransaksi` | varchar(10) | Primary key |
| `id_menu` | varchar(10) | Foreign key → `menu` |
| `id_transaksi` | varchar(10) | Foreign key → `transaksi` |
| `jumlah` | int | Jumlah item yang dibeli |
| `subtotal` | int | `harga × jumlah` |

Trigger Database

**`after_detail_transaksi_insert`** akan berjalan otomatis setiap kali item baru ditambahkan ke `detail_transaksi`. Fungsinya mengurangi stok menu secara otomatis:

```sql
UPDATE menu SET stok = stok - NEW.jumlah WHERE id_menu = NEW.id_menu;
```
View Database

| View | Kegunaan |
|------|----------|
| `detailstruk` | Detail item per transaksi untuk tampilan struk |
| `transaksilengkap` | Gabungan transaksi + nama karyawan + status |
| `menubasedonkategori` | Daftar menu aktif dikelompokkan per kategori |

---

## Cara Menjalankan Aplikasi
1. Pastikan folder **TokoRoti** sudah berada di dalam direktori server lokal, misalnya `C:\laragon\www\TokoRoti` (pada Laragon) atau `C:\xampp\htdocs\TokoRoti` (pada XAMPP).
2. Buka **phpMyAdmin**, buat database baru bernama `tokoroti`.
3. Pilih menu **Import**, lalu unggah file database `tokoroti (4).sql`.
4. Buka file `config/database.php` di VS Code dan pastikan pengaturan *host*, *dbname*, *username*, dan *password* sudah sesuai dengan kredensial database lokal.
5. Aktifkan layanan **Apache** dan **MySQL** pada panel kontrol Laragon atau XAMPP.
6. Buka halaman `localhost/TokoRoti/public/index.php` untuk mengakses halaman login.
7. Gunakan salah satu akun berikut untuk masuk ke dalam sistem:
   * **Akses Manajer**: Username `paul.mescal` | Password `paul123`.
   * **Akses Kasir 1**: Username `pedro.pascal` | Password `pedro123`.
   * **Akses Kasir 2**: Username `theo.james` | Password `theo123`.

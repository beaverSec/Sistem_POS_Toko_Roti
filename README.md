# Sistem_POS_Toko_Roti
TEST TEST
## Database
1. Tambah kolom username, password, is_active ke tabel karyawan
   Password cukup di-hash pakai MD5 atau password_hash() PHP — tidak perlu JWT

2. Perbaiki bug di view transaksilengkap — syntax ORDER BY ... AS DESCdesc ASC tidak valid, ganti ke ORDER BY waktu_transaksi DESC

3. Tambah kolom uang_bayar, kembalian, metode_bayar, status ke tabel transaksi
   
4. Tambah kolom is_deleted ke tabel menu untuk soft delete
   Supaya histori transaksi lama tidak error saat menu dihapus
   
SETELAH ITU
1. Buat view laporan_harian — total transaksi dan pendapatan dikelompokkan per hari
2. Ekspor file .sql terbaru setiap ada perubahan skema, bagikan ke seluruh anggota tim
   Ini penting karena aplikasi jalan lokal — semua harus pakai versi database yang sama

## Backend
1. Buat koneksi.php — satu file koneksi ke MySQL yang di-include oleh semua halaman
2. Buat login.php dan logout.php — cek username + password dari DB, simpan ke $_SESSION
   Cukup pakai session_start() dan $_SESSION['role'], tidak perlu JWT
3. Buat proses/transaksi.php — terima data dari form kasir, hitung total + kembalian, simpan ke tabel transaksi dan detail_transaksi sekaligus
4. Buat proses/menu_add.php, menu_edit.php, menu_delete.php — CRUD menu dengan soft delete
   
SETELAH ITU
1. Tambahkan pengecekan stok sebelum transaksi diproses — tampilkan pesan error jika stok tidak cukup
2. Buat query untuk halaman laporan — ambil data dari view laporan_harian dan transaksilengkap
3. Proteksi semua halaman dengan cek $_SESSION di bagian atas setiap file — redirect ke login jika belum masuk

## UI/UX Designer
1. Buat halaman login.php — form username + password, tampilkan pesan error dari session jika login gagal
2. Buat halaman kasir index.php — tampilkan daftar menu dari DB, keranjang belanja, input uang bayar, dan tampilkan kembalian secara otomatis
   Kembalian bisa dihitung langsung di JS saat kasir input uang — tidak perlu tunggu PHP
3. Buat halaman manajemen menu menu.php — tabel daftar menu, tombol tambah, edit, dan hapus
   
SETELAH ITU
1. Buat halaman laporan laporan.php — tabel transaksi per hari, total pendapatan
   Halaman ini hanya ditampilkan untuk role manajer — backend yang handle redirect-nya
2. Buat tampilan struk sederhana setelah transaksi berhasil — bisa berupa halaman baru atau popup
3. Pastikan tampilan responsif dan nyaman dipakai di layar laptop — font cukup besar, tombol mudah diklik

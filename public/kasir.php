<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

// Ambil semua menu aktif beserta kategorinya
$menu = $conn->query(
    "SELECT m.id_menu, m.nama_menu, m.stok, m.harga, k.nama_kategori
     FROM menu m
     JOIN kategori k ON m.id_kategori = k.id_kategori
     WHERE m.is_deleted = 0 AND m.stok > 0
     ORDER BY k.nama_kategori, m.nama_menu"
)->fetchAll(PDO::FETCH_ASSOC);

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir - TokoRoti</title>
</head>
<body>
<div class="topbar">
    <h2>Kasir - TokoRoti</h2>
    <span>👤 <?= htmlspecialchars($_SESSION['nama']) ?></span>
    <a href="../process/logout.php">Logout</a>
</div>

<?php if ($pesan === 'sukses'): ?>
    <p style="color:green;">Transaksi berhasil disimpan!</p>
<?php elseif ($pesan === 'gagal'): ?>
    <p style="color:red;">Transaksi gagal. Cek kembali stok dan input.</p>
<?php endif; ?>

<div class="kasir-layout">
    <!-- Kiri: Daftar Menu -->
    <div class="menu-panel">
        <h3>Pilih Menu</h3>
        <div class="menu-grid">
            <?php foreach ($menu as $m): ?>
            <div class="menu-card" onclick="tambahKeKeranjang('<?= $m['id_menu'] ?>', '<?= addslashes($m['nama_menu']) ?>', <?= $m['harga'] ?>, <?= $m['stok'] ?>)">
                <div class="menu-nama"><?= htmlspecialchars($m['nama_menu']) ?></div>
                <div class="menu-kategori"><?= htmlspecialchars($m['nama_kategori']) ?></div>
                <div class="menu-harga">Rp <?= number_format($m['harga'], 0, ',', '.') ?></div>
                <div class="menu-stok">Stok: <?= $m['stok'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kanan: Keranjang -->
    <div class="keranjang-panel">
        <h3>Keranjang</h3>
        <div id="keranjang-kosong" style="color:gray;">Belum ada item dipilih.</div>
        <table id="tabel-keranjang" style="display:none; width:100%;" border="1" cellpadding="6">
            <thead>
                <tr><th>Menu</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody id="isi-keranjang"></tbody>
        </table>

        <div id="section-bayar" style="display:none; margin-top:12px;">
            <p><strong>Total: Rp <span id="total-display">0</span></strong></p>
            <label>Uang Bayar: <input type="number" id="uang-bayar" oninput="hitungKembalian()" min="0"></label>
            <p>Kembalian: Rp <span id="kembalian-display">0</span></p>
            <label>Metode:
                <select id="metode-bayar">
                    <option value="Cash">Cash</option>
                    <option value="QRIS">QRIS</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </label>
            <br><br>
            <button onclick="prosesTransaksi()">Proses Transaksi</button>
            <button onclick="resetKeranjang()">Reset</button>
        </div>
    </div>
</div>

<!-- Form tersembunyi untuk submit ke PHP -->
<form id="form-transaksi" action="../process/transaksi_process.php" method="POST" style="display:none;">
    <input type="hidden" name="total_bayar"  id="input-total">
    <input type="hidden" name="uang_bayar"   id="input-uang">
    <input type="hidden" name="kembalian"    id="input-kembalian">
    <input type="hidden" name="metode_bayar" id="input-metode">
    <input type="hidden" name="items"        id="input-items">
</form>

<script>
let keranjang = {};
let stokTersedia = {};

function tambahKeKeranjang(id, nama, harga, stok) {
    if (!stokTersedia[id]) stokTersedia[id] = stok;

    if (keranjang[id]) {
        if (keranjang[id].qty >= stokTersedia[id]) {
            alert('Stok tidak mencukupi!');
            return;
        }
        keranjang[id].qty++;
    } else {
        keranjang[id] = { nama, harga, qty: 1 };
    }
    renderKeranjang();
}

function ubahQty(id, delta) {
    if (!keranjang[id]) return;
    keranjang[id].qty += delta;
    if (keranjang[id].qty <= 0) {
        delete keranjang[id];
    }
    renderKeranjang();
}

function renderKeranjang() {
    const tbody = document.getElementById('isi-keranjang');
    const kosong = document.getElementById('keranjang-kosong');
    const tabel = document.getElementById('tabel-keranjang');
    const sectionBayar = document.getElementById('section-bayar');

    tbody.innerHTML = '';
    let total = 0;

    const keys = Object.keys(keranjang);
    if (keys.length === 0) {
        kosong.style.display = 'block';
        tabel.style.display = 'none';
        sectionBayar.style.display = 'none';
        return;
    }

    kosong.style.display = 'none';
    tabel.style.display = 'table';
    sectionBayar.style.display = 'block';

    keys.forEach(id => {
        const item = keranjang[id];
        const subtotal = item.harga * item.qty;
        total += subtotal;
        tbody.innerHTML += `
            <tr>
                <td>${item.nama}</td>
                <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
                <td>
                    <button onclick="ubahQty('${id}', -1)">-</button>
                    ${item.qty}
                    <button onclick="ubahQty('${id}', 1)">+</button>
                </td>
                <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                <td><button onclick="ubahQty('${id}', -${item.qty})">✕</button></td>
            </tr>`;
    });

    document.getElementById('total-display').textContent = total.toLocaleString('id-ID');
    hitungKembalian();
}

function hitungKembalian() {
    const total = parseInt(document.getElementById('total-display').textContent.replace(/\./g, '')) || 0;
    const uang  = parseInt(document.getElementById('uang-bayar').value) || 0;
    const kembalian = uang - total;
    document.getElementById('kembalian-display').textContent = 
        kembalian >= 0 ? kembalian.toLocaleString('id-ID') : '—';
}

function prosesTransaksi() {
    const total   = parseInt(document.getElementById('total-display').textContent.replace(/\./g, '')) || 0;
    const uang    = parseInt(document.getElementById('uang-bayar').value) || 0;
    const metode  = document.getElementById('metode-bayar').value;

    if (Object.keys(keranjang).length === 0) { alert('Keranjang kosong!'); return; }
    if (metode === 'Cash' && uang < total) { alert('Uang bayar kurang!'); return; }

    document.getElementById('input-total').value    = total;
    document.getElementById('input-uang').value     = uang;
    document.getElementById('input-kembalian').value = uang - total;
    document.getElementById('input-metode').value   = metode;
    document.getElementById('input-items').value    = JSON.stringify(keranjang);

    document.getElementById('form-transaksi').submit();
}

function resetKeranjang() {
    keranjang = {};
    stokTersedia = {};
    document.getElementById('uang-bayar').value = '';
    renderKeranjang();
}
</script>
</body>
</html>

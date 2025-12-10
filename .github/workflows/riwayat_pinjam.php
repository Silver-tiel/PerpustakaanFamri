<?php
require 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = $_SESSION['user'];
$data = loadData();
$peminjaman = $data['peminjaman'];
$pengembalian = $data['pengembalian'];

// Filter for current user
$myPeminjaman = array_filter($peminjaman, function($i) use ($user) { return $i['nama'] === $user['nama']; });
$myPengembalian = array_filter($pengembalian, function($i) use ($user) { return $i['nama'] === $user['nama']; });

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Peminjaman</title>
    <style>
        /* Styles adapted */
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        .navbar { background: #2c3e50; padding: 15px 40px; display: flex; justify-content: space-between; color: white; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.07); border-radius: 8px; overflow: hidden; }
        th { background: #2d3436; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pinjam { background: #fdcb6e; color: #333; }
        .status-kembali { background: #55efc4; color: #333; }
        .alert { padding: 15px; margin-bottom: 20px; background: #d4edda; color: #155724; border-radius: 5px; text-align: center; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Perpustakaan FAMRI</div>
    <div class="nav-links">
        <a href="index.php">Beranda</a>
        <a href="pinjam.php">Pinjam Buku</a>
        <a href="pengembalian.php">Pengembalian</a>
        <a href="riwayat_pinjam.php">Riwayat</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>📚 Riwayat Peminjaman Buku</h2>
    <br>
    <?php if ($flash): ?>
        <div class="alert"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tanggal Kembali</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            // Merge lists for display (Active first)
            $allHistory = array_merge($myPeminjaman, $myPengembalian);
            
            if (empty($allHistory)): ?>
                <tr><td colspan="7" style="text-align:center; padding: 20px;">Belum ada riwayat.</td></tr>
            <?php else: 
                foreach ($allHistory as $item): 
                    $dueDate = $item['jatuhTempo'] ?? '-';
                    $denda = isset($item['denda']) && $item['denda'] > 0 ? 'Rp ' . number_format($item['denda'], 0, ',', '.') : '-';
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($item['book_title']) ?></td>
                    <td><?= htmlspecialchars($item['tanggalPinjam']) ?></td>
                    <td><?= htmlspecialchars($dueDate) ?></td>
                    <td><?= isset($item['tanggalPengembalian']) ? htmlspecialchars($item['tanggalPengembalian']) : '-' ?></td>
                    <td style="color: <?= $denda !== '-' ? 'red' : 'inherit' ?>; font-weight: <?= $denda !== '-' ? 'bold' : 'normal' ?>"><?= $denda ?></td>
                    <td>
                        <span class="status-badge <?= $item['status'] === 'Dipinjam' ? 'status-pinjam' : 'status-kembali' ?>">
                            <?= htmlspecialchars($item['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; 
            endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

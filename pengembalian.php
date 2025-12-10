<?php
require 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = $_SESSION['user'];
$data = loadData();
$peminjaman = $data['peminjaman'];

// Filter Active Loans for User
$activeLoans = array_filter($peminjaman, function($loan) use ($user) {
    return ($loan['nama'] === $user['nama']);
});

// Handle Return
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = $_POST['loan_id'];
    $returnDate = $_POST['return_date'];

    $loanIndex = -1;
    $loanData = null;

    foreach ($data['peminjaman'] as $index => $loan) {
        if ($loan['id'] == $loanId) {
            $loanIndex = $index;
            $loanData = $loan;
            break;
        }
    }

    if ($loanData) {
        // Calculate Fine
        $dueDate = $loanData['jatuhTempo'] ?? date('Y-m-d', strtotime($loanData['tanggalPinjam'] . ' + 7 days')); // Fallback
        $denda = calculateFine($dueDate, $returnDate);

        // Move to Pengembalian
        $returnRecord = $loanData;
        $returnRecord['tanggalPengembalian'] = $returnDate;
        $returnRecord['status'] = 'Dikembalikan';
        $returnRecord['denda'] = $denda;
        $returnRecord['id'] = time(); // New ID for return record

        $data['pengembalian'][] = $returnRecord;

        // Increase Stock
        foreach ($data['books'] as &$book) {
            if ($book['id'] == $loanData['book_id']) {
                $book['quantity']++;
                break;
            }
        }

        // Remove from Peminjaman
        array_splice($data['peminjaman'], $loanIndex, 1);

        saveData($data);
        
        $msg = 'Buku berhasil dikembalikan!';
        if ($denda > 0) {
            $msg .= " Anda terkena denda sebesar Rp " . number_format($denda, 0, ',', '.');
        }
        
        setFlashMessage('success', $msg);
        redirect('riwayat_pinjam.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengembalian Buku</title>
    <style>
        /* Styles adapted */
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        .navbar { background: #2c3e50; padding: 15px 40px; display: flex; justify-content: space-between; color: white; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .form-section { padding: 40px; text-align: center; }
        .form-box { max-width: 400px; margin: auto; background: #fff; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px; }
        label { display: block; margin-top: 15px; text-align: left; font-weight: bold; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #aaa; border-radius: 5px; }
        .btn { width: 100%; padding: 10px; margin-top: 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #1a252f; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Perpustakaan FAMRI</div>
    <div class="nav-links">
        <a href="dashboard.php">Beranda</a>
        <a href="pinjam.php">Pinjam Buku</a>
        <a href="pengembalian.php">Pengembalian</a>
        <a href="riwayat_pinjam.php">Riwayat</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<section class="form-section">
    <h2>Formulir Pengembalian Buku</h2>
    <form class="form-box" method="POST">
        <label>Nama Peminjam</label>
        <input type="text" value="<?= htmlspecialchars($user['nama']) ?>" disabled>

        <label>Judul Buku yang Dipinjam</label>
        <?php if (empty($activeLoans)): ?>
            <p style="text-align:left; margin-top:5px; color:red;">Anda tidak memiliki buku yang sedang dipinjam.</p>
        <?php else: ?>
            <select name="loan_id" required>
                <option value="">Pilih Buku...</option>
                <?php foreach ($activeLoans as $loan): ?>
                    <option value="<?= $loan['id'] ?>"><?= htmlspecialchars($loan['book_title']) ?> (Dipinjam: <?= $loan['tanggalPinjam'] ?>)</option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <label>Tanggal Pengembalian</label>
        <input type="date" name="return_date" required value="<?= date('Y-m-d') ?>">

        <?php if (!empty($activeLoans)): ?>
            <button class="btn" type="submit">Kembalikan Buku</button>
        <?php else: ?>
            <button class="btn" type="button" disabled style="background:#ccc; cursor:not-allowed;">Kembalikan Buku</button>
        <?php endif; ?>
    </form>
</section>

</body>
</html>

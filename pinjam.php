<?php
require 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = $_SESSION['user'];
$data = loadData();
$books = $data['books'];

// Handle Borrowing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = $_POST['book_id'];
    $borrowDate = $_POST['borrow_date'];

    // Validation
    $selectedBook = null;
    $bookIndex = -1;
    foreach ($books as $index => $b) {
        if ($b['id'] == $bookId) {
            $selectedBook = $b;
            $bookIndex = $index;
            break;
        }
    }

    if ($selectedBook && $selectedBook['quantity'] > 0) {
        
        // CHECK LOAN LIMIT
        $activeLoans = getActiveLoanCount($user['id'], $data);
        if ($activeLoans >= 3) {
            setFlashMessage('error', 'Batasan Peminjaman: Maksymal 3 buku!');
            redirect('pinjam.php');
        }

        // Calculate Due Date (7 days)
        $date = new DateTime($borrowDate);
        $date->modify('+7 days');
        $dueDate = $date->format('Y-m-d');

        // Create Loan Record
        $loan = [
            'id' => time(),
            'user_id' => $user['id'] ?? $user['nama'], // Fallback if no ID
            'nama' => $user['nama'],
            'book_id' => $selectedBook['id'],
            'book_title' => $selectedBook['title'],
            'tanggalPinjam' => $borrowDate,
            'jatuhTempo' => $dueDate,
            'status' => 'Dipinjam'
        ];

        // Add to peminjaman
        $data['peminjaman'][] = $loan;

        // Decrease Stock
        $data['books'][$bookIndex]['quantity']--;

        saveData($data);
        setFlashMessage('success', "Buku berhasil dipinjam! Batas Pengembalian: $dueDate");
        redirect('riwayat_pinjam.php');
    } else {
        setFlashMessage('error', 'Stok buku habis atau buku tidak valid.');
    }
}

$selectedBookId = $_GET['book_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pinjam Buku</title>
    <style>
        /* Styles from pinjam.html */
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        .navbar { background: #2c3e50; padding: 15px 40px; display: flex; justify-content: space-between; color: white; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .form-section { padding: 40px; text-align: center; }
        .main-container { display: flex; gap: 30px; max-width: 1000px; margin: auto; text-align: left; }
        .book-sidebar { flex: 1; background: #fff; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px; }
        .form-container { flex: 1; }
        .form-box { background: #fff; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #aaa; border-radius: 5px; }
        .btn { width: 100%; padding: 10px; margin-top: 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #1a252f; }
        .alert { color: white; padding: 10px; border-radius: 5px; margin-bottom: 10px; text-align: center; }
        .alert-error { background: #e74c3c; }
        .book-cover { width: 100%; max-height: 300px; object-fit: cover; margin-bottom: 15px; }
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
    <h2>Formulir Peminjaman Buku</h2>
    <?php if ($msg = getFlashMessage()): ?>
        <div class="alert alert-<?= $msg['type'] == 'success' ? 'success' : 'error' ?>" style="background: <?= $msg['type'] == 'success' ? '#27ae60' : '#e74c3c' ?>">
            <?= $msg['message'] ?>
        </div>
    <?php endif; ?>

    <div class="main-container">
        <!-- SIDEBAR -->
        <div class="book-sidebar">
            <h3>📚 Detail Buku</h3>
            <div id="book-details">
                <p style="color: #999; text-align: center;">Pilih buku untuk melihat detail</p>
            </div>
        </div>

        <!-- FORM -->
        <div class="form-container">
            <form class="form-box" method="POST">
                <label>Nama Peminjam</label>
                <input type="text" value="<?= htmlspecialchars($user['nama']) ?>" disabled>

                <label>Judul Buku</label>
                <select name="book_id" id="judul-buku" required onchange="updateBookDetails()">
                    <option value="">Pilih Buku</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= $book['id'] ?>" <?= $selectedBookId == $book['id'] ? 'selected' : '' ?> 
                                data-cover="<?= htmlspecialchars($book['cover']) ?>"
                                data-author="<?= htmlspecialchars($book['author']) ?>"
                                data-year="<?= htmlspecialchars($book['year']) ?>"
                                data-desc="<?= htmlspecialchars($book['description']) ?>"
                        >
                            <?= htmlspecialchars($book['title']) ?> (Stok: <?= $book['quantity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Tanggal Pinjam</label>
                <input type="date" name="borrow_date" required value="<?= date('Y-m-d') ?>">

                <button class="btn" type="submit">Pinjam Sekarang</button>
            </form>
        </div>
    </div>
</section>

<script>
    function updateBookDetails() {
        const select = document.getElementById('judul-buku');
        const option = select.options[select.selectedIndex];
        const detailsDiv = document.getElementById('book-details');

        if (!option.value) {
            detailsDiv.innerHTML = '<p style="color: #999; text-align: center;">Pilih buku untuk melihat detail</p>';
            return;
        }

        const cover = option.dataset.cover || 'https://via.placeholder.com/200x300';
        const title = option.text;
        const author = option.dataset.author;
        const year = option.dataset.year;
        const desc = option.dataset.desc;

        detailsDiv.innerHTML = `
            <img src="${cover}" class="book-cover">
            <p><strong>Judul:</strong> ${title}</p>
            <p><strong>Penulis:</strong> ${author}</p>
            <p><strong>Tahun:</strong> ${year}</p>
            <br>
            <p><strong>Deskripsi:</strong><br>${desc}</p>
        `;
    }

    // Auto trigger if pre-selected
    if (document.getElementById('judul-buku').value) {
        updateBookDetails();
    }
</script>

</body>
</html>

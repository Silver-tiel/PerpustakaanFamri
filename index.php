<?php
require 'functions.php';

$data = loadData();
$books = $data['books'];
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$flash = getFlashMessage();
$success = ($flash && $flash['type'] === 'success') ? $flash['message'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perpustakaan FAMRI - Dashboard</title>
    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }

        /* NAVBAR */
        .navbar {
            width: 100%;
            padding: 15px 40px;
            background: #2c3e50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .navbar .nav-links {
            display: flex;
            gap: 20px;
        }

        .navbar .nav-links li {
            list-style: none;
        }

        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .navbar .nav-links a:hover {
            text-decoration: underline;
        }

        /* NAV USER */
        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-login {
            display: inline-block;
            padding: 8px 15px;
            background: #667eea;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background: #764ba2;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            color: white;
            font-weight: bold;
        }

        .btn-logout {
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            text-decoration: none; /* Make it look like a button link */
        }

        .btn-logout:hover {
            background: #c0392b;
        }

        /* HERO */
        .hero {
            background: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f') center/cover no-repeat;
            height: 300px;
            text-align: center;
            color: white;
            padding-top: 100px;
            background-blend-mode: darken;
            background-color: rgba(0,0,0,0.4);
        }

        .hero h1 {
            font-size: 38px;
        }

        .hero p {
            margin-top: 10px;
            font-size: 18px;
        }

        /* BOOK LIST */
        .books-section {
            padding: 40px;
            text-align: center;
        }

        .book-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .book-card {
            width: 220px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .book-card img {
            width: 100%;
            height: 300px; /* Enforce height */
            object-fit: cover;
            border-radius: 6px;
        }

        .book-card h3 {
            margin: 10px 0 5px;
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin-top: 10px;
            background: #2c3e50;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn:hover {
            background: #1a252f;
        }

        /* FOOTER */
        footer {
            margin-top: 40px;
            padding: 15px;
            text-align: center;
            background: #2c3e50;
            color: white;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 20px 40px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">Perpustakaan FAMRI</div>
        <ul class="nav-links">
            <li><a href="index.php">Beranda</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="pinjam.php">Pinjam Buku</a></li>
                <li><a href="pengembalian.php">Pengembalian</a></li>
                <li><a href="riwayat_pinjam.php">Riwayat</a></li>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <li><a href="admin.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
        <div class="nav-user">
            <?php if ($user): ?>
                <div class="user-info">
                    <span class="user-name">👤 <?= htmlspecialchars($user['nama']) ?></span>
                    <a href="logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-login">Login/Registrasi</a>
            <?php endif; ?>
        </div>
    </nav>

    <?php if ($success): ?>
        <div class="alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php 
    if ($user) {
        $overdueItems = checkOverdueLoans($user, $data);
        if (!empty($overdueItems)): 
    ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px 40px; border-radius: 5px; text-align: center; border: 1px solid #f5c6cb;">
            <strong>⚠️ Perhatian!</strong> Anda memiliki <?= count($overdueItems) ?> buku yang melewati batas pengembalian. 
            <br>Segera kembalikan untuk menghindari denda bertambah.
        </div>
    <?php 
        endif; 
    }
    ?>

    <!-- HERO -->
    <section class="hero">
        <h1>Selamat Datang di Perpustakaan FAMRI</h1>
        <p>Temukan dan pinjam berbagai buku favoritmu</p>
    </section>

    <!-- LIST BUKU -->
    <section class="books-section">
        <h2>Daftar Buku</h2>
        
        <!-- SEARCH & FILTER -->
        <form method="GET" action="index.php" style="margin-bottom: 30px; display: flex; justify-content: center; gap: 10px;">
            <input type="text" name="q" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
            <select name="category" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                <option value="">Semua Kategori</option>
                <option value="Fiksi" <?= (isset($_GET['category']) && $_GET['category'] == 'Fiksi') ? 'selected' : '' ?>>Fiksi</option>
                <option value="Non-Fiksi" <?= (isset($_GET['category']) && $_GET['category'] == 'Non-Fiksi') ? 'selected' : '' ?>>Non-Fiksi</option>
                <option value="Sains" <?= (isset($_GET['category']) && $_GET['category'] == 'Sains') ? 'selected' : '' ?>>Sains</option>
                <option value="Sejarah" <?= (isset($_GET['category']) && $_GET['category'] == 'Sejarah') ? 'selected' : '' ?>>Sejarah</option>
                <option value="Teknologi" <?= (isset($_GET['category']) && $_GET['category'] == 'Teknologi') ? 'selected' : '' ?>>Teknologi</option>
                <option value="Lainnya" <?= (isset($_GET['category']) && $_GET['category'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
            </select>
            <button type="submit" class="btn" style="margin-top:0;">Cari</button>
            <?php if (!empty($_GET['q']) || !empty($_GET['category'])): ?>
                <a href="index.php" class="btn" style="margin-top:0; background: #e74c3c;">Reset</a>
            <?php endif; ?>
        </form>

        <?php
        // Filter Logic
        $filteredBooks = $books;
        if (!empty($_GET['q'])) {
            $q = strtolower($_GET['q']);
            $filteredBooks = array_filter($filteredBooks, function($b) use ($q) {
                return strpos(strtolower($b['title']), $q) !== false || strpos(strtolower($b['author']), $q) !== false;
            });
        }
        if (!empty($_GET['category'])) {
            $cat = $_GET['category'];
            $filteredBooks = array_filter($filteredBooks, function($b) use ($cat) {
                return isset($b['category']) && $b['category'] === $cat;
            });
        }
        ?>

        <div class="book-container">
            <?php if (empty($filteredBooks)): ?>
                <p>Tidak ada buku yang ditemukan</p>
            <?php else: ?>
                <?php foreach ($filteredBooks as $book): ?>
                    <div class="book-card">
                        <img src="<?= htmlspecialchars($book['cover'] ? $book['cover'] : 'https://via.placeholder.com/200x300') ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p><strong><?= htmlspecialchars($book['author']) ?></strong></p>
                        <p style="font-size: 12px; color: #7f8c8d;"><?= htmlspecialchars($book['year']) ?></p>
                        <p style="font-size: 13px; color: #555;"><?= htmlspecialchars(substr($book['description'], 0, 50)) ?>...</p>
                        <p style="font-size: 12px; color: #27ae60;"><strong>Stok: <?= (int)$book['quantity'] ?></strong></p>
                        
                        <?php if (isLoggedIn()): ?>
                             <a href="pinjam.php?book_id=<?= $book['id'] ?>" class="btn">Pinjam</a>
                        <?php else: ?>
                             <a href="login.php" class="btn">Login untuk Pinjam</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2025 Perpustakaan FAMRI. All rights reserved.</p>
    </footer>

</body>
</html>

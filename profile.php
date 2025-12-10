<?php
require 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = $_SESSION['user'];
$data = loadData();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua field harus diisi!";
    } else {
        // Update Session
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['password'] = $password;

        // Update Data
        foreach ($data['users'] as &$u) {
            if ($u['id'] == $user['id']) {
                $u['nama'] = $nama;
                $u['email'] = $email;
                $u['password'] = $password;
                break;
            }
        }
        
        // Also update any loans with new name if name changed (legacy support)
        if ($user['nama'] !== $nama) {
            foreach ($data['peminjaman'] as &$loan) {
                if ($loan['user_id'] == $user['id']) {
                    $loan['nama'] = $nama;
                }
            }
            foreach ($data['pengembalian'] as &$ret) {
                 if ($ret['user_id'] == $user['id']) {
                    $ret['nama'] = $nama;
                }
            }
        }

        saveData($data);
        $user = $_SESSION['user']; // Refresh local var
        $success = "Profil berhasil diperbarui!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }
        .navbar { background: #2c3e50; padding: 15px 40px; display: flex; justify-content: space-between; color: white; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 600px; margin: 40px auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn:hover { background: #5a6fd6; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Perpustakaan FAMRI</div>
    <div class="nav-links">
        <a href="dashboard.php">Beranda</a>
        <a href="pinjam.php">Pinjam Buku</a>
        <a href="profile.php" style="font-weight:bold; border-bottom: 2px solid white;">Profil</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>Edit Profil</h2>
        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <input type="text" value="<?= htmlspecialchars($user['status']) ?>" disabled style="background:#f9f9f9; color:#666;">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>" required>
            </div>
            <button type="submit" class="btn">Simpan Perubahan</button>
        </form>
    </div>
</div>

</body>
</html>

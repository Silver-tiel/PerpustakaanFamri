<?php
require 'functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($nama) || empty($email) || empty($status) || empty($password) || empty($confirm)) {
        $error = "❌ Semua data wajib diisi!";
    } elseif (strlen($nama) < 3) {
        $error = "❌ Nama minimal 3 karakter!";
    } elseif (strlen($password) < 6) {
        $error = "❌ Password minimal 6 karakter!";
    } elseif ($password !== $confirm) {
        $error = "❌ Konfirmasi password tidak cocok!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Format email tidak valid!";
    } else {
        $data = loadData();
        $users = $data['users'];
        
        $emailExist = false;
        $namaExist = false;

        foreach ($users as $user) {
            if ($user['email'] === $email) $emailExist = true;
            if ($user['nama'] === $nama) $namaExist = true;
        }

        if ($namaExist) {
            $error = "❌ Nama sudah terdaftar!";
        } elseif ($emailExist) {
            $error = "❌ Email sudah terdaftar!";
        } else {
            $newUser = [
                'id' => time() * 1000, // Approximate JS Date.now()
                'nama' => $nama,
                'email' => $email,
                'status' => $status,
                'password' => $password, // In production, hash this! keeping plain as per prototype
                'isAdmin' => false,
                'tanggalDaftar' => date('Y-m-d')
            ];
            
            $data['users'][] = $newUser;
            saveData($data);
            
            setFlashMessage('success', "✅ Registrasi Berhasil! Silahkan login.");
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registrasi</title>
    <style>
        /* GLOBAL */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background: #ffffff;
        }

        .wrapper {
            width: 100%;
            height: 100vh;
            display: flex;
        }

        /* LEFT PANEL */
        .left-panel {
            width: 40%;
            background: linear-gradient(135deg, #0A3D62, #3CAEA3);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            animation: slideLeft 0.8s ease;
        }

        .left-panel h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .left-panel p {
            opacity: 0.9;
            font-size: 15px;
            margin-bottom: 30px;
        }

        /* RIGHT PANEL */
        .right-panel {
            width: 60%;
            background: white;
            padding: 60px 80px;
            position: relative;
            animation: fadeIn 1s ease;
            overflow-y: auto;
        }

        /* FORM BOXES */
        .form-box {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        h2 {
            color: #0A3D62;
        }

        /* INPUTS */
        .form-group {
            margin-bottom: 15px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #cfd9e3;
            background: #f3f9ff;
            outline: none;
            transition: 0.2s;
        }

        input:focus,
        select:focus {
            border-color: #3CAEA3;
        }

        /* BUTTON */
        .btn {
            width: 100%;
            padding: 12px;
            background: #0A3D62;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        /* ERROR TEXT */
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin: 10px 0;
            padding: 10px;
            background: #fdeaea;
            border-radius: 5px;
            display: block;
        }

        /* TOGGLE TEXT */
        .toggle-text {
            margin-top: 15px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .wrapper {
                flex-direction: column;
            }

            .left-panel, .right-panel {
                width: 100%;
                text-align: center;
            }

            .right-panel {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="logo"><a href="index.php" style="color:white; text-decoration:none;">Perpustakaan FAMRI</a></div>
        <p>Untuk tetap terhubung dengan kami, silakan daftar untuk membuat akun baru.</p>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <!-- REGISTER -->
        <div id="register-box" class="form-box">
            <h2>Buat Akun Baru</h2>
            
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="nama" placeholder="Nama Pengguna" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <select name="status" required>
                        <option value="" disabled <?= !isset($_POST['status']) ? 'selected' : '' ?>>Pilih Status...</option>
                        <option value="Mahasiswa" <?= (isset($_POST['status']) && $_POST['status'] === 'Mahasiswa') ? 'selected' : '' ?>>Mahasiswa</option>
                        <option value="Masyarakat" <?= (isset($_POST['status']) && $_POST['status'] === 'Masyarakat') ? 'selected' : '' ?>>Masyarakat</option>
                        <option value="Pelajar" <?= (isset($_POST['status']) && $_POST['status'] === 'Pelajar') ? 'selected' : '' ?>>Pelajar</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Password (min. 6 karakter)" required>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm" placeholder="Konfirmasi Password" required>
                </div>

                <button type="submit" class="btn">Daftar</button>
            </form>

            <p class="toggle-text">Sudah punya akun? <a href="login.php" style="cursor: pointer; color: #667eea; font-weight: bold; text-decoration: none;">Masuk di sini</a></p>
        </div>
    </div>
</div>

</body>
</html>

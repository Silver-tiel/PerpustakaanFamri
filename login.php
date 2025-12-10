<?php
require 'functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $password = $_POST['password'];

    if (empty($nama) || empty($password)) {
        $error = "❌ Nama dan password wajib diisi!";
    } else {
        $data = loadData();
        $users = $data['users'];
        $found = null;

        foreach ($users as $user) {
            if ($user['nama'] === $nama && $user['password'] === $password) {
                $found = $user;
                break;
            }
        }

        if ($found) {
            $_SESSION['user'] = $found;
            setFlashMessage('success', "✅ Login berhasil! Selamat datang " . $found['nama']);
            redirect('dashboard.php');
        } else {
            $error = "❌ Nama atau password salah!";
            // Optional: Check if user exists for specific error message similar to JS version
            foreach ($users as $user) {
                if ($user['nama'] === $nama) {
                    $error = "❌ Password salah!";
                    break;
                }
            }
        }
    }
}

$flash = getFlashMessage();
if ($flash) {
    if ($flash['type'] === 'success') $success = $flash['message'];
    else $error = $flash['message'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:"Poppins", sans-serif;
        }

        body {
            background: linear-gradient(135deg, #E3FDFD, #ffffff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
        .error, .success {
            font-size: 14px;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            display: block;
        }

        .error {
            color: #e74c3c;
            background: #fdeaea;
        }

        .success {
            color: #27ae60;
            background: #d4edda;
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
        <h1>Selamat Datang Di Perpustakaan FAMRI</h1>
        <p>Untuk tetap terhubung dengan kami, silakan masuk menggunakan informasi pribadi Anda.</p>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <!-- LOGIN -->
        <div id="login-box" class="form-box">
            <h2>Masuk</h2>
            
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="nama" placeholder="Nama Pengguna" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                </div>

                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn">MASUK</button>
            </form>

            <p class="toggle-text">Belum punya akun? <a href="regist.php" style="cursor: pointer; color: #667eea; font-weight: bold; text-decoration: none;">Daftar di sini</a></p>
        </div>
    </div>
</div>

</body>
</html>

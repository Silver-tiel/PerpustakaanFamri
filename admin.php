<?php
require 'functions.php';

if (!isAdmin()) {
    // Show access denied or redirect
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <title>Akses Ditolak</title>
        <style>
            body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; font-family: sans-serif; }
            .content { text-align: center; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            h1 { color: #e74c3c; }
            a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="content">
            <h1>⛔ Akses Ditolak</h1>
            <p>Hanya admin yang dapat mengakses halaman ini.</p>
            <a href="index.php">Kembali ke Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$data = loadData();
$books = $data['books'];
$editBook = null;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $year = (int)$_POST['year'];
        $quantity = (int)$_POST['quantity'];
        $description = $_POST['description'];
        $coverPath = $_POST['existing_cover'] ?? ''; // Default to existing if available

        // Handle File Upload
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['cover']['tmp_name'];
            $fileName = $_FILES['cover']['name'];
            $fileSize = $_FILES['cover']['size'];
            $fileType = $_FILES['cover']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'heic', 'webp');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Generate unique name
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = 'foto buku/';
                $dest_path = $uploadFileDir . $newFileName;

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $coverPath = $dest_path;
                } else {
                    setFlashMessage('error', 'Gagal mengupload gambar, silakan coba lagi.');
                    // If add, might want to stop? For now proceed with empty cover or handle error
                }
            } else {
                 setFlashMessage('error', 'Format file tidak didukung. Gunakan JPG, PNG, HEIC, GIF, atau WEBP.');
            }
        }
        
        $newBook = [
            'id' => ($action === 'update') ? (int)$_POST['id'] : time(),
            'title' => $title,
            'author' => $author,
            'year' => $year,
            'quantity' => $quantity,
            'description' => $description,
            'cover' => $coverPath,
            // Preserve existing fields if update, or defaults
            'isbn' => $_POST['isbn'] ?? '-',
            'publisher' => $_POST['publisher'] ?? '-',
            'category' => $_POST['category'] ?? 'Umum'
        ];

        if ($action === 'add') {
            $data['books'][] = $newBook;
            setFlashMessage('success', 'Buku berhasil ditambahkan!');
        } else {
            foreach ($data['books'] as &$book) {
                if ($book['id'] == $newBook['id']) {
                    $book = array_merge($book, $newBook);
                    break;
                }
            }
            setFlashMessage('success', 'Buku berhasil diperbarui!');
        }
        saveData($data);
        redirect('admin.php?tab=view-books');
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $data['books'] = array_filter($data['books'], function($b) use ($id) {
            return $b['id'] != $id;
        });
        // Re-index array
        $data['books'] = array_values($data['books']);
        saveData($data);
        setFlashMessage('success', 'Buku berhasil dihapus!');
        redirect('admin.php?tab=view-books');
    } elseif ($action === 'delete_user') {
        $id = $_POST['id']; // User ID can be string or int
        $data['users'] = array_filter($data['users'], function($u) use ($id) {
            return $u['id'] != $id;
        });
        $data['users'] = array_values($data['users']);
        saveData($data);
        setFlashMessage('success', 'Anggota berhasil dihapus!');
        redirect('admin.php?tab=view-members');
    } elseif ($action === 'update_user') {
        $id = $_POST['id'];
        foreach ($data['users'] as &$u) {
            if ($u['id'] == $id) {
                $u['nama'] = $_POST['nama'];
                $u['email'] = $_POST['email'];
                $u['status'] = $_POST['status'];
                break;
            }
        }
        saveData($data);
        setFlashMessage('success', 'Data anggota berhasil diperbarui!');
        redirect('admin.php?tab=view-members');
    }
}

// Handle details view or edit prefill
if (isset($_GET['edit_id'])) {
    $id = (int)$_GET['edit_id'];
    foreach ($books as $b) {
        if ($b['id'] == $id) {
            $editBook = $b;
            break;
        }
    }
}

$activeTab = $_GET['tab'] ?? 'dashboard';
if ($editBook) $activeTab = 'add-book';

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - Perpustakaan FAMRI</title>
    <style>
        /* Copied and adapted styles from admin.html */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f0f2f5; color: #2c3e50; }
        .navbar { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .admin-container { display: flex; min-height: calc(100vh - 70px); }
        .sidebar { width: 260px; background: #2c3e50; color: white; padding-top: 20px; }
        .menu-btn { display: block; width: 100%; padding: 15px 20px; background: none; border: none; color: #bdc3c7; text-align: left; cursor: pointer; font-size: 16px; }
        .menu-btn:hover, .menu-btn.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid #667eea; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-primary { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-danger { background: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-edit { background: #3498db; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; background: #d4edda; color: #155724; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .stat-card p { font-size: 36px; font-weight: bold; color: #2c3e50; margin-top: 10px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">Perpustakaan FAMRI Admin</div>
    <div class="nav-links">
        <a href="index.php">Beranda</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="admin-container">
    <aside class="sidebar">
        <button class="menu-btn <?= $activeTab==='dashboard'?'active':'' ?>" onclick="switchTab('dashboard')">📊 Dashboard</button>
        <button class="menu-btn <?= $activeTab==='add-book'?'active':'' ?>" onclick="switchTab('add-book')">➕ <?= $editBook ? 'Edit Buku' : 'Tambah Buku' ?></button>
        <button class="menu-btn <?= $activeTab==='view-books'?'active':'' ?>" onclick="switchTab('view-books')">📚 Lihat Buku</button>
        <button class="menu-btn <?= $activeTab==='view-members'?'active':'' ?>" onclick="switchTab('view-members')">👥 Lihat Anggota</button>
    </aside>

    <main class="main-content">
        <?php if ($flash): ?>
            <div class="alert"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <!-- DASHBOARD -->
        <div id="dashboard" class="tab-content <?= $activeTab==='dashboard'?'active':'' ?>">
            <h1>Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Buku</h3>
                    <p><?= count($books) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total User</h3>
                    <p><?= count($data['users']) ?></p>
                </div>
            </div>
        </div>

        <!-- ADD/EDIT BOOK -->
        <div id="add-book" class="tab-content <?= $activeTab==='add-book'?'active':'' ?>">
            <h1><?= $editBook ? 'Edit Buku' : 'Tambah Buku Baru' ?></h1>
            <form method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $editBook ? 'update' : 'add' ?>">
                <?php if ($editBook): ?>
                    <input type="hidden" name="id" value="<?= $editBook['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Judul Buku</label>
                    <input type="text" name="title" required value="<?= $editBook['title'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Penulis</label>
                    <input type="text" name="author" required value="<?= $editBook['author'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Tahun Terbit</label>
                    <input type="number" name="year" required value="<?= $editBook['year'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Stok (Jumlah)</label>
                    <input type="number" name="quantity" required value="<?= $editBook['quantity'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="4" required><?= $editBook['description'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <label>Cover Buku (Wajib Upload Foto)</label>
                    <?php if ($editBook && !empty($editBook['cover'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= htmlspecialchars($editBook['cover']) ?>" alt="Cover Saat Ini" style="height: 100px; border-radius: 5px;">
                            <p style="font-size: 0.8em; color: #666;">Cover saat ini (biarkan kosong jika tidak ingin mengubah)</p>
                            <input type="hidden" name="existing_cover" value="<?= htmlspecialchars($editBook['cover']) ?>">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="cover" accept="image/*" <?= $editBook ? '' : 'required' ?>>
                </div>

                <button type="submit" class="btn-primary"><?= $editBook ? 'Simpan Perubahan' : 'Tambah Buku' ?></button>
                <?php if ($editBook): ?>
                    <a href="admin.php?tab=view-books" class="btn-danger" style="text-decoration:none; background:#95a5a6;">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- VIEW BOOKS -->
        <div id="view-books" class="tab-content <?= $activeTab==='view-books'?'active':'' ?>">
            <h1>Daftar Buku</h1>
            
            <!-- SEARCH & FILTER -->
            <form method="GET" action="admin.php" style="margin-bottom: 20px; display: flex; gap: 10px;">
                <input type="hidden" name="tab" value="view-books">
                <input type="text" name="q" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex: 1;">
                <select name="category" style="width: 150px; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="">Semua Kategori</option>
                    <option value="Fiksi" <?= (isset($_GET['category']) && $_GET['category'] == 'Fiksi') ? 'selected' : '' ?>>Fiksi</option>
                    <option value="Non-Fiksi" <?= (isset($_GET['category']) && $_GET['category'] == 'Non-Fiksi') ? 'selected' : '' ?>>Non-Fiksi</option>
                    <option value="Sains" <?= (isset($_GET['category']) && $_GET['category'] == 'Sains') ? 'selected' : '' ?>>Sains</option>
                    <option value="Sejarah" <?= (isset($_GET['category']) && $_GET['category'] == 'Sejarah') ? 'selected' : '' ?>>Sejarah</option>
                    <option value="Teknologi" <?= (isset($_GET['category']) && $_GET['category'] == 'Teknologi') ? 'selected' : '' ?>>Teknologi</option>
                    <option value="Lainnya" <?= (isset($_GET['category']) && $_GET['category'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                </select>
                <button type="submit" class="btn-primary">Cari</button>
                <?php if (!empty($_GET['q']) || !empty($_GET['category'])): ?>
                    <a href="admin.php?tab=view-books" class="btn-danger" style="text-decoration:none; padding-top:10px;">Reset</a>
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

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($filteredBooks)): ?>
                        <tr><td colspan="6" style="text-align:center;">Tidak ada buku ditemukan</td></tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($filteredBooks as $book): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['category'] ?? '-') ?></td>
                            <td><?= $book['quantity'] ?></td>
                            <td>
                                <a href="admin.php?edit_id=<?= $book['id'] ?>&tab=add-book" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus buku ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- VIEW MEMBERS -->
        <div id="view-members" class="tab-content <?= $activeTab==='view-members'?'active':'' ?>">
            <h1>Daftar Anggota</h1>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($data['users'] as $u): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <form method="POST" action="admin.php" style="display:flex; gap:5px;">
                                <input type="hidden" name="action" value="update_user">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="text" name="nama" value="<?= htmlspecialchars($u['nama']) ?>" required style="padding:5px; width: 100px;">
                        </td>
                        <td>
                                <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required style="padding:5px; width: 150px;">
                        </td>
                        <td>
                                <select name="status" style="padding:5px;">
                                    <option value="Administrator" <?= $u['status'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                                    <option value="Mahasiswa" <?= $u['status'] === 'Mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                                    <option value="Dosen" <?= $u['status'] === 'Dosen' ? 'selected' : '' ?>>Dosen</option>
                                    <option value="Masyarakat" <?= $u['status'] === 'Masyarakat' ? 'selected' : '' ?>>Masyarakat</option>
                                    <option value="Pelajar" <?= $u['status'] === 'Pelajar' ? 'selected' : '' ?>>Pelajar</option>
                                </select>
                        </td>
                        <td><?= $u['tanggalDaftar'] ?></td>
                        <td>
                                <button type="submit" class="btn-edit" style="border:none; cursor:pointer;">Simpan</button>
                            </form>
                            <?php if (!isset($u['isAdmin']) || !$u['isAdmin']): ?>
                            <form method="POST" style="display:inline; margin-top:5px;" onsubmit="return confirm('Yakin hapus user ini?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    function switchTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.menu-btn').forEach(el => el.classList.remove('active'));
        
        // Show target tab
        document.getElementById(tabId).classList.add('active');
        
        // Highlight menu
        // Simple iteration to find the button with matching onclick or just reload page with param if complex
        // Here we just use JS toggle for smoother feel, but forms reload page.
        // Update URL to keep state on refresh
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        // Remove edit_id if switching away from add-book to avoid getting stuck in edit mode logic visually?
        // Actually PHP controls the initial render.
        window.history.pushState({}, '', url);
        
        // Find button
        const buttons = document.querySelectorAll('.menu-btn');
        buttons.forEach(btn => {
            if (btn.getAttribute('onclick').includes(tabId)) {
                btn.classList.add('active');
            }
        });
    }
</script>

</body>
</html>

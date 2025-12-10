================================================================================
                        PANDUAN AKUN ADMIN
================================================================================

AKUN ADMIN DEFAULT
==================
Ketika pertama kali membuka halaman Registrasi (regist.html), sistem akan 
otomatis membuat akun admin dengan detail berikut:

Username : admin
Email    : admin@perpustakaan.com
Password : admin123
Status   : Admin

CARA MENGGUNAKAN ADMIN PANEL
=============================
1. Buka halaman Login (login.html)
2. Masukkan username "admin" dan password "admin123"
3. Klik tombol "MASUK"
4. Sistem akan memvalidasi akun Anda
5. Jika berhasil login sebagai admin, Anda akan diarahkan ke Dashboard
6. Buka menu Admin Panel untuk mengakses fitur administrasi

FITUR ADMIN PANEL
=================
1. Dashboard
   - Menampilkan total buku yang ada di sistem
   
2. Tambah Buku
   - Form untuk menambahkan buku baru ke sistem
   - Fields: Judul, Penulis, ISBN, Penerbit, Tahun, Kategori, Stok, Gambar, Deskripsi
   
3. Lihat Buku
   - Menampilkan daftar semua buku dalam bentuk tabel
   - Fitur pencarian untuk mencari buku
   - Tombol Edit untuk mengubah data buku
   - Tombol Hapus untuk menghapus buku

KEAMANAN
=========
- Hanya akun yang memiliki property isAdmin = true yang dapat mengakses Admin Panel
- Jika akun bukan admin mencoba membuka admin.html, akan ditampilkan halaman "Akses Ditolak"
- Validasi dilakukan setiap kali halaman admin diakses
- Jika user logout, akses ke admin panel akan ditolak

MEMBUAT AKUN ADMIN TAMBAHAN
============================
Saat ini sistem hanya menyediakan 1 akun admin default. 
Untuk menambah admin baru, Anda perlu:

1. Buka Browser Console (F12 > Console)
2. Jalankan kode berikut:

let users = JSON.parse(localStorage.getItem("users")) || [];
users.push({
    id: Date.now(),
    nama: 'admin2',
    email: 'admin2@perpustakaan.com',
    status: 'Admin',
    password: 'admin123',
    isAdmin: true,
    tanggalDaftar: new Date().toLocaleDateString('id-ID')
});
localStorage.setItem("users", JSON.stringify(users));

Ganti 'admin2' dengan nama admin yang diinginkan.

PERINGATAN PENTING
==================
- Jangan hapus atau ubah akun admin default tanpa backup
- Pastikan ada minimal 1 akun admin yang masih aktif
- Jika lupa password admin, data tidak dapat dipulihkan (harus reset localStorage)

================================================================================

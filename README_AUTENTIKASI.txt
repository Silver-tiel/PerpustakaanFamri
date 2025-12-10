/**
 * SISTEM AUTENTIKASI PERPUSTAKAAN DIGITAL
 * 
 * PENJELASAN:
 * 1. DATA PENYIMPANAN:
 *    - localStorage: Menyimpan data user terdaftar (permanent)
 *    - sessionStorage: Menyimpan data user yang sedang login (sementara, hilang saat refresh/close)
 * 
 * 2. ALUR REGISTRASI:
 *    - User masuk ke regist.html
 *    - Klik tab "Buat Akun Baru"
 *    - Isi form: Nama, Email, Status, Password
 *    - Klik "Daftar"
 *    - Data disimpan ke localStorage
 *    - Tampilan berubah ke form login
 * 
 * 3. ALUR LOGIN:
 *    - User masuk ke regist.html (tampil form login by default)
 *    - Isi Nama & Password
 *    - Klik "MASUK"
 *    - Sistem cek di localStorage, jika cocok:
 *      * Data user disimpan ke sessionStorage
 *      * Redirect ke index.html
 *      * Navbar menampilkan nama user & tombol logout
 * 
 * 4. FILE-FILE PENTING:
 *    - auth.js: Mengelola status login/logout di navbar
 *    - login.js: Fungsi login dan validasi
 *    - regis.js: Fungsi registrasi dan validasi
 *    - index.js: Fungsi tambahan di halaman utama
 * 
 * 5. FITUR KEAMANAN:
 *    - Validasi email format
 *    - Password minimal 6 karakter
 *    - Nama minimal 3 karakter
 *    - Cek duplikasi nama dan email
 *    - Konfirmasi password harus sama
 * 
 * 6. TEST DATA (Sudah ada di localStorage):
 *    - Tidak ada, user harus registrasi dulu
 *    - Atau bisa tambah manual di browser console
 * 
 * TESTING:
 * 1. Buka regist.html
 * 2. Klik "Buat Akun Baru"
 * 3. Isi: Nama: "test", Email: "test@test.com", Status: "Mahasiswa", 
 *    Password: "123456", Konfirmasi: "123456"
 * 4. Klik Daftar
 * 5. Form berubah ke Login
 * 6. Isi Nama: "test", Password: "123456"
 * 7. Klik MASUK
 * 8. Redirect ke index.html
 * 9. Navbar menampilkan "👤 test" dan "Logout"
 */

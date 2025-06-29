<?php
// File: api/middleware/auth_admin.php
// Middleware untuk memeriksa apakah admin sudah login.

// Mulai sesi PHP untuk mengakses variabel $_SESSION.
// @ supresses warning if session is already started.
@session_start();

// Cek apakah 'admin_id' ada di dalam sesi.
// Jika tidak ada, artinya admin belum login.
if (!isset($_SESSION['admin_id'])) {
    // Kirim response error 401 Unauthorized.
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Anda harus login sebagai admin.']);
    // Hentikan eksekusi skrip lebih lanjut.
    exit();
}

// Jika lolos, skrip yang memanggil middleware ini akan melanjutkan eksekusinya.
?>

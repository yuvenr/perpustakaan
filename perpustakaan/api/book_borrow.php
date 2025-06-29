<?php
// File: api/book_borrow.php
// Endpoint untuk anggota meminjam buku.

require_once __DIR__ . '/../config/database.php';
// Middleware: Memastikan hanya anggota yang sudah login (dengan JWT valid) bisa mengakses.
$member_data = require_once __DIR__ . '/middleware/auth_member.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']));
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->book_id)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Book ID harus diisi.']));
}

$book_id = (int)$data->book_id;
$user_id = (int)$member_data['id']; // Ambil ID pengguna dari token JWT

$conn = get_db_connection();
if(!$conn) { exit(); }

// Gunakan transaksi untuk menjaga integritas data
$conn->begin_transaction();

try {
    // 1. Kunci baris buku untuk mencegah race condition (dua orang meminjam buku yang sama)
    $stmt = $conn->prepare("SELECT status FROM books WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Buku tidak ditemukan.");
    }

    $book = $result->fetch_assoc();
    if ($book['status'] !== 'available') {
        throw new Exception("Buku tidak tersedia atau sudah dipinjam.");
    }

    // 2. Ubah status buku menjadi 'borrowed'
    $stmt_update = $conn->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
    $stmt_update->bind_param("i", $book_id);
    $stmt_update->execute();

    // 3. Catat transaksi peminjaman
    $due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // Tentukan durasi peminjaman (7 hari)
    $stmt_insert = $conn->prepare("INSERT INTO borrowing_records (book_id, user_id, due_date) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $book_id, $user_id, $due_date);
    $stmt_insert->execute();

    // Jika semua berhasil, commit transaksi
    $conn->commit();

    http_response_code(201); // Created
    echo json_encode(['status' => 'success', 'message' => 'Buku berhasil dipinjam. Harap kembalikan sebelum ' . $due_date]);

} catch (Exception $e) {
    // Jika terjadi error, batalkan semua perubahan
    $conn->rollback();
    http_response_code(409); // Conflict
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>

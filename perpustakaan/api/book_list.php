<?php
// File: api/book_list.php
// Endpoint untuk anggota melihat daftar semua buku.

require_once __DIR__ . '/../config/database.php';
// Middleware: Memastikan hanya anggota yang sudah login (dengan JWT valid) bisa mengakses.
$member_data = require_once __DIR__ . '/middleware/auth_member.php';

$conn = get_db_connection();
if(!$conn) { exit(); }

$result = $conn->query("SELECT id, title, author, publication_year, status FROM books ORDER BY title ASC");

$books = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $books]);

$conn->close();
?>

<?php
// File: /proyek_perpus/admin_panel/dashboard.php (Versi Refactor)
require_once __DIR__ . '/../api/middleware/auth_admin.php'; // Proteksi halaman tetap di sini
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        button { cursor: pointer; }
        #message { margin-top: 15px; padding: 10px; border-radius: 5px; display: none; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! | <a href="logout.php">Logout</a></p>
    <hr>
    <h2>Pendaftar yang Menunggu Persetujuan</h2>
    <div id="message"></div>
    <table>
        <thead>
            <tr>
                <th>Nama Lengkap</th>
                <th>NIM</th>
                <th>Email</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="pendingUsersTableBody">
            </tbody>
    </table>

    <script>
        const messageDiv = document.getElementById('message');

        // Fungsi untuk mengambil dan menampilkan pendaftar
        async function fetchPendingUsers() {
            try {
                const response = await fetch('../api/admin_get_pending.php');
                const result = await response.json();
                const tableBody = document.getElementById('pendingUsersTableBody');
                tableBody.innerHTML = ''; // Kosongkan tabel

                if (response.ok && result.status === 'success') {
                    if (result.data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5">Tidak ada pendaftar baru.</td></tr>`;
                    } else {
                        result.data.forEach(user => {
                            const row = `<tr>
                                <td>${user.full_name}</td>
                                <td>${user.nim}</td>
                                <td>${user.email}</td>
                                <td>${user.registration_date}</td>
                                <td>
                                    <button onclick="verifyUser(${user.id}, 'approve')" style="color:green;">Setujui</button>
                                    <button onclick="verifyUser(${user.id}, 'reject')" style="color:red;">Tolak</button>
                                </td>
                            </tr>`;
                            tableBody.innerHTML += row;
                        });
                    }
                } else {
                    tableBody.innerHTML = `<tr><td colspan="5">${result.message || 'Gagal memuat data.'}</td></tr>`;
                }
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="5">Gagal menghubungi server.</td></tr>`;
            }
        }

        // Fungsi untuk verifikasi (approve/reject)
        async function verifyUser(userId, action) {
            if (!confirm(`Apakah Anda yakin ingin "${action}" pengguna ini?`)) return;

            try {
                const response = await fetch('../api/admin_verify_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, action: action })
                });
                const result = await response.json();
                
                messageDiv.className = response.ok ? 'success' : 'error';
                messageDiv.textContent = result.message;
                messageDiv.style.display = 'block';

                fetchPendingUsers(); // Muat ulang data setelah aksi
            } catch (error) {
                messageDiv.className = 'error';
                messageDiv.textContent = 'Gagal menghubungi server untuk verifikasi.';
                messageDiv.style.display = 'block';
            }
        }
        
        // Panggil fungsi saat halaman dimuat
        fetchPendingUsers();
    </script>
</body>
</html>
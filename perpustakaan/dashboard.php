<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        button { cursor: pointer; }
        #userInfo { margin-bottom: 20px; }
        #message { margin-top: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div id="userInfo"></div>
    <button id="logoutButton">Logout</button>
    <hr>
    
    <h1>Daftar Buku Perpustakaan</h1>
    <div id="message"></div>
    <table id="bookTable">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Tahun Terbit</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            </tbody>
    </table>

    <script>
        const token = localStorage.getItem('member_token');
        const userInfoDiv = document.getElementById('userInfo');
        const messageDiv = document.getElementById('message');

        // Fungsi untuk mem-parse data dari token JWT
        function parseJwt(token) {
            try {
                return JSON.parse(atob(token.split('.')[1]));
            } catch (e) {
                return null;
            }
        }

        // Cek jika tidak ada token, tendang kembali ke halaman login
        if (!token) {
            window.location.href = 'login.html';
        } else {
            const userData = parseJwt(token).data;
            userInfoDiv.innerHTML = `Selamat datang, <strong>${userData.full_name}</strong> (NIM: ${userData.nim})!`;
        }
        
        // Fungsi untuk mengambil dan menampilkan daftar buku
        async function fetchBooks() {
            try {
                const response = await fetch('api/book_list.php', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const result = await response.json();
                const tableBody = document.querySelector("#bookTable tbody");
                tableBody.innerHTML = ''; // Kosongkan tabel sebelum diisi

                if (response.ok) {
                    result.data.forEach(book => {
                        const row = `<tr>
                            <td>${book.title}</td>
                            <td>${book.author}</td>
                            <td>${book.publication_year}</td>
                            <td>${book.status}</td>
                            <td>
                                ${book.status === 'available' ? `<button onclick="borrowBook(${book.id})">Pinjam</button>` : 'Tidak Tersedia'}
                            </td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                } else {
                     tableBody.innerHTML = `<tr><td colspan="5">${result.message}</td></tr>`;
                }
            } catch (error) {
                 document.querySelector("#bookTable tbody").innerHTML = `<tr><td colspan="5">Gagal memuat data buku.</td></tr>`;
            }
        }

        // Fungsi untuk meminjam buku
        async function borrowBook(bookId) {
            if (!confirm('Apakah Anda yakin ingin meminjam buku ini?')) return;

            try {
                const response = await fetch('api/book_borrow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ book_id: bookId })
                });
                const result = await response.json();
                if(response.ok) {
                    messageDiv.className = 'success';
                } else {
                    messageDiv.className = 'error';
                }
                messageDiv.textContent = result.message;
                fetchBooks(); // Muat ulang daftar buku untuk melihat status terbaru
            } catch (error) {
                messageDiv.className = 'error';
                messageDiv.textContent = 'Gagal menghubungi server untuk meminjam buku.';
            }
        }

        // Event listener untuk tombol logout
        document.getElementById('logoutButton').addEventListener('click', () => {
            localStorage.removeItem('member_token');
            window.location.href = 'login.html';
        });

        // Panggil fungsi untuk memuat buku saat halaman dibuka
        fetchBooks();
    </script>
</body>
</html>

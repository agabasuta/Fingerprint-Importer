<?php

// Isi sesuai user dan database
$host = "localhost";
$user = "";     
$pass = "";         
$db   = "attendance_db";  

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil parameter pencarian
$searchNama = isset($_GET['nama']) ? $_GET['nama'] : "";
$searchDate = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Query filter
$where = [];
if (!empty($searchNama)) {
    $where[] = "nama LIKE '%" . $conn->real_escape_string($searchNama) . "%'";
}
if (!empty($searchDate)) {
    $where[] = "DATE(tgl_waktu) = '" . $conn->real_escape_string($searchDate) . "'";
}
$whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Hitung total data
$totalResult = $conn->query("SELECT COUNT(*) as total FROM attendance_logs $whereSql");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Ambil data absensi
$sql = "SELECT * FROM attendance_logs $whereSql ORDER BY tgl_waktu DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Absensi</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #e8f5e9; 
            margin: 0; padding: 0; 
        }
        .container { 
            width: 95%; 
            margin: auto; 
            padding: 20px; 
        }
        h2 { 
            text-align: center; 
            color: #2e7d32;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="date"] {
            padding: 7px; 
            border: 1px solid #bbb; 
            border-radius: 5px;
        }
        button {
            padding: 7px 14px;
            background: #4CAF50;
            color: #fff;
            border: none; 
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover { background: #45a049; }
        .export-btn {
            display: inline-block;
            margin-bottom: 10px;
            padding: 8px 14px;
            background: #43a047;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            background: #fff;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: center; 
        }
        th { background: #a5d6a7; }
        tr:nth-child(even) { background: #f1f8e9; }
        .pagination { text-align: center; margin-top: 10px; }
        .pagination a, .pagination span {
            margin: 0 3px;
            padding: 6px 12px;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            text-decoration: none;
            color: #2e7d32;
            background: #c8e6c9;
        }
        .pagination a.active {
            background: #2e7d32;
            color: white;
        }
        .pagination span {
            border: none;
            background: transparent;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Dashboard Absensi</h2>

    <!-- Form pencarian -->
    <form method="get" action="">
        <input type="text" name="nama" placeholder="Cari Nama" value="<?= htmlspecialchars($searchNama) ?>">
        <input type="date" name="tanggal" value="<?= htmlspecialchars($searchDate) ?>">
        <button type="submit">Cari</button>
    </form>

    <!-- Tombol export -->
    <div style="text-align:right;">
        <a class="export-btn" 
           href="export.php?nama=<?= urlencode($searchNama) ?>&tanggal=<?= urlencode($searchDate) ?>">
           ⬇ Download CSV
        </a>
    </div>

    <!-- Tabel absensi -->
    <table>
        <tr>
            <th>ID</th>
            <th>Departemen</th>
            <th>Nama</th>
            <th>No ID</th>
            <th>Tanggal & Waktu</th>
            <th>Status</th>
            <th>Lokasi</th>
            <th>No PIN</th>
            <th>No Kartu</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['departemen']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['no_id']) ?></td>
                    <td><?= $row['tgl_waktu'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= htmlspecialchars($row['lokasi_id']) ?></td>
                    <td><?= htmlspecialchars($row['no_pin']) ?></td>
                    <td><?= htmlspecialchars($row['no_kartu']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">Tidak ada data</td></tr>
        <?php endif; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php
        $adjacents = 2; 
        $lastpage = $totalPages;
        $lpm1 = $lastpage - 1;

        if ($lastpage > 1) {
            // Tombol pertama & prev
            if ($page > 1) {
                echo "<a href='?page=1&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>&laquo;&laquo;</a>";
                echo "<a href='?page=" . ($page-1) . "&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>&laquo;</a>";
            }

            // Jika jauh dari awal
            if ($page > ($adjacents + 2)) {
                echo "<a href='?page=1&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>1</a>";
                echo "<span>...</span>";
            }

            // Nomor sekitar halaman aktif
            for ($i = max(1, $page - $adjacents); $i <= min($lastpage, $page + $adjacents); $i++) {
                if ($i == $page) {
                    echo "<a class='active'>$i</a>";
                } else {
                    echo "<a href='?page=$i&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>$i</a>";
                }
            }

            // Jika jauh dari akhir
            if ($page < ($lastpage - $adjacents - 1)) {
                echo "<span>...</span>";
                echo "<a href='?page=$lastpage&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>$lastpage</a>";
            }

            // Tombol next & last
            if ($page < $lastpage) {
                echo "<a href='?page=" . ($page+1) . "&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>&raquo;</a>";
                echo "<a href='?page=$lastpage&nama=" . urlencode($searchNama) . "&tanggal=" . urlencode($searchDate) . "'>&raquo;&raquo;</a>";
            }
        }
        ?>
    </div>
</div>
</body>
</html>
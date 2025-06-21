<?php 
session_start();
include 'koneksi.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik & Laporan - Toko Buku Online</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .table-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.4rem;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        
        .sql-query {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #2c3e50;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
        }
        
        .highlight {
            background: #e8f5e8;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-selesai { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-dibatalkan { background: #f8d7da; color: #721c24; }
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .low-stock {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .alert-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
        }
        
        .alert-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <header>
        <h1>Statistik & Laporan</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="book.php">Daftar Buku</a>
            <a href="add_book.php">Tambah Buku</a>
            <a href="statistics.php">Statistik</a>
        </nav>
    </header>

    <main>
        <h2>📊 Dashboard Statistik Toko Buku</h2>
        
         <!-- Alert untuk stok rendah -->
        <?php
        $query_low_stock = "SELECT COUNT(*) as low_stock_count FROM buku WHERE stok < 10";
        $result_low_stock = $koneksi->query($query_low_stock);
        $low_stock_count = $result_low_stock->fetch_assoc()['low_stock_count'];
        
        if ($low_stock_count > 0) {
            echo "<div class='alert-box'>
                    <h4>⚠️ Peringatan Stok Rendah!</h4>
                    <p>Terdapat <strong>{$low_stock_count}</strong> buku dengan stok kurang dari 10 unit. Segera lakukan restock!</p>
                  </div>";
        }
        ?>

        <!-- Statistik Utama dengan Fungsi Agregat -->
        <div class="stats-grid">
            <?php
            // Query 1: COUNT - Total Buku
            $query1 = "SELECT COUNT(*) as total_buku FROM buku";
            $result1 = $koneksi->query($query1);
            $total_buku = $result1->fetch_assoc()['total_buku'];
            ?>
            <div class="stat-card">
                <h3>📚 Total Buku</h3>
                <div class="stat-value"><?= number_format($total_buku) ?></div>
                <div class="stat-label">Jumlah Buku Tersedia</div>
                <div class="sql-query">SQL: <span class="highlight">COUNT(*)</span> FROM buku</div>
            </div>

            <?php
            // Query 2: SUM - Total Stok
            $query2 = "SELECT SUM(stok) as total_stok FROM buku";
            $result2 = $koneksi->query($query2);
            $total_stok = $result2->fetch_assoc()['total_stok'];
            ?>
            <div class="stat-card">
                <h3>📦 Total Stok</h3>
                <div class="stat-value"><?= number_format($total_stok) ?></div>
                <div class="stat-label">Unit Buku Tersedia</div>
                <div class="sql-query">SQL: <span class="highlight">SUM(stok)</span> FROM buku</div>
            </div>

            <?php
            // Query 3: AVG - Harga Rata-rata
            $query3 = "SELECT AVG(harga) as rata_harga FROM buku";
            $result3 = $koneksi->query($query3);
            $rata_harga = $result3->fetch_assoc()['rata_harga'];
            ?>
            <div class="stat-card">
                <h3>💰 Harga Rata-rata</h3>
                <div class="stat-value">Rp <?= number_format($rata_harga, 0, ',', '.') ?></div>
                <div class="stat-label">Harga Rata-rata Buku</div>
                <div class="sql-query">SQL: <span class="highlight">AVG(harga)</span> FROM buku</div>
            </div>

            <?php
            // Query 4: MAX - Harga Termahal
            $query4 = "SELECT MAX(harga) as harga_max FROM buku";
            $result4 = $koneksi->query($query4);
            $harga_max = $result4->fetch_assoc()['harga_max'];
            ?>
            <div class="stat-card">
                <h3>🔥 Harga Termahal</h3>
                <div class="stat-value">Rp <?= number_format($harga_max, 0, ',', '.') ?></div>
                <div class="stat-label">Buku Termahal</div>
                <div class="sql-query">SQL: <span class="highlight">MAX(harga)</span> FROM buku</div>
            </div>

            <?php
            // Query 5: MIN - Harga Termurah
            $query5 = "SELECT MIN(harga) as harga_min FROM buku";
            $result5 = $koneksi->query($query5);
            $harga_min = $result5->fetch_assoc()['harga_min'];
            ?>
            <div class="stat-card">
                <h3>💸 Harga Termurah</h3>
                <div class="stat-value">Rp <?= number_format($harga_min, 0, ',', '.') ?></div>
                <div class="stat-label">Buku Termurah</div>
                <div class="sql-query">SQL: <span class="highlight">MIN(harga)</span> FROM buku</div>
            </div>

            <?php
            // Query 6: COUNT dengan GROUP BY - Total Kategori
            $query6 = "SELECT COUNT(DISTINCT id_kategori) as total_kategori FROM buku";
            $result6 = $koneksi->query($query6);
            $total_kategori = $result6->fetch_assoc()['total_kategori'];
            ?>
            <div class="stat-card">
                <h3>🏷️ Total Kategori</h3>
                <div class="stat-value"><?= number_format($total_kategori) ?></div>
                <div class="stat-label">Kategori Buku Aktif</div>
                <div class="sql-query">SQL: <span class="highlight">COUNT(DISTINCT id_kategori)</span> FROM buku</div>
            </div>
        </div>
        
            <?php
            // Query 7: COUNT untuk Total Transaksi
            $query7 = "SELECT COUNT(*) as total_transaksi FROM transaksi WHERE status_transaksi = 'selesai'";
            $result7 = $koneksi->query($query7);
            $total_transaksi = $result7->fetch_assoc()['total_transaksi'];
            ?>
            <div class="stat-card">
                <h3>💳 Total Transaksi</h3>
                <div class="stat-value"><?= number_format($total_transaksi) ?></div>
                <div class="stat-label">Transaksi Selesai</div>
                <div class="sql-query">SQL: <span class="highlight">COUNT(*)</span> FROM transaksi WHERE status = 'selesai'</div>
            </div>

            <?php
            // Query 8: SUM untuk Total Pendapatan
            $query8 = "SELECT SUM(total_harga) as total_pendapatan FROM transaksi WHERE status_transaksi = 'selesai'";
            $result8 = $koneksi->query($query8);
            $total_pendapatan = $result8->fetch_assoc()['total_pendapatan'] ?? 0;
            ?>
            <div class="stat-card">
                <h3>💰 Total Pendapatan</h3>
                <div class="stat-value">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                <div class="stat-label">Pendapatan Bersih</div>
                <div class="sql-query">SQL: <span class="highlight">SUM(total_harga)</span> FROM transaksi WHERE status = 'selesai'</div>
            </div>
        </div>

        <!-- Statistik per Kategori (GROUP BY + Fungsi Agregat) -->
        <div class="table-section">
            <h3 class="section-title">📊 Statistik per Kategori</h3>
            <div class="sql-query">
                SELECT k.nama_kategori, <span class="highlight">COUNT(b.id_buku)</span> as jumlah_buku, 
                <span class="highlight">SUM(b.stok)</span> as total_stok, <span class="highlight">AVG(b.harga)</span> as rata_harga,
                <span class="highlight">MAX(b.harga)</span> as harga_tertinggi, <span class="highlight">MIN(b.harga)</span> as harga_terendah
                FROM buku b <span class="highlight">INNER JOIN</span> kategori k ON b.id_kategori = k.id_kategori
                <span class="highlight">GROUP BY</span> k.id_kategori, k.nama_kategori
                <span class="highlight">ORDER BY</span> jumlah_buku DESC
            </div>
            
            <div class="table-container">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah Buku</th>
                            <th>Total Stok</th>
                            <th>Rata-rata Harga</th>
                            <th>Harga Tertinggi</th>
                            <th>Harga Terendah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_kategori = "
                            SELECT 
                                k.nama_kategori,
                                COUNT(b.id_buku) as jumlah_buku,
                                SUM(b.stok) as total_stok,
                                AVG(b.harga) as rata_harga,
                                MAX(b.harga) as harga_tertinggi,
                                MIN(b.harga) as harga_terendah
                            FROM buku b
                            INNER JOIN kategori k ON b.id_kategori = k.id_kategori
                            GROUP BY k.id_kategori, k.nama_kategori
                            ORDER BY jumlah_buku DESC
                        ";
                        
                        $result_kategori = $koneksi->query($query_kategori);
                        while ($row = $result_kategori->fetch_assoc()) {
                            echo "<tr>
                                <td><strong>" . htmlspecialchars($row['nama_kategori']) . "</strong></td>
                                <td>" . number_format($row['jumlah_buku']) . "</td>
                                <td>" . number_format($row['total_stok']) . "</td>
                                <td>Rp " . number_format($row['rata_harga'], 0, ',', '.') . "</td>
                                <td>Rp " . number_format($row['harga_tertinggi'], 0, ',', '.') . "</td>
                                <td>Rp " . number_format($row['harga_terendah'], 0, ',', '.') . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistik per Penerbit (Multiple JOIN) -->
        <div class="table-section">
            <h3 class="section-title">🏢 Statistik per Penerbit</h3>
            <div class="sql-query">
                SELECT p.nama_penerbit, <span class="highlight">COUNT(b.id_buku)</span> as jumlah_buku,
                <span class="highlight">SUM(b.stok)</span> as total_stok, <span class="highlight">AVG(b.harga)</span> as rata_harga
                FROM buku b <span class="highlight">INNER JOIN</span> penerbit p ON b.id_penerbit = p.id_penerbit
                <span class="highlight">GROUP BY</span> p.id_penerbit, p.nama_penerbit
                <span class="highlight">HAVING</span> COUNT(b.id_buku) > 0
                <span class="highlight">ORDER BY</span> jumlah_buku DESC
            </div>
            
            <div class="table-container">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Penerbit</th>
                            <th>Jumlah Buku</th>
                            <th>Total Stok</th>
                            <th>Rata-rata Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_penerbit = "
                            SELECT 
                                p.nama_penerbit,
                                COUNT(b.id_buku) as jumlah_buku,
                                SUM(b.stok) as total_stok,
                                AVG(b.harga) as rata_harga
                            FROM buku b
                            INNER JOIN penerbit p ON b.id_penerbit = p.id_penerbit
                            GROUP BY p.id_penerbit, p.nama_penerbit
                            HAVING COUNT(b.id_buku) > 0
                            ORDER BY jumlah_buku DESC
                        ";
                        
                        $result_penerbit = $koneksi->query($query_penerbit);
                        while ($row = $result_penerbit->fetch_assoc()) {
                            echo "<tr>
                                <td><strong>" . htmlspecialchars($row['nama_penerbit']) . "</strong></td>
                                <td>" . number_format($row['jumlah_buku']) . "</td>
                                <td>" . number_format($row['total_stok']) . "</td>
                                <td>Rp " . number_format($row['rata_harga'], 0, ',', '.') . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistik Transaksi per Status -->
        <div class="table-section">
            <h3 class="section-title">📈 Statistik Transaksi per Status</h3>
            <div class="sql-query">
                SELECT status_transaksi, <span class="highlight">COUNT(*)</span> as jumlah_transaksi,
                <span class="highlight">SUM(total_harga)</span> as total_nilai, <span class="highlight">AVG(total_harga)</span> as rata_rata_nilai
                FROM transaksi <span class="highlight">GROUP BY</span> status_transaksi
                <span class="highlight">ORDER BY</span> jumlah_transaksi DESC
            </div>
            
            <div class="table-container">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Nilai</th>
                            <th>Rata-rata Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_status = "
                            SELECT 
                                status_transaksi,
                                COUNT(*) as jumlah_transaksi,
                                SUM(total_harga) as total_nilai,
                                AVG(total_harga) as rata_rata_nilai
                            FROM transaksi
                            GROUP BY status_transaksi
                            ORDER BY jumlah_transaksi DESC
                        ";
                        
                        $result_status = $koneksi->query($query_status);
                        while ($row = $result_status->fetch_assoc()) {
                            $status_class = '';
                            switch($row['status_transaksi']) {
                                case 'selesai': $status_class = 'status-selesai'; break;
                                case 'pending': $status_class = 'status-pending'; break;
                                case 'dibatalkan': $status_class = 'status-dibatalkan'; break;
                            }
                            
                            echo "<tr>
                                <td><span class='status-badge {$status_class}'>" . ucfirst($row['status_transaksi']) . "</span></td>
                                <td>" . number_format($row['jumlah_transaksi']) . "</td>
                                <td>Rp " . number_format($row['total_nilai'], 0, ',', '.') . "</td>
                                <td>Rp " . number_format($row['rata_rata_nilai'], 0, ',', '.') . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftar Buku Lengkap dengan Multiple JOIN -->
        <div class="table-section">
            <h3 class="section-title">📚 Daftar Buku Lengkap (Multiple JOIN)</h3>
            <div class="sql-query">
                SELECT b.judul, b.pengarang, k.nama_kategori, p.nama_penerbit, b.harga, b.stok
                FROM buku b 
                <span class="highlight">INNER JOIN</span> kategori k ON b.id_kategori = k.id_kategori
                <span class="highlight">INNER JOIN</span> penerbit p ON b.id_penerbit = p.id_penerbit
                <span class="highlight">ORDER BY</span> b.judul ASC
            </div>
            
            <div class="table-container">
                <table border="1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Penerbit</th>
                            <th>Harga</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_buku_lengkap = "
                            SELECT 
                                b.judul, 
                                b.pengarang, 
                                k.nama_kategori, 
                                p.nama_penerbit, 
                                b.harga, 
                                b.stok
                            FROM buku b
                            INNER JOIN kategori k ON b.id_kategori = k.id_kategori
                            INNER JOIN penerbit p ON b.id_penerbit = p.id_penerbit
                            ORDER BY b.judul ASC
                        ";
                        
                        $result_buku_lengkap = $koneksi->query($query_buku_lengkap);
                        $no = 1;
                        while ($row = $result_buku_lengkap->fetch_assoc()) {
                            $stok_class = $row['stok'] < 10 ? 'style="color: #e74c3c; font-weight: bold;"' : '';
                            echo "<tr>
                                <td>{$no}</td>
                                <td><strong>" . htmlspecialchars($row['judul']) . "</strong></td>
                                <td>" . htmlspecialchars($row['pengarang']) . "</td>
                                <td>" . htmlspecialchars($row['nama_kategori']) . "</td>
                                <td>" . htmlspecialchars($row['nama_penerbit']) . "</td>
                                <td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                                <td class='{$stok_class}'>" . number_format($row['stok']) . "</td>
                            </tr>";
                            $no++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Buku Terlaris (LEFT JOIN dengan Transaksi) -->
        <div class="table-section">
            <h3 class="section-title">🏆 Buku Terlaris</h3>
            <div class="sql-query">
                SELECT b.judul, b.pengarang, k.nama_kategori, 
                <span class="highlight">COUNT(t.id_transaksi)</span> as jumlah_transaksi,
                <span class="highlight">SUM(t.jumlah_beli)</span> as total_terjual,
                <span class="highlight">SUM(t.total_harga)</span> as total_pendapatan
                FROM buku b
                <span class="highlight">LEFT JOIN</span> transaksi t ON b.id_buku = t.id_buku AND t.status_transaksi = 'selesai'
                <span class="highlight">INNER JOIN</span> kategori k ON b.id_kategori = k.id_kategori
                <span class="highlight">GROUP BY</span> b.id_buku, b.judul, b.pengarang, k.nama_kategori
                <span class="highlight">ORDER BY</span> total_terjual DESC, jumlah_transaksi DESC
                <span class="highlight">LIMIT</span> 10
            </div>
            
            <div class="table-container">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Judul Buku</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_terlaris = "
                            SELECT 
                                b.judul, 
                                b.pengarang, 
                                k.nama_kategori,
                                COUNT(t.id_transaksi) as jumlah_transaksi,
                                IFNULL(SUM(t.jumlah_beli), 0) as total_terjual,
                                IFNULL(SUM(t.total_harga), 0) as total_pendapatan
                            FROM buku b
                            LEFT JOIN transaksi t ON b.id_buku = t.id_buku AND t.status_transaksi = 'selesai'
                            INNER JOIN kategori k ON b.id_kategori = k.id_kategori
                            GROUP BY b.id_buku, b.judul, b.pengarang, k.nama_kategori
                            ORDER BY total_terjual DESC, jumlah_transaksi DESC
                            LIMIT 10
                        ";
                        
                        $result_terlaris = $koneksi->query($query_terlaris);
                        $ranking = 1;
                        while ($row = $result_terlaris->fetch_assoc()) {
                            $medal = '';
                            if ($ranking == 1) $medal = '🥇';
                            elseif ($ranking == 2) $medal = '🥈';
                            elseif ($ranking == 3) $medal = '🥉';
                            else $medal = $ranking;
                            
                            echo "<tr>
                                <td style='text-align: center; font-size: 1.2rem;'>{$medal}</td>
                                <td><strong>" . htmlspecialchars($row['judul']) . "</strong></td>
                                <td>" . htmlspecialchars($row['pengarang']) . "</td>
                                <td>" . htmlspecialchars($row['nama_kategori']) . "</td>
                                <td>" . number_format($row['jumlah_transaksi']) . "</td>
                                <td><strong>" . number_format($row['total_terjual']) . "</strong></td>
                                <td>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>
                            </tr>";
                            $ranking++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="table-section">
            <h3 class="section-title">🕒 Transaksi Terbaru</h3>
            <div class="sql-query">
                SELECT t.nama_pembeli, b.judul, t.jumlah_beli, t.total_harga, t.tanggal_transaksi, t.status_transaksi
                FROM transaksi t
                <span class="highlight">INNER JOIN</span> buku b ON t.id_buku = b.id_buku
                <span class="highlight">ORDER BY</span> t.tanggal_transaksi DESC
                <span class="highlight">LIMIT</span> 15
            </div>
            
            <div class="table-container">
                <table border="1">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pembeli</th>
                            <th>Judul Buku</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_transaksi_terbaru = "
                            SELECT 
                                t.nama_pembeli,
                                b.judul,
                                t.jumlah_beli,
                                t.total_harga,
                                t.tanggal_transaksi,
                                t.status_transaksi
                            FROM transaksi t
                            INNER JOIN buku b ON t.id_buku = b.id_buku
                            ORDER BY t.tanggal_transaksi DESC
                            LIMIT 15
                        ";
                         $result_transaksi_terbaru = $koneksi->query($query_transaksi_terbaru);
<?php 
session_start();
include 'koneksi.php'; 

// Ambil pesan sukses jika ada
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku - Toko Buku Online</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .stats-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <header>
        <h1>Daftar Buku</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="book.php">Daftar Buku</a>
            <a href="add_book.php">Tambah Buku</a>
        </nav>
    </header>

    <main>
        <h2>Daftar Buku Tersedia</h2>
        
        <!-- Alert untuk menampilkan pesan sukses -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <!-- Quick action buttons -->
        <div class="btn-group">
            <a href="add_book.php" class="btn">+ Tambah Buku Baru</a>
            <button onclick="exportData()" class="btn btn-secondary">📊 Export Data</button>
            <button onclick="refreshData()" class="btn btn-secondary">🔄 Refresh</button>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="book.php" class="filter-form" id="filterForm">
            <input 
                type="text" 
                name="search" 
                placeholder="Cari Judul/Pengarang..." 
                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                id="searchInput"
            >
            <select name="sort_by" id="sortSelect">
                <option value="judul_asc" <?php echo (($_GET['sort_by'] ?? '') == 'judul_asc') ? 'selected' : ''; ?>>Judul (A-Z)</option>
                <option value="judul_desc" <?php echo (($_GET['sort_by'] ?? '') == 'judul_desc') ? 'selected' : ''; ?>>Judul (Z-A)</option>
                <option value="harga_asc" <?php echo (($_GET['sort_by'] ?? '') == 'harga_asc') ? 'selected' : ''; ?>>Harga (Termurah)</option>
                <option value="harga_desc" <?php echo (($_GET['sort_by'] ?? '') == 'harga_desc') ? 'selected' : ''; ?>>Harga (Termahal)</option>
                <option value="stok_asc" <?php echo (($_GET['sort_by'] ?? '') == 'stok_asc') ? 'selected' : ''; ?>>Stok (Terendah)</option>
                <option value="stok_desc" <?php echo (($_GET['sort_by'] ?? '') == 'stok_desc') ? 'selected' : ''; ?>>Stok (Tertinggi)</option>
            </select>
            <input type="submit" value="Terapkan Filter" id="filterBtn">
            <a href="book.php" class="reset-button">Reset Filter</a>
        </form>

        <?php
        // Inisialisasi variabel
        $search_query = $_GET['search'] ?? '';
        $sort_by = $_GET['sort_by'] ?? 'judul_asc';

        // Inisialisasi statistik
        $total_books = 0;
        $total_stock = 0;
        $avg_price = 0;
        $total_categories = 0;

        try {
            // Base query
            $sql = "
                SELECT b.judul, b.pengarang, k.nama_kategori, b.harga, b.stok
                FROM buku b
                INNER JOIN kategori k ON b.id_kategori = k.id_kategori
            ";
            // $sql = "
            //     SELECT judul, pengarang, nama_kategori, harga, stok
            //     FROM view_buku_lengkap
            //     ";


            $where_clause = [];
            $bind_params = '';
            $bind_values = [];

            // Search functionality
            if (!empty($search_query)) {
                $where_clause[] = "(b.judul LIKE ? OR b.pengarang LIKE ?)";
                $bind_params .= 'ss';
                $bind_values[] = '%' . $search_query . '%';
                $bind_values[] = '%' . $search_query . '%';
            }

            if (!empty($where_clause)) {
                $sql .= " WHERE " . implode(" AND ", $where_clause);
            }

            // Sorting
            switch ($sort_by) {
                case 'judul_asc':
                    $sql .= " ORDER BY b.judul ASC";
                    break;
                case 'judul_desc':
                    $sql .= " ORDER BY b.judul DESC";
                    break;
                case 'harga_asc':
                    $sql .= " ORDER BY b.harga ASC";
                    break;
                case 'harga_desc':
                    $sql .= " ORDER BY b.harga DESC";
                    break;
                case 'stok_asc':
                    $sql .= " ORDER BY b.stok ASC";
                    break;
                case 'stok_desc':
                    $sql .= " ORDER BY b.stok DESC";
                    break;
                default:
                    $sql .= " ORDER BY b.judul ASC";
                    break;
            }

            // Statistics query
            $stats_sql = "
                SELECT 
                    COUNT(*) as total_books,
                    SUM(b.stok) as total_stock,
                    AVG(b.harga) as avg_price,
                    COUNT(DISTINCT b.id_kategori) as total_categories
                FROM buku b
            ";
            
            if (!empty($where_clause)) {
                $stats_sql .= " WHERE " . implode(" AND ", $where_clause);
            }
            
            // Get statistics
            if ($stats_stmt = $koneksi->prepare($stats_sql)) {
                if (!empty($bind_params)) {
                    $stats_stmt->bind_param($bind_params, ...$bind_values);
                }
                $stats_stmt->execute();
                $stats_result = $stats_stmt->get_result();
                if ($stats_row = $stats_result->fetch_assoc()) {
                    $total_books = $stats_row['total_books'] ?? 0;
                    $total_stock = $stats_row['total_stock'] ?? 0;
                    $avg_price = $stats_row['avg_price'] ?? 0;
                    $total_categories = $stats_row['total_categories'] ?? 0;
                }
                $stats_stmt->close();
            }

        } catch (Exception $e) {
            // Log error atau tampilkan pesan error yang user-friendly
            error_log("Database error in book.php: " . $e->getMessage());
            echo "<div class='alert alert-error'>Terjadi kesalahan saat mengambil data. Silakan coba lagi.</div>";
        }
        ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stats-item">
                📚 Total Buku: <strong><?= number_format($total_books) ?></strong>
            </div>
            <div class="stats-item">
                📦 Total Stok: <strong><?= number_format($total_stock) ?></strong>
            </div>
            <div class="stats-item">
                💰 Harga Rata-rata: <strong>Rp <?= number_format($avg_price, 0, ',', '.') ?></strong>
            </div>
            <div class="stats-item">
                🏷️ Kategori: <strong><?= $total_categories ?></strong>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table border="1" id="booksTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        if ($stmt = $koneksi->prepare($sql)) {
                            if (!empty($bind_params)) {
                                $stmt->bind_param($bind_params, ...$bind_values);
                            }

                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
                                    $stok_class = $row['stok'] < 5 ? 'style="color: #e74c3c; font-weight: bold;"' : '';
                                    echo "<tr>
                                        <td>{$no}</td>
                                        <td>" . htmlspecialchars($row['judul']) . "</td>
                                        <td>" . htmlspecialchars($row['pengarang']) . "</td>
                                        <td>" . htmlspecialchars($row['nama_kategori']) . "</td>
                                        <td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                                        <td {$stok_class}>" . htmlspecialchars($row['stok']) . "</td>
                                    </tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr><td colspan='6' class='no-results'>
                                    <div>📚 Tidak ada buku yang ditemukan</div>
                                    <small>Coba ubah kata kunci pencarian atau tambah buku baru</small>
                                    </td></tr>";
                            }

                            $stmt->close();
                        } else {
                            echo "<tr><td colspan='6' class='no-results'>
                                <div style='color: #e74c3c;'>❌ Error: " . htmlspecialchars($koneksi->error) . "</div>
                                </td></tr>";
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='6' class='no-results'>
                            <div style='color: #e74c3c;'>❌ Terjadi kesalahan saat mengambil data buku</div>
                            </td></tr>";
                        error_log("Database error in book table: " . $e->getMessage());
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Toko Buku Online</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const sortSelect = document.getElementById('sortSelect');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Show loading on form submit
            filterForm.addEventListener('submit', function() {
                showLoading();
            });
            
            // Auto-apply filter on sort change
            sortSelect.addEventListener('change', function() {
                showLoading();
                filterForm.submit();
            });
            
            // Search with debounce
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2 || this.value.length === 0) {
                        showLoading();
                        filterForm.submit();
                    }
                }, 1000);
            });
            
            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
            
            function showLoading() {
                loadingOverlay.style.display = 'flex';
            }
            
            function hideLoading() {
                loadingOverlay.style.display = 'none';
            }
            
            // Hide loading when page is fully loaded
            window.addEventListener('load', function() {
                hideLoading();
            });
        });

        // Export functionality
        function exportData() {
            // Simple CSV export
            const table = document.getElementById('booksTable');
            let csv = '';
            
            // Add headers
            const headers = ['No', 'Judul', 'Pengarang', 'Kategori', 'Harga', 'Stok'];
            csv += headers.join(',') + '\n';
            
            // Add data rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    const rowData = Array.from(cells).map(cell => {
                        return '"' + cell.textContent.replace(/"/g, '""') + '"';
                    });
                    csv += rowData.join(',') + '\n';
                }
            });
            
            // Download CSV
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'daftar_buku_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Refresh functionality
        function refreshData() {
            showLoading();
            window.location.reload();
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
    </script>
</body>
</html>
<?php 
session_start();
include 'koneksi.php'; 

// Ambil data lama jika ada error
$old_data = $_SESSION['old_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

// Hapus session setelah digunakan
unset($_SESSION['old_data'], $_SESSION['errors'], $_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Toko Buku Online</title>
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
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .error-list {
            margin: 0;
            padding-left: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.has-error input,
        .form-group.has-error select {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .loading {
            display: none;
            opacity: 0.7;
            pointer-events: none;
        }
        
        .btn-submit {
            position: relative;
            min-width: 120px;
        }
        
        .btn-submit.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <header>
        <h1>Tambah Buku</h1>
        <nav>
            <a href="index.php">Beranda</a>
            <a href="book.php">Daftar Buku</a>
            <a href="add_book.php">Tambah Buku</a>
        </nav>
    </header>

    <main>
        <h2>Tambah Buku Baru</h2>
        
        <!-- Alert untuk menampilkan pesan -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Terdapat kesalahan dalam pengisian form:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="simpan_buku.php" id="bookForm" novalidate>
            <div class="form-group">
                <label for="judul">Judul <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="judul" 
                    name="judul" 
                    value="<?= htmlspecialchars($old_data['judul'] ?? '') ?>"
                    required
                    maxlength="255"
                    placeholder="Masukkan judul buku"
                >
            </div>

            <div class="form-group">
                <label for="pengarang">Pengarang <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="pengarang" 
                    name="pengarang" 
                    value="<?= htmlspecialchars($old_data['pengarang'] ?? '') ?>"
                    required
                    maxlength="255"
                    placeholder="Masukkan nama pengarang"
                >
            </div>

            <div class="form-group">
                <label for="harga">Harga (Rp) <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="harga" 
                    name="harga" 
                    value="<?= htmlspecialchars($old_data['harga'] ?? '') ?>"
                    required
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                >
            </div>

            <div class="form-group">
                <label for="stok">Stok <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="stok" 
                    name="stok" 
                    value="<?= htmlspecialchars($old_data['stok'] ?? '') ?>"
                    required
                    min="0"
                    placeholder="Jumlah stok"
                >
            </div>

            <div class="form-group">
                <label for="id_kategori">Kategori <span class="required">*</span></label>
                <select id="id_kategori" name="id_kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    $kategori = $koneksi->query("SELECT * FROM kategori ORDER BY nama_kategori");
                    while ($k = $kategori->fetch_assoc()) {
                        $selected = isset($old_data['id_kategori']) && $old_data['id_kategori'] == $k['id_kategori'] ? 'selected' : '';
                        echo "<option value='{$k['id_kategori']}' {$selected}>{$k['nama_kategori']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <input type="submit" value="Simpan Buku" class="btn-submit" id="submitBtn">
                <a href="book.php" class="btn btn-secondary" style="margin-left: 10px;">Batal</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Toko Buku Online</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bookForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                // Remove previous error styling
                document.querySelectorAll('.form-group').forEach(group => {
                    group.classList.remove('has-error');
                });
                
                // Validate each required field
                requiredFields.forEach(field => {
                    const formGroup = field.closest('.form-group');
                    
                    if (!field.value.trim()) {
                        formGroup.classList.add('has-error');
                        isValid = false;
                    }
                    
                    // Additional validation for specific fields
                    if (field.type === 'number' && field.value < 0) {
                        formGroup.classList.add('has-error');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang wajib diisi dengan benar.');
                    return false;
                }
                
                // Show loading state
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                submitBtn.value = 'Menyimpan...';
                
                // Prevent double submission
                form.style.opacity = '0.7';
                form.style.pointerEvents = 'none';
            });
            
            // Auto-hide alerts after 5 seconds
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
            
            // Real-time validation feedback
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const formGroup = this.closest('.form-group');
                    
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        formGroup.classList.add('has-error');
                    } else {
                        formGroup.classList.remove('has-error');
                    }
                });
                
                input.addEventListener('input', function() {
                    const formGroup = this.closest('.form-group');
                    if (this.value.trim()) {
                        formGroup.classList.remove('has-error');
                    }
                });
            });
        });
    </script>
</body>
</html>
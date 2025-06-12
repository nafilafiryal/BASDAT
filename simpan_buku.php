<?php
// Memulai session untuk pesan feedback
session_start();

include 'koneksi.php'; // Menyertakan file koneksi database

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Metode request tidak valid.";
    header("Location: add_book.php");
    exit;
}

// Validasi dan sanitasi input
function validateInput($data) {
    return htmlspecialchars(trim($data));
}

// Ambil dan validasi data dari POST
$errors = [];

$judul = isset($_POST['judul']) ? validateInput($_POST['judul']) : '';
$pengarang = isset($_POST['pengarang']) ? validateInput($_POST['pengarang']) : '';
$harga = isset($_POST['harga']) ? filter_var($_POST['harga'], FILTER_VALIDATE_FLOAT) : false;
$stok = isset($_POST['stok']) ? filter_var($_POST['stok'], FILTER_VALIDATE_INT) : false;
$id_kategori = isset($_POST['id_kategori']) ? filter_var($_POST['id_kategori'], FILTER_VALIDATE_INT) : false;

// Validasi input
if (empty($judul)) {
    $errors[] = "Judul buku harus diisi.";
}

if (empty($pengarang)) {
    $errors[] = "Pengarang buku harus diisi.";
}

if ($harga === false || $harga < 0) {
    $errors[] = "Harga harus berupa angka yang valid dan tidak negatif.";
}

if ($stok === false || $stok < 0) {
    $errors[] = "Stok harus berupa angka yang valid dan tidak negatif.";
}

if ($id_kategori === false || $id_kategori <= 0) {
    $errors[] = "Kategori harus dipilih.";
}

// Jika ada error, kembali ke form dengan pesan error
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_data'] = $_POST; // Simpan data lama untuk diisi kembali
    header("Location: add_book.php");
    exit;
}

// Verifikasi apakah kategori exists
$check_kategori = "SELECT id_kategori FROM kategori WHERE id_kategori = ?";
if ($stmt_check = $koneksi->prepare($check_kategori)) {
    $stmt_check->bind_param("i", $id_kategori);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        $_SESSION['error'] = "Kategori yang dipilih tidak valid.";
        $_SESSION['old_data'] = $_POST;
        header("Location: add_book.php");
        exit;
    }
    $stmt_check->close();
}

// Cek apakah buku dengan judul dan pengarang yang sama sudah ada
$check_duplicate = "SELECT id_buku FROM buku WHERE judul = ? AND pengarang = ?";
if ($stmt_duplicate = $koneksi->prepare($check_duplicate)) {
    $stmt_duplicate->bind_param("ss", $judul, $pengarang);
    $stmt_duplicate->execute();
    $result_duplicate = $stmt_duplicate->get_result();
    
    if ($result_duplicate->num_rows > 0) {
        $_SESSION['error'] = "Buku dengan judul dan pengarang yang sama sudah ada.";
        $_SESSION['old_data'] = $_POST;
        header("Location: add_book.php");
        exit;
    }
    $stmt_duplicate->close();
}

// Menggunakan Prepared Statement untuk mencegah SQL Injection
$query = "INSERT INTO buku (judul, pengarang, harga, stok, id_kategori) VALUES (?, ?, ?, ?, ?)";

// Siapkan statement
if ($stmt = $koneksi->prepare($query)) {
    // Bind parameter ke statement
    // 'ssdii' berarti:
    // s: string (judul)
    // s: string (pengarang) 
    // d: double (harga - untuk DECIMAL)
    // i: integer (stok)
    // i: integer (id_kategori)
    $stmt->bind_param("ssdii", $judul, $pengarang, $harga, $stok, $id_kategori);

    // Jalankan statement dengan error handling yang lebih baik
    if ($stmt->execute()) {
        $_SESSION['success'] = "Buku berhasil ditambahkan!";
        $stmt->close();
        $koneksi->close();
        header("Location: book.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan buku: " . $stmt->error;
        $_SESSION['old_data'] = $_POST;
        $stmt->close();
        header("Location: add_book.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Error preparing statement: " . $koneksi->error;
    $_SESSION['old_data'] = $_POST;
    header("Location: add_book.php");
    exit;
}

// Tutup koneksi database
$koneksi->close();
?>
<?php
session_start();
require_once __DIR__ . '/../../../App/Config/Database.php';

$pdo = \App\Config\Database::connect();
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = '';
unset($_SESSION['flash_success']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa = intval($_POST['id_siswa'] ?? 0);
    $id_guru = intval($_POST['id_guru'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $jenis_kegiatan = trim($_POST['jenis_kegiatan'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $catatan = trim($_POST['catatan_khusus'] ?? '');

    // Handling file upload
    $dokumentasi_path = '';
    if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_dokumentasi']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['foto_dokumentasi']['name']);
        $upload_dir = __DIR__ . '/../../../Public/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $dokumentasi_path = '/SIPAUDQU/Public/uploads/' . $file_name;
        }
    }

    if ($id_siswa && $jenis_kegiatan) {
        try {
            $stmt = $pdo->prepare("INSERT INTO aktivitas (id_siswa, id_guru, tanggal, jenis_kegiatan, kategori, catatan, dokumentasi) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_siswa, $id_guru, $tanggal, $jenis_kegiatan, $kategori, $catatan, $dokumentasi_path]);
            $_SESSION['flash_success'] = "Aktivitas harian berhasil disimpan! 🎉";
            header("Location: aktivitas.php");
            exit;
        } catch (Exception $e) {
            $flash_error = "Gagal menyimpan aktivitas: " . $e->getMessage();
        }
    } else {
        $flash_error = "Mohon isi Nama Siswa dan Jenis Kegiatan.";
    }
}

// Fetch students and teachers for select options
$allStudents = $pdo->query("SELECT id_siswa, nama_siswa FROM murid ORDER BY nama_siswa ASC")->fetchAll();
$allTeachers = $pdo->query("SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC")->fetchAll();

<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$id_guru = $_GET['id'] ?? null;

if (!$id_guru) {
    $_SESSION['flash_error'] = "ID Guru tidak valid.";
    header("Location: ../dataguru.php");
    exit;
}

try {
    $pdo = \App\Config\Database::connect();

    // Ambil data guru
    $stmt = $pdo->prepare("SELECT g.id_guru, g.id_user, g.nama_guru FROM guru g WHERE g.id_guru = :id");
    $stmt->execute(['id' => $id_guru]);
    $teacher = $stmt->fetch();

    if (!$teacher) {
        $_SESSION['flash_error'] = "Data guru tidak ditemukan.";
        header("Location: ../dataguru.php");
        exit;
    }
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Hapus data guru terlebih dahulu
        $stmt_g = $pdo->prepare("DELETE FROM guru WHERE id_guru = :id_guru");
        $stmt_g->execute(['id_guru' => $id_guru]);

        // Hapus data pengguna terkait
        if ($teacher['id_user']) {
            $stmt_p = $pdo->prepare("DELETE FROM pengguna WHERE id_user = :id_user");
            $stmt_p->execute(['id_user' => $teacher['id_user']]);
        }

        $_SESSION['flash_success'] = "Data guru " . htmlspecialchars($teacher['nama_guru']) . " berhasil dihapus.";
    } catch (\Exception $e) {
        $_SESSION['flash_error'] = "Gagal menghapus data: " . $e->getMessage();
    }
    header("Location: ../dataguru.php");
    exit;
}

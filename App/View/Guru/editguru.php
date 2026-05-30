<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . '/../../../../App/Config/Database.php';

$error = '';

$id_guru = $_GET['id'] ?? null;
if (!$id_guru) {
    $_SESSION['flash_error'] = "ID Guru tidak valid.";
    header("Location: ../dataguru.php");
    exit;
}

try {
    $pdo = \App\Config\Database::connect();

    // Ambil data guru beserta data pengguna
    $stmt = $pdo->prepare("
        SELECT g.id_guru, g.id_user, g.nama_guru, g.no_hp, g.status, p.peran, p.kata_sandi
        FROM guru g
        LEFT JOIN pengguna p ON g.id_user = p.id_user
        WHERE g.id_guru = :id
    ");
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
    $nama_guru  = trim($_POST['nama_guru'] ?? '');
    $no_hp      = trim($_POST['no_hp'] ?? '');
    $status     = $_POST['status'] ?? 'Aktif';
    $peran      = $_POST['peran'] ?? 'Guru';
    $kata_sandi = trim($_POST['kata_sandi'] ?? '');

    if (empty($nama_guru)) {
        $error = "Nama Guru wajib diisi.";
    } else {
        try {
            $pdo->beginTransaction();

            // Update tabel pengguna
            if (!empty($kata_sandi)) {
                $stmt_p = $pdo->prepare("UPDATE pengguna SET nama = :nama, kata_sandi = :kata_sandi, peran = :peran WHERE id_user = :id_user");
                $stmt_p->execute([
                    'nama'       => $nama_guru,
                    'kata_sandi' => $kata_sandi,
                    'peran'      => $peran,
                    'id_user'    => $teacher['id_user'],
                ]);
            } else {
                $stmt_p = $pdo->prepare("UPDATE pengguna SET nama = :nama, peran = :peran WHERE id_user = :id_user");
                $stmt_p->execute([
                    'nama'    => $nama_guru,
                    'peran'   => $peran,
                    'id_user' => $teacher['id_user'],
                ]);
            }

            // Update tabel guru
            $stmt_g = $pdo->prepare("UPDATE guru SET nama_guru = :nama_guru, no_hp = :no_hp, status = :status WHERE id_guru = :id_guru");
            $stmt_g->execute([
                'nama_guru' => $nama_guru,
                'no_hp'     => empty($no_hp) ? null : $no_hp,
                'status'    => $status,
                'id_guru'   => $id_guru,
            ]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Data guru berhasil diperbarui.";
            header("Location: ../dataguru.php");
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            $error = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

include '../../../../App/Layout/header.php';
?>
<style>
    body { overflow: hidden; }
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        z-index: 9999;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

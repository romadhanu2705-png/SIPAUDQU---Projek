<?php
session_start();

require_once __DIR__ . '/../../../App/Config/Database.php';

$search = $_GET['search'] ?? '';
$active_tab = $_GET['kelas'] ?? 'semua';

try {
    $pdo = \App\Config\Database::connect();

    // Ambil semua kelas
    $stmt_kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
    $daftar_kelas = $stmt_kelas->fetchAll();

    // Ambil semua siswa, group berdasarkan kelas
    $students_by_kelas = [];

    foreach ($daftar_kelas as $kelas) {
        $query = "
            SELECT m.id_siswa, m.nama_siswa, m.nis, m.tanggal_lahir, m.jenis_kelamin, m.alamat,
                   k.nama_kelas, w.nama_wali
            FROM murid m
            LEFT JOIN kelas k ON m.id_kelas = k.id_kelas
            LEFT JOIN wali_murid w ON m.id_wali = w.id_wali
            WHERE m.id_kelas = :id_kelas
        ";
        $params = ['id_kelas' => $kelas['id_kelas']];

        if (!empty($search)) {
            $query .= " AND (m.nama_siswa LIKE :search1 OR m.nis LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $query .= " ORDER BY m.nama_siswa ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $students_by_kelas[$kelas['id_kelas']] = [
            'nama_kelas' => $kelas['nama_kelas'],
            'id_kelas'   => $kelas['id_kelas'],
            'siswa'      => $stmt->fetchAll()
        ];
    }

    // Juga ambil semua siswa tanpa filter kelas (untuk tab "Semua")
    $query_all = "
        SELECT m.id_siswa, m.nama_siswa, m.nis, m.tanggal_lahir, m.jenis_kelamin, m.alamat,
               k.nama_kelas, w.nama_wali
        FROM murid m
        LEFT JOIN kelas k ON m.id_kelas = k.id_kelas
        LEFT JOIN wali_murid w ON m.id_wali = w.id_wali
    ";
    $params_all = [];
    if (!empty($search)) {
        $query_all .= " WHERE m.nama_siswa LIKE :search1 OR m.nis LIKE :search2";
        $params_all['search1'] = '%' . $search . '%';
        $params_all['search2'] = '%' . $search . '%';
    }
    $query_all .= " ORDER BY k.nama_kelas ASC, m.nama_siswa ASC";
    $stmt_all = $pdo->prepare($query_all);
    $stmt_all->execute($params_all);
    $all_students = $stmt_all->fetchAll();

} catch (\Exception $e) {
    die("Database error: " . $e->getMessage());
}

include __DIR__ . '/../../../App/Layout/header.php';
?>

<style>
    /* ====== TAB NAVIGATION ====== */
    .tab-wrapper {
        margin-bottom: 24px;
    }

    .tab-nav {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 12px 28px;
        font-family: 'Nunito', sans-serif;
        font-weight: 800;
        font-size: 0.9rem;
        color: #64748b;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: -2px;
        text-decoration: none;
    }
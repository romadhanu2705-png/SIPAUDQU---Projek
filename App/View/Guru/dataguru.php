<?php
session_start();

// Database connection and data fetching
require_once __DIR__ . '/../../../App/Config/Database.php';

$teachers = [];
$search = $_GET['search'] ?? '';

try {
    $pdo = \App\Config\Database::connect();

    $query = "
        SELECT g.id_guru, g.nama_guru, g.no_hp, g.status,
               p.peran, k.nama_kelas
        FROM guru g
        LEFT JOIN pengguna p ON g.id_user = p.id_user
        LEFT JOIN kelas k ON k.id_guru = g.id_guru
    ";

    $params = [];
    if (!empty($search)) {
        $query .= " WHERE g.nama_guru LIKE :search1 OR g.no_hp LIKE :search2";
        $params['search1'] = '%' . $search . '%';
        $params['search2'] = '%' . $search . '%';
    }

    $query .= " ORDER BY g.nama_guru ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $teachers = $stmt->fetchAll();

} catch (\Exception $e) {
    $db_error = "Database error: " . $e->getMessage();
}

include '../../../App/Layout/header.php';
?>

<div class="page-wrapper">
    <div class="layout-container">
        <?php include '../../../App/Layout/sidebar.php'; ?>

        <div class="main-content">
            <div class="content-card">
                <h3 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    👩‍🏫 Data Guru
                </h3>

                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div style="background: #dcfce3; color: #22c55e; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;">
                        <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;">
                        <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($db_error)): ?>
                    <div style="background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;">
                        <?php echo htmlspecialchars($db_error); ?>
                    </div>
                <?php endif; ?>

                <!-- Search Box -->
                <div style="margin-bottom: 15px;">
                    <form method="GET" style="display: flex; gap: 8px;">
                        <input type="text" name="search" placeholder="Cari Guru..." value="<?php echo htmlspecialchars($search); ?>"
                               style="flex: 1; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: 'Nunito', sans-serif;">
                        <button type="submit" style="padding: 10px 18px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-family: 'Nunito', sans-serif; font-size: 0.85rem;">Cari</button>
                        <?php if ($search): ?>
                            <a href="dataguru.php" style="padding: 10px 14px; background: #f1f5f9; color: #64748b; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center;">✕</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
                    <a href="CRUD/tambahguru.php" style="background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 700; font-family: 'Nunito', sans-serif;">+ Tambah Guru</a>
                </div>

                <!-- Data Table -->
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Guru</th>
                                <th>No. HP</th>
                                <th>Kelas Diajar</th>
                                <th>Peran</th>
                                <th>Status</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($teachers)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        <?php echo isset($db_error) ? 'Gagal memuat data.' : 'Tidak ada data guru'; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach($teachers as $teacher): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($teacher['nama_guru']); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['no_hp'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['nama_kelas'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($teacher['peran'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $status = $teacher['status'] ?? 'Aktif';
                                                $statusColor = $status === 'Aktif' ? '#22c55e' : '#ef4444';
                                                $statusBg   = $status === 'Aktif' ? '#dcfce7' : '#fee2e2';
                                            ?>
                                            <span style="background: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 6px; align-items: center;">
                                                <a href="CRUD/editguru.php?id=<?php echo $teacher['id_guru']; ?>"
                                                   style="color: #3b82f6; text-decoration: none; font-weight: 700; font-size: 0.75rem;">Edit</a>
                                                <span style="color: #cbd5e1;">|</span>
                                                <a href="CRUD/hapusguru.php?id=<?php echo $teacher['id_guru']; ?>"
                                                   style="color: #ef4444; text-decoration: none; font-weight: 700; font-size: 0.75rem;">Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../App/Layout/footer.php'; ?>

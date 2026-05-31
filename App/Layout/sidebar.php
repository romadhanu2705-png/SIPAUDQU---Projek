<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div class="sidebar">
    <?php
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    function sidebarActive($page) {
        return basename($_SERVER['SCRIPT_NAME']) === $page ? 'active' : '';
    }
    ?>

    <div style="padding: 15px; text-align: center; border-bottom: 1px solid #e2e8f0;">
        <h1 style="font-size: 1.2rem; font-weight: 900; color: var(--primary); margin: 0; line-height: 1;">SIPAUDQU</h1>
        <p style="font-size: 0.65rem; color: #94a3b8; margin: 4px 0 0 0; font-weight: 700;">Sistem Informasi PAUD Qur'an</p>
    </div>

    <nav class="sidebar-nav">
        <li>
            <a href="/SIPAUDQU/App/View/Guru/dashboardguru.php" class="<?= sidebarActive('dashboardguru.php') ?>">
                <span class="nav-icon">🏠</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="/SIPAUDQU/App/View/Guru/dataguru.php" class="<?= sidebarActive('dataguru.php') ?>">
                <span class="nav-icon">🧒</span>
                <span>Data Guru</span>
            </a>
        </li>
        <li>
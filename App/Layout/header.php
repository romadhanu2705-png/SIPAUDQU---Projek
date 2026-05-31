<?php
// header.php - Guru/Admin version
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? $pageTitle . ' - SIPAUDQU' : 'SIPAUDQU - Sistem Informasi PAUD Qur\'an' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/SIPAUDQU/Public/css/style.css">
</head>
<body>

<!-- Clouds -->
<div class="cloud cloud-1"></div>
<div class="cloud cloud-2"></div>
<div class="cloud cloud-3"></div>

<div class="page-wrapper">

  <!-- HEADER -->
  <header class="site-header">
    <div class="header-brand">
      <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
      </button>
      <div>
        <div class="header-logo-text">
          <span class="s">S</span><span class="i">I</span><span class="p">P</span><span class="a">A</span><span class="u">U</span><span class="d">D</span><span class="q">Q</span><span class="u2">U</span>
        </div>
        <div class="header-subtitle">Sistem Informasi PAUD <span>Qur'an</span></div>
      </div>
    </div>
    <div class="header-user">
      <div class="header-user-avatar">
        <i class="fas fa-user"></i>
      </div>
      <div>
        <div style="font-size:0.82rem; font-weight:800; color:#1e293b;">
          <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guru' ?>
        </div>
        <div style="font-size:0.70rem; color:#64748b;">
          <?= isset($_SESSION['peran']) ? htmlspecialchars($_SESSION['peran']) : 'Guru/Admin' ?>
        </div>
      </div>
    </div>
  </header>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Wali Murid - SIPAUDQU</title>
  <!-- Google Fonts & FontAwesome -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    /* ── BASE STYLES ──────────────────────────────────────────────────────── */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Outfit', sans-serif;
    }
    
    body {
      background-color: #f1f5f9;
      color: #1e293b;
      display: flex;
      min-height: 100vh;
    }

    /* ── SIDEBAR NAVIGATION ────────────────────────────────────────────────── */
    .sidebar {
      width: 280px;
      background: linear-gradient(185deg, #0f172a, #1e1b4b);
      color: #cbd5e1;
      display: flex;
      flex-direction: column;
      padding: 28px 24px;
      flex-shrink: 0;
      box-shadow: 4px 0 20px rgba(15, 23, 42, 0.15);
    }
    
    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.4rem;
      font-weight: 800;
      color: #ffffff;
      margin-bottom: 40px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .sidebar-logo i {
      color: #818cf8;
      font-size: 1.65rem;
    }
    
    .sidebar-menu {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
      flex-grow: 1;
    }
    
    .sidebar-item a {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 18px;
      color: #94a3b8;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .sidebar-item a:hover {
      background: rgba(255, 255, 255, 0.05);
      color: #ffffff;
      transform: translateX(4px);
    }
    
    .sidebar-item.active a {
      background: linear-gradient(135deg, #4f46e5, #6366f1);
      color: #ffffff;
      box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    }
    
    .sidebar-footer {
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .logout-btn {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 18px;
      color: #fca5a5;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.95rem;
      transition: all 0.2s ease;
    }
    
    .logout-btn:hover {
      background: rgba(239, 68, 68, 0.12);
      color: #ef4444;
    }

    /* ── MAIN CONTENT WORKSPACE ────────────────────────────────────────────── */
    .main-workspace {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow-y: auto;
    }
    
    .top-header {
      background: #ffffff;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 48px;
      border-bottom: 1px solid #e2e8f0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
      flex-shrink: 0;
    }
    
    .header-title {
      font-size: 1.25rem;
      font-weight: 800;
      color: #0f172a;
    }
    
    .user-profile {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    
    .avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #e0e7ff;
      color: #4f46e5;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.1rem;
      border: 2px solid #818cf8;
    }
    
    .user-info {
      display: flex;
      flex-direction: column;
    }
    
    .user-name {
      font-size: 0.95rem;
      font-weight: 700;
      color: #0f172a;
    }
    
    .user-role {
      font-size: 0.78rem;
      color: #64748b;
      font-weight: 500;
    }
    
    .content-container {
      padding: 40px 48px;
      display: flex;
      flex-direction: column;
      gap: 28px;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }

    /* ── WELCOME BANNER ────────────────────────────────────────────────────── */
    .welcome-banner {
      background: linear-gradient(135deg, #4f46e5, #818cf8);
      border-radius: 20px;
      padding: 32px 40px;
      color: #ffffff;
      box-shadow: 0 10px 20px rgba(79, 70, 229, 0.15);
      position: relative;
      overflow: hidden;
    }
    
    .welcome-banner::after {
      content: '';
      position: absolute;
      right: -40px;
      top: -40px;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.06);
    }
    
    .welcome-banner::before {
      content: '';
      position: absolute;
      right: 80px;
      bottom: -60px;
      width: 150px;
      height: 150px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.04);
    }
    
    .welcome-banner h3 {
      font-size: 1.6rem;
      font-weight: 800;
      margin-bottom: 8px;
    }
    
    .welcome-banner p {
      font-size: 0.98rem;
      color: #e0e7ff;
      font-weight: 400;
    }

    /* ── SELECTOR PANEL ────────────────────────────────────────────────────── */
    .selector-card {
      background: #ffffff;
      border-radius: 18px;
      padding: 20px 28px;
      border: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }
    
    .selector-label {
      font-weight: 800;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1rem;
    }
    
    .selector-label i {
      color: #4f46e5;
      font-size: 1.3rem;
    }
    
    .custom-select {
      padding: 12px 20px;
      border-radius: 12px;
      border: 1.5px solid #cbd5e1;
      background-color: #f8fafc;
      font-size: 0.95rem;
      font-weight: 700;
      color: #1e293b;
      outline: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .custom-select:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    /* ── STUDENT PROFILE HEADER ────────────────────────────────────────────── */
    .student-header {
      background: #ffffff;
      border-radius: 18px;
      padding: 24px 30px;
      border: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      gap: 22px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }
    
    .student-avatar-box {
      width: 64px;
      height: 64px;
      border-radius: 16px;
      background: linear-gradient(135deg, #a5b4fc, #6366f1);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.85rem;
      font-weight: 800;
      box-shadow: 0 6px 15px rgba(99, 102, 241, 0.25);
    }

       <div class="card">
            <h4 class="card-title">
              <i class="fa-solid fa-calendar-check"></i>
              <span id="attendanceTitle">Ringkasan Absensi Bulan Mei</span>
            </h4>
            <div class="attendance-grid">
              <div class="attendance-item hadir">
                <span class="attendance-label">Hadir</span>
                <span class="attendance-value">
                  <span id="attHadir">20</span>
                  <span class="attendance-unit">Hari</span>
                </span>
              </div>
              <div class="attendance-item sakit">
                <span class="attendance-label">Sakit</span>
                <span class="attendance-value">
                  <span id="attSakit">1</span>
                  <span class="attendance-unit">Hari</span>
                </span>
              </div>
              <div class="attendance-item izin">
                <span class="attendance-label">Izin</span>
                <span class="attendance-value">
                  <span id="attIzin">1</span>
                  <span class="attendance-unit">Hari</span>
                </span>
              </div>
              <div class="attendance-item alpha">
                <span class="attendance-label">Alpha</span>
                <span class="attendance-value">
                  <span id="attAlpha">0</span>
                  <span class="attendance-unit">Hari</span>
                </span>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Column: Aktivitas & Perkembangan -->
        <div style="display: flex; flex-direction: column; gap: 28px;">
          
          <!-- Aktivitas Terakhir -->
          <div class="card">
            <h4 class="card-title">
              <i class="fa-solid f .student-title-info {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    
    .student-name-display {
      font-size: 1.45rem;
      font-weight: 800;
      color: #0f172a;
    }
    
    .student-class-display {
      display: inline-block;
      align-self: flex-start;
      padding: 4px 14px;
      background: #e0f2fe;
      color: #0369a1;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 800;
    }

    /* ── DASHBOARD GRID & CARDS ────────────────────────────────────────────── */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 28px;
    }
    
    @media (max-width: 1024px) {
      .dashboard-grid {
        grid-template-columns: 1fr;
      }
    }
    
    .card {
      background: #ffffff;
      border-radius: 20px;
      padding: 28px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
      display: flex;
      flex-direction: column;
      gap: 22px;
      transition: all 0.3s ease;
      opacity: 1;
    }
    
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
    }
    
    .card-title {
      font-size: 1.15rem;
      font-weight: 800;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1.5px solid #f1f5f9;
      padding-bottom: 16px;
    }
    
    .card-title i {
      color: #4f46e5;
    }

    /* ── BIODATA TABLE ────────────────────────────────────────────────────── */
    .biodata-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .biodata-table tr {
      border-bottom: 1px solid #f8fafc;
    }
    
    .biodata-table tr:last-child {
      border-bottom: none;
    }
    
    .biodata-table td {
      padding: 14px 10px;
      font-size: 0.92rem;
    }
    
    .biodata-table td:first-child {
      font-weight: 700;
      color: #64748b;
      width: 150px;
    }
    
    .biodata-table td:nth-child(2) {
      width: 24px;
      color: #cbd5e1;
      font-weight: 800;
    }
    
    .biodata-table td:last-child {
      color: #1e293b;
      font-weight: 500;
    }

    /* ── ATTENDANCE CARDS ─────────────────────────────────────────────────── */
    .attendance-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 18px;
    }
    
    .attendance-item {
      border-radius: 16px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.01);
    }
    
    .attendance-item::before {
      content: '';
      position: absolute;
      right: -10px;
      bottom: -10px;
      font-size: 4.5rem;
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      opacity: 0.06;
    }
    
    .attendance-item.hadir {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #047857;
    }
    .attendance-item.hadir::before { content: '\f00c'; }
    
    .attendance-item.sakit {
      background: #fffbeb;
      border: 1px solid #fde68a;
      color: #b45309;
    }
    .attendance-item.sakit::before { content: '\f0f1'; }
    
    .attendance-item.izin {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      color: #1d4ed8;
    }
    .attendance-item.izin::before { content: '\f274'; }
    
    .attendance-item.alpha {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
    }
    .attendance-item.alpha::before { content: '\f00d'; }
    
    .attendance-label {
      font-size: 0.8rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    
    .attendance-value {
      font-size: 1.7rem;
      font-weight: 800;
      display: flex;
      align-items: baseline;
      gap: 4px;
    }
    
    .attendance-unit {
      font-size: 0.9rem;
      font-weight: 600;
      opacity: 0.85;
    }

    /* ── TIMELINE & ACTIVITY BOXES ────────────────────────────────────────── */
    .activity-box {
      display: flex;
      gap: 20px;
      background: #f8fafc;
      padding: 20px;
      border-radius: 16px;
      border: 1px solid #f1f5f9;
    }
    
    .activity-date-badge {
      width: 64px;
      height: 74px;
      background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
      color: #4f46e5;
      border-radius: 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 4px 10px rgba(79, 70, 229, 0.06);
    }
    
    .date-day {
      font-size: 1.65rem;
      font-weight: 800;
      line-height: 1;
    }
    
    .date-month {
      font-size: 0.72rem;
      font-weight: 800;
      text-transform: uppercase;
      margin-top: 4px;
    }
    
    .activity-details {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    
    .activity-name {
      font-size: 1.05rem;
      font-weight: 800;
      color: #0f172a;
    }
    
    .activity-desc {
      font-size: 0.88rem;
      color: #475569;
      line-height: 1.5;
    }
    
    .activity-author, .dev-author {
      font-size: 0.78rem;
      font-weight: 700;
      color: #94a3b8;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-top: 6px;
    }
    
    .activity-author i, .dev-author i {
      color: #94a3b8;
    }

    .dev-box {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    
    .dev-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .dev-badge {
      padding: 6px 14px;
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
      border-radius: 20px;
      font-size: 0.78rem;
      font-weight: 800;
    }
    
    .dev-date {
      font-size: 0.78rem;
      font-weight: 800;
      color: #94a3b8;
    }
    
    .dev-text {
      font-size: 0.88rem;
      color: #334155;
      line-height: 1.6;
      background: #f8fafc;
      border-left: 4px solid #10b981;
      padding: 16px 20px;
      border-radius: 0 16px 16px 0;
      border-top: 1px solid #f1f5f9;
      border-right: 1px solid #f1f5f9;
      border-bottom: 1px solid #f1f5f9;
    }

    /* ── ANIMATIONS ───────────────────────────────────────────────────────── */
    .fade-transition {
      animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(6px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

  <!-- SIDEBAR NAVIGATION -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <i class="fa-solid fa-graduation-cap"></i>
      <span>SIPAUDQU</span>
    </div>
    
    <nav style="flex-grow: 1;">
      <ul class="sidebar-menu">
        <li class="sidebar-item active">
          <a href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
        </li>
        <li class="sidebar-item">
          <a href="#"><i class="fa-solid fa-calendar-days"></i> Jadwal Belajar</a>
        </li>
        <li class="sidebar-item">
          <a href="#"><i class="fa-solid fa-chart-line"></i> Laporan Anak</a>
        </li>
        <li class="sidebar-item">
          <a href="#"><i class="fa-solid fa-user-check"></i> Presensi</a>
        </li>
      </ul>
    </nav>
    
    <div class="sidebar-footer">
      <a href="#" class="logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i> Keluar
      </a>
    </div>
  </aside>

  <!-- MAIN WORKSPACE -->
  <div class="main-workspace">
    <!-- TOP HEADER -->
    <header class="top-header">
      <div class="header-title">Sistem Informasi PAUD</div>
      <div class="user-profile">
        <div class="avatar">R</div>
        <div class="user-info">
          <span class="user-name">Romadhanu</span>
          <span class="user-role">Wali Murid</span>
        </div>
      </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <main class="content-container">
      
      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <h3>🎉 Selamat datang, Bapak/Ibu Romadhanu!</h3>
        <p>Pantau perkembangan, absensi, dan aktivitas belajar ananda tercinta di SIPAUDQU.</p>
      </div>

      <!-- Selector Panel -->
      <div class="selector-card">
        <div class="selector-label">
          <i class="fa-solid fa-child-reaching"></i>
          <span>Pilih Profil Anak:</span>
        </div>
        <select id="studentSelect" class="custom-select">
          <option value="1">Muhammad Rayhan - Kelas A</option>
          <option value="2">Nayla Humaira - Kelas B</option>
        </select>
      </div>

      <!-- Student Profile Header -->
      <div class="student-header">
        <div class="student-avatar-box">
          <i class="fa-solid fa-child"></i>
        </div>
        <div class="student-title-info">
          <div id="studentNameDisplay" class="student-name-display">Muhammad Rayhan</div>
          <span id="studentClassDisplay" class="student-class-display">Kelompok A</span>
        </div>
      </div>

      <!-- Dashboard Grid -->
      <div class="dashboard-grid">
        
        <!-- Left Column: Biodata & Absensi -->
        <div style="display: flex; flex-direction: column; gap: 28px;">
          
          <!-- Biodata Anak -->
          <div class="card">
            <h4 class="card-title">
              <i class="fa-solid fa-address-card"></i>
              <span>🌱 Biodata Anak</span>
            </h4>
            <table class="biodata-table">
              <tr>
                <td>Nama Lengkap</td>
                <td>:</td>
                <td id="bioName">Muhammad Rayhan</td>
              </tr>
              <tr>
                <td>NIS</td>
                <td>:</td>
                <td id="bioNis">20240901</td>
              </tr>
              <tr>
                <td>Tanggal Lahir</td>
                <td>:</td>
                <td id="bioDob">15 Mei 2019</td>
              </tr>
              <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td id="bioGender">Laki-laki</td>
              </tr>
              <tr>
                <td>Alamat</td>
                <td>:</td>
                <td id="bioAddress">Perumahan Indah Lestari Blok C No. 5, Sidoarjo</td>
              </tr>
              <tr>
                <td>Orang Tua / Wali</td>
                <td>:</td>
                <td id="bioParent">Romadhanu</td>
              </tr>
              <tr>
                <td>Kelompok Kelas</td>
                <td>:</td>
                <td id="bioClass">Kelompok A</td>
              </tr>
            </table>
</div> 

 <!-- Ringkasan Absensi -->a-business-time"></i>
              <span>📅 Aktivitas Belajar Terakhir</span>
            </h4>
            <div class="activity-box">
              <div class="activity-date-badge">
                <span id="actDay" class="date-day">28</span>
                <span id="actMonth" class="date-month">Mei</span>
              </div>
              <div class="activity-details">
                <h5 id="actTitle" class="activity-name">Praktek Sholat & Mengaji</h5>
                <p id="actDesc" class="activity-desc">Rayhan dapat mengikuti gerakan sholat dengan tertib dan membaca Iqra dengan fasih.</p>
                <div class="activity-author">
                  <i class="fa-solid fa-user-tie"></i>
                  <span>Guru Pengajar: <span id="actTeacher">Bu Sarah</span></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Catatan Perkembangan -->
          <div class="card">
            <h4 class="card-title">
              <i class="fa-solid fa-star"></i>
              <span>⭐ Catatan Perkembangan Terbaru</span>
            </h4>
            <div class="dev-box">
              <div class="dev-card-header">
                <span id="devAspect" class="dev-badge">Aspek Kognitif</span>
                <span id="devDate" class="dev-date">Mei 2026</span>
              </div>
              <p id="devText" class="dev-text">Ananda Rayhan sudah mampu mengenali angka 1-10 dengan sangat baik dan mengelompokkan warna secara mandiri.</p>
              <div class="dev-author">
                <i class="fa-solid fa-pen-nib"></i>
                <span>Oleh Wali Kelas: <span id="devTeacher">Bu Sarah</span></span>
              </div>
            </div>
          </div>

        </div>

      </div>

    </main>
  </div>

  <!-- INTERACTIVE JAVASCRIPT FOR FRONTEND DATA SWITCHING -->
  <script>
    // Mock Data Store
    const studentsData = {
      "1": {
        nama: "Muhammad Rayhan",
        nis: "20240901",
        tanggal_lahir: "15 Mei 2019",
        jenis_kelamin: "Laki-laki",
        alamat: "Perumahan Indah Lestari Blok C No. 5, Sidoarjo",
        orangtua: "Romadhanu",
        kelas: "Kelompok A",
        absensi: {
          bulan: "Mei",
          hadir: 20,
          sakit: 1,
          izin: 1,
          alpha: 0
        },
        aktivitas: {
          tanggal: "28",
          bulan: "Mei",
          kegiatan: "Praktek Sholat & Mengaji",
          deskripsi: "Rayhan dapat mengikuti gerakan sholat dengan tertib dan membaca Iqra dengan fasih.",
          guru: "Bu Sarah"
        },
        perkembangan: {
          bulan_tahun: "Mei 2026",
          aspek: "Aspek Kognitif",
          catatan: "Ananda Rayhan sudah mampu mengenali angka 1-10 dengan sangat baik dan mengelompokkan warna secara mandiri.",
          guru: "Bu Sarah"
        }
      },
      "2": {
        nama: "Nayla Humaira",
        nis: "20240902",
        tanggal_lahir: "22 Agustus 2019",
        jenis_kelamin: "Perempuan",
        alamat: "Jl. Flamboyan No. 12, Sidoarjo",
        orangtua: "Romadhanu",
        kelas: "Kelompok B",
        absensi: {
          bulan: "Mei",
          hadir: 18,
          sakit: 2,
          izin: 2,
          alpha: 0
        },
        aktivitas: {
          tanggal: "27",
          bulan: "Mei",
          kegiatan: "Menggambar dan Mewarnai",
          deskripsi: "Nayla melukis pemandangan taman bunga dengan kombinasi warna pastel yang sangat indah.",
          guru: "Bu Laras"
        },
        perkembangan: {
          bulan_tahun: "Mei 2026",
          aspek: "Aspek Motorik Halus",
          catatan: "Ananda Nayla menunjukkan ketelitian yang tinggi dalam meronce manik-manik dan memotong bentuk geometris.",
          guru: "Bu Laras"
        }
      }
    };

    // DOM Elements
    const studentSelect = document.getElementById('studentSelect');
    const studentNameDisplay = document.getElementById('studentNameDisplay');
    const studentClassDisplay = document.getElementById('studentClassDisplay');
    
    // Biodata DOM
    const bioName = document.getElementById('bioName');
    const bioNis = document.getElementById('bioNis');
    const bioDob = document.getElementById('bioDob');
    const bioGender = document.getElementById('bioGender');
    const bioAddress = document.getElementById('bioAddress');
    const bioParent = document.getElementById('bioParent');
    const bioClass = document.getElementById('bioClass');
    
    // Absensi DOM
    const attendanceTitle = document.getElementById('attendanceTitle');
    const attHadir = document.getElementById('attHadir');
    const attSakit = document.getElementById('attSakit');
    const attIzin = document.getElementById('attIzin');
    const attAlpha = document.getElementById('attAlpha');
    
    // Aktivitas DOM
    const actDay = document.getElementById('actDay');
    const actMonth = document.getElementById('actMonth');
    const actTitle = document.getElementById('actTitle');
    const actDesc = document.getElementById('actDesc');
    const actTeacher = document.getElementById('actTeacher');
    
    // Perkembangan DOM
    const devAspect = document.getElementById('devAspect');
    const devDate = document.getElementById('devDate');
    const devText = document.getElementById('devText');
    const devTeacher = document.getElementById('devTeacher');

    // Function to update page content dynamically
    function updateStudentData(studentId) {
      const data = studentsData[studentId];
      if (!data) return;

      // Add fade class to trigger animation
      const cards = document.querySelectorAll('.card, .student-header');
      cards.forEach(card => card.classList.remove('fade-transition'));
      
      // Force reflow
      void studentNameDisplay.offsetWidth;

      // Update Texts
      studentNameDisplay.innerText = data.nama;
      studentClassDisplay.innerText = data.kelas;
      
      bioName.innerText = data.nama;
      bioNis.innerText = data.nis;
      bioDob.innerText = data.tanggal_lahir;
      bioGender.innerText = data.jenis_kelamin;
      bioAddress.innerText = data.alamat;
      bioParent.innerText = data.orangtua;
      bioClass.innerText = data.kelas;
      
      attendanceTitle.innerText = `Ringkasan Absensi Bulan ${data.absensi.bulan}`;
      attHadir.innerText = data.absensi.hadir;
      attSakit.innerText = data.absensi.sakit;
      attIzin.innerText = data.absensi.izin;
      attAlpha.innerText = data.absensi.alpha;
      
      actDay.innerText = data.aktivitas.tanggal;
      actMonth.innerText = data.aktivitas.bulan;
      actTitle.innerText = data.aktivitas.kegiatan;
      actDesc.innerText = data.aktivitas.deskripsi;
      actTeacher.innerText = data.aktivitas.guru;
      
      devAspect.innerText = data.perkembangan.aspek;
      devDate.innerText = data.perkembangan.bulan_tahun;
      devText.innerText = data.perkembangan.catatan;
      devTeacher.innerText = data.perkembangan.guru;

      // Add transition class
      cards.forEach(card => card.classList.add('fade-transition'));
    }

    // Event Listener
    studentSelect.addEventListener('change', (e) => {
      updateStudentData(e.target.value);
    });

    // Initialize with first student
    updateStudentData("1");
  </script>
</body>
</html>
an_terbaru['guru']) ?>
              </p>
            </div>
          </div>

        </div><!-- .dashboard-grid -->

      </div><!-- .content-card -->
    </main>
  </div><!-- .layout-container -->

<?php include '../../../App/Layout/footer.php'; ?>


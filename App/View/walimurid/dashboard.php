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

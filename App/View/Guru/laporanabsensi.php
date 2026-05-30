<style>
    .laporan-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        font-family: 'Nunito', sans-serif;
    }
    
    .page-title {
        color: #2563eb;
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .controls-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .filter-input {
        padding: 8px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Nunito', sans-serif;
        font-weight: 700;
        color: #0f172a;
        outline: none;
    }
    .filter-input:focus { border-color: #3b82f6; }
    
    .search-input {
        flex: 1;
        padding: 8px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: 'Nunito', sans-serif;
        font-weight: 600;
        color: #0f172a;
        outline: none;
    }

    .search-input:focus { border-color: #3b82f6; }
    
    .tab-nav {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .tab-pill {
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 0.85rem;
        text-decoration: none;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        background: white;
        transition: all 0.2s;
    }
    
    .tab-pill.active {
        background: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
    }
    
    .data-table th {
        background: #e0f2fe;
        color: #0f172a;
        font-weight: 800;
        padding: 15px;
        text-align: center;
        border-bottom: 2px solid #cbd5e1;
    }
    
    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
        color: #0f172a;
        font-weight: 800;
        font-size: 0.9rem;
        vertical-align: middle;
        border-right: 1px solid #e2e8f0;
    }
    .data-table td:last-child { border-right: none; }
    
    .stat-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.9rem;
    }
    .stat-hadir { background: #dcfce7; color: #22c55e; border: 1px solid #86efac; }
    .stat-izin { background: #fef3c7; color: #eab308; border: 1px solid #fde047; }
    .stat-sakit { background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; }
    .stat-alpa { background: #e0f2fe; color: #3b82f6; border: 1px solid #93c5fd; }
    
    .btn-detail {
        background: #fbbf24;
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 800;
        display: inline-block;
        transition: opacity 0.2s;
    }
    .btn-detail:hover { opacity: 0.8; }
    
    .modal-overlay { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        display: flex; align-items: center; justify-content: center; 
        z-index: 9999; 
        background: rgba(255, 255, 255, 0.5); 
        backdrop-filter: blur(4px); 
        -webkit-backdrop-filter: blur(4px);
    }
    .modal-card { 
        background: white; border-radius: 20px; padding: 30px; width: 450px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.15); position: relative; font-family: 'Nunito', sans-serif;
    }
    .modal-close {
        position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;
    }
    .modal-title {
        color: #2563eb; font-size: 1.1rem; font-weight: 800; margin-top: 0; margin-bottom: 20px; text-align: center;
    }
    .detail-table {
        width: 100%; border-collapse: collapse; border-radius: 10px; overflow: hidden; border: 1px solid #cbd5e1;
    }
    .detail-table th { background: #f8fafc; color: #0f172a; font-weight: 800; padding: 12px; text-align: center; border-bottom: 1px solid #cbd5e1; font-size: 0.9rem;}
    .detail-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center; font-size: 0.85rem; font-weight: 700; border-right: 1px solid #e2e8f0;}
    .detail-table td:last-child { border-right: none; }
    .badge-pill-small { display: inline-block; padding: 4px 15px; border-radius: 20px; font-weight: 800; font-size: 0.75rem; }
</style>
<?php
require_once __DIR__ . '/../../libs/db.php';
require_once __DIR__ . '/../../libs/admin_auth.php';
startSession();

$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage !== 'login.php') {
    requireAdminLogin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPG Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #030b14; color: #fff; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        
        .top-navbar { height: 60px; background: #bfa100; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; z-index: 10; color: #000; }
        .top-navbar h2 { margin: 0; font-size: 20px; font-weight: bold; }
        .admin-profile { display: flex; align-items: center; gap: 10px; font-weight: bold; }
        
        .content-layout { display: flex; flex: 1; height: calc(100vh - 60px); }
        
        sidebar { width: 260px; background: #061121; border-right: 1px solid #ffb703; padding: 20px 10px; display: flex; flex-direction: column; overflow-y: auto; transition: margin-left 0.3s ease; }
        .nav-list { display: flex; flex-direction: column; gap: 8px; list-style: none; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 12px; color: #e2e8f0; text-decoration: none; border-radius: 6px; font-size: 14px; transition: background 0.2s; }
        .nav-item a:hover { background: rgba(255, 183, 3, 0.1); color: #ffb703; }
        .nav-item a i { width: 20px; text-align: center; }
        .submenu { margin-left: 30px; display: none; flex-direction: column; gap: 5px; }
        .nav-item.open .submenu { display: flex; }
        
        main { flex: 1; padding: 30px; overflow-y: auto; }
        .card { background: rgba(6, 17, 33, 0.8); border: 1px solid #ffb703; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .card-title { color: #ffb703; margin-bottom: 15px; font-size: 18px; }
        
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(255, 183, 3, 0.2); }
        th { background: rgba(255, 183, 3, 0.1); color: #ffb703; font-weight: bold; }
        
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; color: #000; }
        .btn-gold { background: #bfa100; }
        .btn-danger { background: #ff4d4d; color: #fff; }
        .btn-success { background: #2ecc71; color: #fff; }
        
        .breadcrumb { display: flex; gap: 8px; margin-bottom: 20px; color: #a0aec0; font-size: 14px; }
        .breadcrumb a { color: #ffb703; text-decoration: none; }
        
        /* Grid */
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .metric-card { background: #061121; border: 1px solid #ffb703; padding: 20px; border-radius: 8px; text-align: center; }
        .metric-value { font-size: 24px; font-weight: bold; color: #ffb703; margin-top: 10px; }
        
        /* Form */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #ffb703; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; background: rgba(0,0,0,0.5); border: 1px solid #ffb703; color: #fff; border-radius: 4px; }
        
    </style>
</head>
<body>
<?php if ($currentPage !== 'login.php'): ?>
    <div class="top-navbar">
        <h2>SAPG Admin Panel</h2>
        <div class="admin-profile">
            <i class="fas fa-user-shield"></i>
            <span>Admin</span>
        </div>
    </div>
    <div class="content-layout">
        <?php include 'sidebar.php'; ?>
        <main>
<?php endif; ?>

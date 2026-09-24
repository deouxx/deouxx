<?php
require_once __DIR__ . '/../../includes/data.php';

// Proteksi Autentikasi: Hanya Admin & Kasir yang diizinkan
if (!isAdmin()) {
    $_SESSION['flash_msg_error'] = 'Silakan masuk dengan akun Admin atau Kasir untuk mengakses dashboard.';
    header('Location: login.php');
    exit;
}

$current_admin_page = basename($_SERVER['PHP_SELF']);
$admin_user = $_SESSION['user'] ?? ['name' => 'Petugas', 'role' => 'kasir'];
$is_super_admin = isSuperAdmin();
$user_role_label = $is_super_admin ? 'Administrator' : 'Kasir Utama';

// Hitung jumlah tagihan pending untuk badge menu
$pending_bills_count = 0;
try {
    $pStmt = $pdo->query("SELECT COUNT(*) as cnt FROM bills WHERE payment_status = 'unpaid'");
    $pending_bills_count = (int) $pStmt->fetch()['cnt'];
} catch (Exception $e) {
    $pending_bills_count = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Dashboard'); ?> - Dapur Ina Aina POS & Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Admin & POS Stylesheet -->
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

    <!-- Sidebar Admin & Kasir -->
    <aside class="admin-sidebar <?= $is_super_admin ? 'sidebar-admin-mode' : 'sidebar-kasir-mode'; ?>" id="admin-sidebar" aria-label="Navigasi <?= $is_super_admin ? 'Admin' : 'Kasir'; ?>">
        <a href="index.php" class="sidebar-brand">
            <div class="brand-icon-box <?= $is_super_admin ? 'brand-icon-admin' : ''; ?>">
                <?php if ($is_super_admin): ?>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <?php else: ?>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                <?php endif; ?>
            </div>
            <div class="brand-name-group">
                <span class="brand-name-main">Dapur Ina Aina</span>
                <span class="brand-name-sub"><?= $is_super_admin ? 'Panel Admin' : 'POS Kasir'; ?></span>
            </div>
        </a>

        <nav class="sidebar-menu">
            <?php if ($is_super_admin): ?>
            <span class="menu-section-label" style="color: #fbbf24; letter-spacing: 0.08em;">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="#fbbf24" stroke="none" style="vertical-align:middle; margin-right:4px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Panel Administrator
            </span>
            <?php else: ?>
            <span class="menu-section-label">Operasional Kasir</span>
            <?php endif; ?>
            
            <a href="index.php" class="sidebar-link <?= ($current_admin_page === 'index.php') ? 'active' : ''; ?>">
                <span class="link-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </span>
                <span><?= $is_super_admin ? 'Ringkasan Admin' : 'Ringkasan Kasir'; ?></span>
            </a>

            <a href="bills.php" class="sidebar-link <?= ($current_admin_page === 'bills.php' || $current_admin_page === 'bill-detail.php') ? 'active' : ''; ?>">
                <span class="link-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </span>
                <span>Tagihan &amp; Kasir</span>
                <?php if ($pending_bills_count > 0): ?>
                    <span class="sidebar-badge"><?= $pending_bills_count; ?></span>
                <?php endif; ?>
            </a>

            <?php if ($is_super_admin): ?>
            <span class="menu-section-label">Manajemen Dapur</span>

            <a href="menu.php" class="sidebar-link <?= ($current_admin_page === 'menu.php') ? 'active' : ''; ?>">
                <span class="link-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                </span>
                <span>Katalog &amp; Stok Menu</span>
            </a>
            <?php endif; ?>

            <span class="menu-section-label">Pintasan</span>

            <a href="../menu.php" target="_blank" rel="noopener" class="sidebar-link">
                <span class="link-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </span>
                <span>Buka Menu Toko</span>
            </a>

            <a href="../index.php" target="_blank" rel="noopener" class="sidebar-link">
                <span class="link-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </span>
                <span>Buka Toko Utama</span>
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-avatar-circle <?= $is_super_admin ? 'avatar-admin' : ''; ?>">
                <?= strtoupper(substr($admin_user['name'], 0, 1)); ?>
            </div>
            <div class="user-info-text">
                <div class="user-info-name"><?= htmlspecialchars($admin_user['name']); ?></div>
                <div class="user-info-role"><?= htmlspecialchars($user_role_label); ?></div>
            </div>
            <a href="logout.php" class="btn-sidebar-logout" title="Keluar dari Dashboard">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="admin-main-wrapper">
        <!-- Top Bar -->
        <header class="admin-topbar <?= $is_super_admin ? 'topbar-admin' : 'topbar-kasir'; ?>">
            <div class="topbar-left">
                <button type="button" class="btn-mobile-sidebar-toggle" id="sidebar-toggle-btn" aria-label="Buka Menu Sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <div>
                    <h1 class="topbar-page-title"><?= htmlspecialchars($page_title ?? 'Dashboard'); ?></h1>
                    <?php if ($is_super_admin): ?>
                    <span style="font-size:0.72rem; color:#f59e0b; font-weight:700; letter-spacing:0.05em; text-transform:uppercase;">
                        ★ Mode Administrator
                    </span>
                    <?php else: ?>
                    <span style="font-size:0.72rem; color:var(--admin-primary); font-weight:700; letter-spacing:0.05em; text-transform:uppercase;">
                        Kasir Aktif
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="topbar-right">
                <span class="system-status-indicator">
                    <span class="status-pulse-dot"></span>
                    <span>POS Aktif &bull; <?= date('d M Y'); ?></span>
                </span>
                
                <a href="../index.php" target="_blank" rel="noopener" class="topbar-view-store">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>Website Toko</span>
                </a>
            </div>
        </header>

        <!-- Flash Message Alerts -->
        <?php if (!empty($_SESSION['flash_msg'])): ?>
            <div class="admin-flash-alert success" style="margin: 20px 28px 0; padding: 12px 18px; background: #dcfce7; color: #15803d; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 10px; border-left: 4px solid #16a34a;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span><?= htmlspecialchars($_SESSION['flash_msg']); ?></span>
            </div>
            <?php unset($_SESSION['flash_msg']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_msg_error'])): ?>
            <div class="admin-flash-alert error" style="margin: 20px 28px 0; padding: 12px 18px; background: #fee2e2; color: #b91c1c; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 10px; border-left: 4px solid #ef4444;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($_SESSION['flash_msg_error']); ?></span>
            </div>
            <?php unset($_SESSION['flash_msg_error']); ?>
        <?php endif; ?>


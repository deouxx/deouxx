<?php
require_once __DIR__ . '/data.php';

$current_page = basename($_SERVER['PHP_SELF']);
$cart_count = getCartCount();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Dapur Ina Aina | Cita Rasa Rumahan Otentik</title>
    <meta name="description" content="Dapur Ina Aina menyajikan aneka masakan rumahan Nusantara lezat, higienis, dan 100% halal. Pesan antar makanan dan minuman segar dengan mudah dan cepat.">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Top Announcement Bar (Tanpa Emotikon, Menggunakan SVG Bersih) -->
    <div class="top-announcement">
        <div class="container announcement-inner">
            <span class="announcement-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="inline-svg"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
                Gratis Ongkir Pesanan &ge; Rp 100rb (Kode: <strong>INAHEMAT</strong>)
            </span>
            <span class="announcement-divider">|</span>
            <span class="announcement-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="inline-svg"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                100% Halal, Segar & Higienis
            </span>
            <span class="announcement-divider">|</span>
            <span class="announcement-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="inline-svg"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                CS & Katering: <a href="tel:+6281234567890">0812-3456-7890</a>
            </span>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="site-header" id="header">
        <div class="container nav-container">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-logo" aria-label="Dapur Ina Aina">
                <div class="logo-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                        <line x1="6" y1="1" x2="6" y2="4"></line>
                        <line x1="10" y1="1" x2="10" y2="4"></line>
                        <line x1="14" y1="1" x2="14" y2="4"></line>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-title">Dapur Ina Aina<span class="brand-dot">.</span></span>
                    <span class="brand-subtitle">Cita Rasa Otentik Rumahan</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="nav-menu" id="nav-menu" aria-label="Navigasi Utama">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?= ($current_page === 'index.php') ? 'active' : ''; ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a href="menu.php" class="nav-link <?= ($current_page === 'menu.php') ? 'active' : ''; ?>">Daftar Menu</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php#about" class="nav-link">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php#contact" class="nav-link">Kontak</a>
                    </li>
                </ul>

                <div class="nav-mobile-extras">
                    <?php if (!empty($_SESSION['user'])): ?>
                        <div class="user-greeting">
                            Halo, <strong><?= htmlspecialchars($_SESSION['user']['name']); ?></strong>!
                            <?php if (isAdmin()): ?>
                                <a href="admin/index.php" class="btn btn-primary btn-sm">Dashboard</a>
                            <?php endif; ?>
                            <a href="login.php?action=logout" class="btn btn-outline btn-sm">Keluar</a>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Right Actions (Cart & Auth) -->
            <div class="nav-actions">
                <!-- Cart Button with Counter -->
                <a href="cart.php" class="btn-cart <?= ($current_page === 'cart.php') ? 'active' : ''; ?>" aria-label="Keranjang Belanja">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="cart-label">Pesanan</span>
                    <span class="cart-badge" id="cart-counter"><?= $cart_count; ?></span>
                </a>

                <!-- User Session Menu -->
                <div class="user-nav-dropdown">
                    <?php if (!empty($_SESSION['user'])): ?>
                        <div class="user-pill">
                            <span class="user-avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)); ?></span>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name']); ?></span>
                            <?php if (isAdmin()): ?>
                                <a href="admin/index.php" class="btn-admin-nav-chip" title="Buka Dashboard">Dashboard</a>
                            <?php endif; ?>
                            <a href="login.php?action=logout" class="logout-link" title="Keluar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hamburger Button for Mobile -->
                <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Buka Menu" aria-expanded="false" aria-controls="nav-menu">
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                    <span class="hamburger-bar"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Global Flash Message Alerts -->
    <?php if (!empty($_SESSION['flash_msg'])): ?>
        <div class="global-alert alert-success">
            <div class="container alert-content">
                <span class="alert-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </span>
                <span><?= htmlspecialchars($_SESSION['flash_msg']); ?></span>
                <button type="button" class="close-alert" onclick="this.parentElement.parentElement.remove();">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['flash_msg']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_msg_error'])): ?>
        <div class="global-alert alert-error">
            <div class="container alert-content">
                <span class="alert-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </span>
                <span><?= htmlspecialchars($_SESSION['flash_msg_error']); ?></span>
                <button type="button" class="close-alert" onclick="this.parentElement.parentElement.remove();">&times;</button>
            </div>
        </div>
        <?php unset($_SESSION['flash_msg_error']); ?>
    <?php endif; ?>

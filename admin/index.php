<?php
$page_title = 'Ringkasan Dashboard';
require_once __DIR__ . '/includes/header.php';

// ============================================================
// AMBIL DATA UNTUK SEMUA ROLE
// ============================================================
try {
    // Total Pendapatan Lunas
    $revStmt = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM bills WHERE payment_status = 'paid'");
    $total_revenue = (int) $revStmt->fetch()['total'];

    // Total Tagihan
    $countStmt = $pdo->query("SELECT COUNT(*) as total_bills, COALESCE(SUM(grand_total), 0) as gross_total FROM bills");
    $bills_overview = $countStmt->fetch();
    $total_bills    = (int) $bills_overview['total_bills'];
    $gross_total    = (int) $bills_overview['gross_total'];

    // Lunas vs Unpaid
    $statusStmt = $pdo->query("SELECT
        COUNT(CASE WHEN payment_status = 'paid'   THEN 1 END) as paid_count,
        COUNT(CASE WHEN payment_status = 'unpaid' THEN 1 END) as unpaid_count
        FROM bills");
    $status_data  = $statusStmt->fetch();
    $paid_count   = (int) $status_data['paid_count'];
    $unpaid_count = (int) $status_data['unpaid_count'];

    // Hari ini
    $todayStmt = $pdo->query("SELECT COUNT(*) as cnt, COALESCE(SUM(grand_total), 0) as today_rev
        FROM bills WHERE DATE(created_at) = DATE('now')");
    $today_data  = $todayStmt->fetch();
    $today_count = (int) $today_data['cnt'];
    $today_rev   = (int) $today_data['today_rev'];

    // 10 tagihan terbaru
    $recentBillsStmt = $pdo->query("SELECT b.*, o.table_no
        FROM bills b LEFT JOIN orders o ON b.order_id = o.id
        ORDER BY b.id DESC LIMIT 10");
    $recent_bills = $recentBillsStmt->fetchAll();

    // Menu Terlaris (Top 5)
    $topMenuStmt = $pdo->query("SELECT menu_name, SUM(qty) as total_sold, SUM(subtotal) as total_val
        FROM order_items GROUP BY menu_name ORDER BY total_sold DESC LIMIT 5");
    $top_menu = $topMenuStmt->fetchAll();

    // ---- Hanya untuk Admin ----
    if (isSuperAdmin()) {
        // Total item menu
        $menuCountStmt  = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_available=1 THEN 1 ELSE 0 END) as available FROM menu_items");
        $menu_stats     = $menuCountStmt->fetch();
        $total_menu     = (int) $menu_stats['total'];
        $available_menu = (int) $menu_stats['available'];

        // Stok habis
        $stockHabisStmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 0 ORDER BY id DESC");
        $stok_habis     = $stockHabisStmt->fetchAll();

        // Semua menu untuk tabel stok cepat
        $allMenuStmt = $pdo->query("SELECT * FROM menu_items ORDER BY category_slug, name ASC LIMIT 20");
        $all_menu_quick = $allMenuStmt->fetchAll();
    }

} catch (Exception $e) {
    $total_revenue  = $total_bills = $paid_count = $unpaid_count = 0;
    $today_count    = $today_rev  = $gross_total  = 0;
    $recent_bills   = $top_menu   = [];
    $total_menu     = $available_menu = 0;
    $stok_habis     = $all_menu_quick = [];
}
?>

<?php if (isSuperAdmin()): ?>
<!-- ============================================================ -->
<!-- DASHBOARD ADMINISTRATOR (FULL CONTROL)                       -->
<!-- ============================================================ -->
<main class="admin-content">

    <!-- Admin Hero Banner -->
    <div style="background: linear-gradient(135deg, #1a1040 0%, #0f172a 100%); border-radius: 16px; padding: 24px 28px; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="admin-section-badge" style="margin-bottom: 10px;">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="#92400e"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Panel Administrator
            </div>
            <h2 style="color:#ffffff; font-size:1.3rem; font-weight:800; margin-bottom:4px;">Selamat Datang, <?= htmlspecialchars($admin_user['name']); ?>!</h2>
            <p style="color:#94a3b8; font-size:0.85rem; margin:0;">Anda memiliki akses penuh ke semua fitur manajemen dapur &amp; keuangan.</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="menu.php?action=add" class="btn-admin btn-admin-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706); border:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Menu</span>
            </a>
            <a href="bills.php?status=unpaid" class="btn-admin btn-admin-secondary" style="background:rgba(255,255,255,0.1); color:#fff; border-color:rgba(255,255,255,0.2);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Cek Tagihan (<?= $unpaid_count; ?>)</span>
            </a>
        </div>
    </div>

    <!-- KPI Metrics Grid -->
    <section class="kpi-metrics-grid" style="margin-bottom: 28px;">
        <!-- Pendapatan Riil -->
        <div class="kpi-card admin-gold">
            <div class="kpi-info-group">
                <span class="kpi-label">Pendapatan Lunas</span>
                <span class="kpi-value"><?= formatRupiah($total_revenue); ?></span>
                <span class="kpi-subtext">Dari <strong><?= $paid_count; ?> tagihan</strong> terbayar</span>
            </div>
            <div class="kpi-icon-wrap green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>

        <!-- Total Tagihan -->
        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Total Tagihan (Bills)</span>
                <span class="kpi-value"><?= $total_bills; ?></span>
                <span class="kpi-subtext">Nilai bruto: <?= formatRupiah($gross_total); ?></span>
            </div>
            <div class="kpi-icon-wrap orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
        </div>

        <!-- Stok Menu Aktif -->
        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Stok Menu Aktif</span>
                <span class="kpi-value"><?= $available_menu; ?> / <?= $total_menu; ?></span>
                <span class="kpi-subtext" style="<?= (count($stok_habis) > 0) ? 'color:#dc2626;font-weight:700;' : ''; ?>">
                    <?= count($stok_habis) > 0 ? count($stok_habis) . ' menu HABIS!' : 'Semua stok tersedia'; ?>
                </span>
            </div>
            <div class="kpi-icon-wrap" style="background: linear-gradient(135deg,#fef3c7,#fde68a);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
            </div>
        </div>

        <!-- Hari Ini -->
        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Omset Hari Ini</span>
                <span class="kpi-value"><?= formatRupiah($today_rev); ?></span>
                <span class="kpi-subtext"><?= $today_count; ?> transaksi hari ini</span>
            </div>
            <div class="kpi-icon-wrap purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
        </div>
    </section>

    <!-- Grid: Tagihan + Stok -->
    <div class="bill-detail-grid">

        <!-- Kolom Kiri: Tagihan Terbaru -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h2>Tagihan &amp; Pesanan Terbaru</h2>
                        <p>10 transaksi terakhir yang masuk</p>
                    </div>
                    <div class="card-header-actions">
                        <a href="bills.php" class="btn-admin btn-admin-secondary"><span>Lihat Semua &rarr;</span></a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="admin-table admin-overview-table">
                        <thead>
                            <tr>
                                <th>No. Bill</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Bayar</th>
                                <th>Dapur</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_bills)): ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada tagihan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recent_bills as $b): ?>
                                    <tr>
                                        <td>
                                            <strong style="color:var(--text-main); display:block; font-size:0.82rem;"><?= htmlspecialchars($b['bill_number']); ?></strong>
                                            <span style="font-size:0.72rem; color:var(--text-muted);"><?= htmlspecialchars($b['receipt_id']); ?></span>
                                        </td>
                                        <td>
                                            <span style="font-weight:700; font-size:0.85rem;"><?= htmlspecialchars($b['customer_name']); ?></span>
                                            <span style="display:block; font-size:0.72rem; color:var(--text-muted);"><?= strtoupper($b['order_type']); ?></span>
                                        </td>
                                        <td><strong><?= formatRupiah($b['grand_total']); ?></strong></td>
                                        <td>
                                            <?php if ($b['payment_status'] === 'paid'): ?>
                                                <span class="badge-status paid">Lunas</span>
                                            <?php else: ?>
                                                <span class="badge-status unpaid">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-order <?= htmlspecialchars($b['order_status']); ?>">
                                                <?= ucfirst(htmlspecialchars($b['order_status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="bill-detail.php?id=<?= $b['id']; ?>" class="btn-action-icon" title="Rincian">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Stok Menu + Top Selling -->
        <div style="display:flex; flex-direction:column; gap:20px;">

            <!-- Kelola Stok Cepat -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h3>Kelola Stok Menu Cepat</h3>
                        <p>Toggle ketersediaan menu dapur</p>
                    </div>
                    <div class="card-header-actions">
                        <a href="menu.php" class="btn-admin btn-admin-secondary" style="font-size:0.78rem; padding:6px 12px;"><span>Kelola Semua</span></a>
                    </div>
                </div>
                <div style="padding: 0 20px 16px; max-height: 320px; overflow-y: auto;">
                    <?php if (empty($all_menu_quick)): ?>
                        <p style="padding:20px 0; color:var(--text-muted); font-size:0.85rem;">Belum ada menu terdaftar.</p>
                    <?php else: ?>
                        <?php foreach ($all_menu_quick as $m): ?>
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border-light); gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                                    <img src="../<?= htmlspecialchars($m['image_url']); ?>" alt="" style="width:36px; height:36px; border-radius:8px; object-fit:cover; flex-shrink:0;">
                                    <div style="min-width:0;">
                                        <strong style="font-size:0.82rem; display:block; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px;"><?= htmlspecialchars($m['name']); ?></strong>
                                        <span style="font-size:0.72rem; color:var(--text-muted);"><?= formatRupiah($m['price']); ?></span>
                                    </div>
                                </div>
                                <form action="menu.php" method="POST" style="flex-shrink:0;">
                                    <input type="hidden" name="action" value="toggle_availability">
                                    <input type="hidden" name="item_id" value="<?= $m['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?= $m['is_available']; ?>">
                                    <button type="submit" style="padding:4px 10px; font-size:0.72rem; font-weight:700; border-radius:6px; border:none; cursor:pointer; transition:all 0.2s;
                                        <?= $m['is_available'] == 1
                                            ? 'background:#dcfce7; color:#15803d;'
                                            : 'background:#fee2e2; color:#b91c1c;'; ?>">
                                        <?= $m['is_available'] == 1 ? 'Tersedia' : 'Habis'; ?>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (count($stok_habis) > 0): ?>
                <div style="padding: 10px 20px; background:#fef2f2; border-top:1px solid #fecaca; border-radius: 0 0 12px 12px;">
                    <p style="font-size:0.78rem; color:#b91c1c; font-weight:700; margin:0;">
                        &#9888; <?= count($stok_habis); ?> menu sedang habis — segera perbarui stok!
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Top Menu Terlaris -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h3>Menu Paling Laris</h3>
                        <p>Total porsi terpesan</p>
                    </div>
                </div>
                <div style="padding: 0 20px 10px;">
                    <?php if (empty($top_menu)): ?>
                        <p style="padding:20px 0; color:var(--text-muted); font-size:0.85rem;">Belum ada data penjualan.</p>
                    <?php else: ?>
                        <?php foreach ($top_menu as $idx => $tm): ?>
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border-light); gap:8px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="width:22px; height:22px; border-radius:50%; background:<?= ['#fbbf24','#94a3b8','#cd7c54','#a5b4fc','#6ee7b7'][$idx] ?? '#e2e8f0'; ?>; color:#fff; font-size:0.7rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><?= $idx + 1; ?></span>
                                    <div>
                                        <strong style="font-size:0.85rem; display:block; color:var(--text-main);"><?= htmlspecialchars($tm['menu_name']); ?></strong>
                                        <span style="font-size:0.72rem; color:var(--text-muted);"><?= formatRupiah($tm['total_val']); ?></span>
                                    </div>
                                </div>
                                <span style="background:var(--admin-primary-light); color:var(--admin-primary); font-weight:800; font-size:0.78rem; padding:3px 10px; border-radius:999px; flex-shrink:0;">
                                    <?= $tm['total_sold']; ?> porsi
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<?php else: ?>
<!-- ============================================================ -->
<!-- DASHBOARD KASIR (OPERASIONAL)                                -->
<!-- ============================================================ -->
<main class="admin-content">

    <!-- Kasir Header Banner -->
    <div class="kasir-dashboard-header">
        <div class="kasir-badge-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
        </div>
        <div>
            <h2>Selamat Bertugas, <?= htmlspecialchars($admin_user['name']); ?>!</h2>
            <p>Anda login sebagai <strong>Kasir</strong> &mdash; <?= date('l, d F Y'); ?> &bull; Kelola tagihan pelanggan di bawah ini.</p>
        </div>
    </div>

    <!-- KPI Mini Kasir -->
    <section class="kpi-metrics-grid" style="margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Menunggu Bayar</span>
                <span class="kpi-value" style="color:#ea580c;"><?= $unpaid_count; ?></span>
                <span class="kpi-subtext">Tagihan perlu konfirmasi</span>
            </div>
            <div class="kpi-icon-wrap orange">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Lunas Hari Ini</span>
                <span class="kpi-value"><?= $today_count; ?></span>
                <span class="kpi-subtext">Transaksi selesai hari ini</span>
            </div>
            <div class="kpi-icon-wrap green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Omset Hari Ini</span>
                <span class="kpi-value" style="font-size:1.1rem;"><?= formatRupiah($today_rev); ?></span>
                <span class="kpi-subtext">Total pembayaran masuk</span>
            </div>
            <div class="kpi-icon-wrap blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info-group">
                <span class="kpi-label">Total Tagihan</span>
                <span class="kpi-value"><?= $total_bills; ?></span>
                <span class="kpi-subtext">Semua waktu</span>
            </div>
            <div class="kpi-icon-wrap purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            </div>
        </div>
    </section>

    <div class="bill-detail-grid">
        <!-- Tagihan Terbaru Kasir -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h2>Tagihan Masuk Terbaru</h2>
                        <p>Pantau dan proses pembayaran pelanggan</p>
                    </div>
                    <div class="card-header-actions">
                        <a href="bills.php" class="btn-admin btn-admin-secondary"><span>Lihat Semua &rarr;</span></a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>No. Bill</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_bills)): ?>
                                <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada tagihan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recent_bills as $b): ?>
                                    <tr>
                                        <td>
                                            <strong style="color:var(--text-main); font-size:0.85rem;"><?= htmlspecialchars($b['bill_number']); ?></strong>
                                        </td>
                                        <td>
                                            <span style="font-weight:700;"><?= htmlspecialchars($b['customer_name']); ?></span>
                                            <span style="display:block; font-size:0.72rem; color:var(--text-muted);"><?= strtoupper($b['order_type']); ?></span>
                                        </td>
                                        <td><strong><?= formatRupiah($b['grand_total']); ?></strong></td>
                                        <td>
                                            <?php if ($b['payment_status'] === 'paid'): ?>
                                                <span class="badge-status paid">Lunas</span>
                                            <?php else: ?>
                                                <span class="badge-status unpaid">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:5px; align-items:center;">
                                                <?php if ($b['payment_status'] === 'unpaid'): ?>
                                                    <form action="bills.php" method="POST" onsubmit="return confirm('Tandai tagihan <?= htmlspecialchars(addslashes($b['bill_number'])); ?> sebagai LUNAS?');">
                                                        <input type="hidden" name="action" value="mark_paid">
                                                        <input type="hidden" name="bill_id" value="<?= $b['id']; ?>">
                                                        <button type="submit" class="btn-admin btn-admin-success" style="padding:4px 8px; font-size:0.72rem;">Bayar Lunas</button>
                                                    </form>
                                                <?php endif; ?>
                                                <a href="bill-detail.php?id=<?= $b['id']; ?>" class="btn-action-icon" title="Detail">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </a>
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

        <!-- Aksi Cepat Kasir -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group"><h3>Aksi Cepat Kasir</h3></div>
                </div>
                <div style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="bills.php?status=unpaid" class="btn-admin btn-admin-primary" style="justify-content:center; padding:14px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Tagihan Belum Lunas (<?= $unpaid_count; ?>)</span>
                    </a>
                    <a href="bills.php" class="btn-admin btn-admin-secondary" style="justify-content:center; padding:14px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <span>Semua Tagihan</span>
                    </a>
                    <a href="../menu.php" target="_blank" class="btn-admin btn-admin-secondary" style="justify-content:center; padding:14px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Buka Menu Toko &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Top Menu Terlaris untuk Kasir -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group"><h3>Menu Paling Laris</h3></div>
                </div>
                <div style="padding: 0 20px 10px;">
                    <?php if (empty($top_menu)): ?>
                        <p style="padding:16px 0; color:var(--text-muted); font-size:0.85rem;">Belum ada data penjualan.</p>
                    <?php else: ?>
                        <?php foreach ($top_menu as $tm): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border-light);">
                                <strong style="font-size:0.85rem; color:var(--text-main);"><?= htmlspecialchars($tm['menu_name']); ?></strong>
                                <span style="background:var(--admin-primary-light); color:var(--admin-primary); font-weight:800; font-size:0.78rem; padding:3px 10px; border-radius:999px;">
                                    <?= $tm['total_sold']; ?> porsi
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

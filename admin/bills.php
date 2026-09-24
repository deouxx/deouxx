<?php
// ============================================================
// WAJIB: POST handler harus di paling atas, SEBELUM include header
// karena header.php langsung output HTML
// ============================================================
require_once __DIR__ . '/../includes/data.php';

// Pastikan sesi & auth sudah berjalan
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action  = trim($_POST['action']);
    $bill_id = (int) ($_POST['bill_id'] ?? 0);

    if ($action === 'mark_paid' && $bill_id > 0) {
        try {
            $now = date('Y-m-d H:i:s');
            $upd = $pdo->prepare("
                UPDATE bills
                SET payment_status = 'paid',
                    order_status   = 'completed',
                    amount_paid    = grand_total,
                    paid_at        = ?,
                    updated_at     = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $upd->execute([$now, $bill_id]);

            $bRow = $pdo->prepare("SELECT order_id FROM bills WHERE id = ?");
            $bRow->execute([$bill_id]);
            $orderRow = $bRow->fetch();
            if ($orderRow) {
                $pdo->prepare("UPDATE orders SET payment_status = 'Lunas (Kasir Terverifikasi)' WHERE id = ?")
                    ->execute([$orderRow['order_id']]);
            }
            $_SESSION['flash_msg'] = 'Tagihan berhasil ditandai LUNAS!';
        } catch (Exception $e) {
            $_SESSION['flash_msg_error'] = 'Gagal: ' . $e->getMessage();
        }
        header('Location: bills.php');
        exit;
    }

    if ($action === 'update_order_status' && $bill_id > 0) {
        try {
            $allowed    = ['pending', 'cooking', 'delivering', 'completed', 'cancelled'];
            $new_status = $_POST['order_status'] ?? 'pending';
            if (!in_array($new_status, $allowed)) $new_status = 'pending';

            $upd = $pdo->prepare("UPDATE bills SET order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$new_status, $bill_id]);
            $_SESSION['flash_msg'] = 'Status pesanan diperbarui: ' . ucfirst($new_status);
        } catch (Exception $e) {
            $_SESSION['flash_msg_error'] = 'Gagal: ' . $e->getMessage();
        }
        header('Location: bills.php');
        exit;
    }
}

// ============================================================
// Baru include header (yang output HTML) setelah POST selesai
// ============================================================
$page_title = 'Kelola Tagihan & Kasir';
require_once __DIR__ . '/includes/header.php';

// ============================================================
// Filter & Pencarian
// ============================================================
$filter_status = $_GET['status'] ?? 'semua';
$search_q      = trim($_GET['q'] ?? '');

$sql    = "SELECT b.*, o.table_no FROM bills b LEFT JOIN orders o ON b.order_id = o.id WHERE 1=1";
$params = [];

if (in_array($filter_status, ['paid', 'unpaid', 'cancelled'])) {
    $sql     .= " AND b.payment_status = ?";
    $params[] = $filter_status;
} elseif (in_array($filter_status, ['pending', 'cooking', 'delivering', 'completed'])) {
    $sql     .= " AND b.order_status = ?";
    $params[] = $filter_status;
}

if (!empty($search_q)) {
    $like     = '%' . $search_q . '%';
    $sql     .= " AND (b.bill_number LIKE ? OR b.receipt_id LIKE ? OR b.customer_name LIKE ? OR b.customer_phone LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY b.id DESC";

try {
    $stmt  = $pdo->prepare($sql);
    $stmt->execute($params);
    $bills = $stmt->fetchAll();
} catch (Exception $e) {
    $bills = [];
}

$pills = [
    'semua'      => 'Semua Tagihan',
    'unpaid'     => 'Belum Lunas',
    'paid'       => 'Lunas',
    'pending'    => 'Menunggu Dapur',
    'cooking'    => 'Dimasak',
    'delivering' => 'Diantar',
    'completed'  => 'Selesai',
];
?>

<main class="admin-content">
    <div class="admin-card">

        <!-- Header -->
        <div class="admin-card-header">
            <div class="card-heading-group">
                <h2>Daftar Seluruh Tagihan &amp; Pesanan</h2>
                <p>Menampilkan <strong><?= count($bills); ?></strong> transaksi berdasarkan filter saat ini</p>
            </div>
            <div class="card-header-actions">
                <a href="../menu.php" target="_blank" class="btn-admin btn-admin-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Input Pesanan Baru</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="admin-filter-bar">
            <form action="bills.php" method="GET" class="admin-search-wrap">
                <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" class="admin-search-input" placeholder="Cari No. Bill, Nama, atau No. HP..." value="<?= htmlspecialchars($search_q); ?>">
                <button type="submit" style="background:var(--admin-primary);color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:0.8rem;font-weight:700;cursor:pointer;">Cari</button>
            </form>

            <div class="filter-pills-scroll">
                <?php foreach ($pills as $key => $label): ?>
                    <a href="bills.php?status=<?= urlencode($key); ?><?= !empty($search_q) ? '&q='.urlencode($search_q) : ''; ?>"
                       class="filter-pill <?= ($filter_status === $key) ? 'active' : ''; ?>">
                        <?= $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>No. Tagihan</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status Bayar</th>
                        <th>Status Dapur</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding:50px 20px; color:var(--text-muted);">
                                Tidak ada tagihan yang sesuai filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--text-main);display:block;font-size:0.85rem;"><?= htmlspecialchars($b['bill_number']); ?></strong>
                                    <span style="font-size:0.72rem;color:var(--text-muted);"><?= htmlspecialchars($b['receipt_id']); ?></span>
                                </td>
                                <td>
                                    <span style="font-size:0.82rem;font-weight:600;"><?= date('d/m/Y', strtotime($b['created_at'])); ?></span>
                                    <span style="display:block;font-size:0.72rem;color:var(--text-muted);"><?= date('H:i', strtotime($b['created_at'])); ?> WIB</span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['customer_name']); ?></strong>
                                    <span style="display:block;font-size:0.72rem;color:var(--text-muted);"><?= htmlspecialchars($b['customer_phone']); ?></span>
                                </td>
                                <td>
                                    <span style="font-size:0.78rem;font-weight:800;text-transform:uppercase;"><?= htmlspecialchars($b['order_type']); ?></span>
                                </td>
                                <td>
                                    <strong><?= formatRupiah($b['grand_total']); ?></strong>
                                </td>
                                <td>
                                    <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;background:#f1f5f9;padding:3px 8px;border-radius:4px;">
                                        <?= htmlspecialchars($b['payment_method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['payment_status'] === 'paid'): ?>
                                        <span class="badge-status paid">Lunas</span>
                                    <?php elseif ($b['payment_status'] === 'cancelled'): ?>
                                        <span class="badge-status cancelled">Batal</span>
                                    <?php else: ?>
                                        <span class="badge-status unpaid">Belum Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="bills.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="bill_id" value="<?= (int)$b['id']; ?>">
                                        <select name="order_status" onchange="this.form.submit()"
                                            style="padding:4px 6px;font-size:0.75rem;font-weight:700;border-radius:6px;border:1px solid var(--border-ui);background:#fff;cursor:pointer;">
                                            <option value="pending"    <?= $b['order_status']==='pending'    ? 'selected':'' ?>>Pending</option>
                                            <option value="cooking"    <?= $b['order_status']==='cooking'    ? 'selected':'' ?>>Dimasak</option>
                                            <option value="delivering" <?= $b['order_status']==='delivering' ? 'selected':'' ?>>Diantar</option>
                                            <option value="completed"  <?= $b['order_status']==='completed'  ? 'selected':'' ?>>Selesai</option>
                                            <option value="cancelled"  <?= $b['order_status']==='cancelled'  ? 'selected':'' ?>>Batal</option>
                                        </select>
                                    </form>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:flex-end;">

                                        <?php if ($b['payment_status'] === 'unpaid'): ?>
                                        <form action="bills.php" method="POST"
                                            onsubmit="return confirm('Konfirmasi pelunasan tagihan <?= htmlspecialchars(addslashes($b['bill_number']), ENT_QUOTES); ?>?');">
                                            <input type="hidden" name="action"  value="mark_paid">
                                            <input type="hidden" name="bill_id" value="<?= (int)$b['id']; ?>">
                                            <button type="submit" class="btn-admin btn-admin-success"
                                                style="padding:5px 10px;font-size:0.75rem;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>Bayar Lunas</span>
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                        <a href="bill-detail.php?id=<?= (int)$b['id']; ?>"
                                           class="btn-admin btn-admin-secondary"
                                           style="padding:5px 10px;font-size:0.75rem;">
                                            <span>Rincian</span>
                                        </a>

                                        <a href="print-bill.php?id=<?= (int)$b['id']; ?>" target="_blank"
                                           class="btn-action-icon print" title="Cetak Nota">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
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
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// POST handler HARUS di paling atas sebelum include header (yang output HTML)
require_once __DIR__ . '/../includes/data.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$bill_id = (int) ($_GET['id'] ?? 0);
if ($bill_id <= 0) {
    header('Location: bills.php');
    exit;
}

// Handler Update Status & Catatan Kasir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_bill_update') {
    $new_pay_status   = $_POST['payment_status'] ?? 'unpaid';
    $new_order_status = $_POST['order_status'] ?? 'pending';
    $cashier_notes    = trim($_POST['cashier_notes'] ?? '');
    $amount_paid      = (int) ($_POST['amount_paid'] ?? 0);
    $change_amount    = (int) ($_POST['change_amount'] ?? 0);

    $paid_at_clause = ($new_pay_status === 'paid') ? date('Y-m-d H:i:s') : null;

    try {
        $updStmt = $pdo->prepare("
            UPDATE bills
            SET payment_status = ?, order_status = ?, cashier_notes = ?,
                amount_paid = ?, change_amount = ?, paid_at = COALESCE(paid_at, ?),
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $updStmt->execute([
            $new_pay_status,
            $new_order_status,
            $cashier_notes,
            $amount_paid,
            $change_amount,
            $paid_at_clause,
            $bill_id
        ]);

        $bRow = $pdo->prepare("SELECT order_id FROM bills WHERE id = ?");
        $bRow->execute([$bill_id]);
        $orderRow = $bRow->fetch();
        if ($orderRow) {
            $payText = ($new_pay_status === 'paid') ? 'Lunas (Kasir Terverifikasi)' : 'Menunggu Pembayaran';
            $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?")
                ->execute([$payText, $orderRow['order_id']]);
        }

        $_SESSION['flash_msg'] = 'Perubahan tagihan & status berhasil disimpan!';
    } catch (Exception $e) {
        $_SESSION['flash_msg_error'] = 'Gagal menyimpan: ' . $e->getMessage();
    }

    header('Location: bill-detail.php?id=' . $bill_id);
    exit;
}

// Setelah semua redirect selesai, baru include header yang output HTML
$page_title = 'Rincian Tagihan & Faktur';
require_once __DIR__ . '/includes/header.php';

// Ambil Data Bill & Order
$stmt = $pdo->prepare("
    SELECT b.*, o.delivery_address, o.table_no, o.notes as customer_notes, o.discount_code, o.cash_amount
    FROM bills b
    LEFT JOIN orders o ON b.order_id = o.id
    WHERE b.id = ?
");
$stmt->execute([$bill_id]);
$bill = $stmt->fetch();

if (!$bill) {
    echo "<main class='admin-content'><div class='admin-card' style='padding: 40px; text-align: center;'>Tagihan tidak ditemukan. <a href='bills.php'>Kembali</a></div></main>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Ambil Item Pesanan
$itemsStmt = $pdo->prepare("
    SELECT oi.*, m.image_url 
    FROM order_items oi
    LEFT JOIN menu_items m ON oi.menu_id = m.id
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$bill['order_id']]);
$items = $itemsStmt->fetchAll();
?>

<main class="admin-content">
    <!-- Header Card -->
    <div class="admin-card" style="margin-bottom: 24px;">
        <div class="admin-card-header">
            <div>
                <a href="bills.php" style="font-size: 0.8rem; font-weight: 700; color: var(--admin-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 8px;">
                    &larr; Kembali ke Daftar Tagihan
                </a>
                <h2>Faktur Tagihan: <?= htmlspecialchars($bill['bill_number']); ?></h2>
                <p>No. Struk: <strong><?= htmlspecialchars($bill['receipt_id']); ?></strong> &bull; Dibuat pada: <?= date('d M Y, H:i', strtotime($bill['created_at'])); ?> WIB</p>
            </div>
            <div class="card-header-actions">
                <a href="print-bill.php?id=<?= $bill['id']; ?>" target="_blank" class="btn-admin btn-admin-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Cetak Nota Kasir</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2 Column Grid -->
    <div class="bill-detail-grid">
        <!-- Left: Order Items & Breakdown -->
        <div>
            <!-- Item List Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h3>Daftar Menu yang Dipesan</h3>
                        <p><?= count($items); ?> jenis hidangan</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="bill-items-table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th style="text-align: right;">Harga Satuan</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td>
                                        <div class="item-name-cell">
                                            <?php if (!empty($it['image_url'])): ?>
                                                <img src="../<?= htmlspecialchars($it['image_url']); ?>" alt="<?= htmlspecialchars($it['menu_name']); ?>" class="item-thumb-micro">
                                            <?php endif; ?>
                                            <div>
                                                <strong style="color: var(--text-main); display: block;"><?= htmlspecialchars($it['menu_name']); ?></strong>
                                                <?php if (!empty($it['note'])): ?>
                                                    <span style="font-size: 0.78rem; color: #ea580c; display: block;">Catatan: <?= htmlspecialchars($it['note']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right;"><?= formatRupiah($it['price']); ?></td>
                                    <td style="text-align: center;"><strong><?= $it['qty']; ?></strong></td>
                                    <td style="text-align: right; font-weight: 700;"><?= formatRupiah($it['subtotal']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Breakdown -->
                <div class="bill-summary-list">
                    <div class="bill-summary-row">
                        <span>Subtotal Hidangan</span>
                        <strong><?= formatRupiah($bill['subtotal']); ?></strong>
                    </div>

                    <?php if ($bill['discount_amount'] > 0): ?>
                        <div class="bill-summary-row" style="color: #16a34a;">
                            <span>Diskon Promo <?= !empty($bill['discount_code']) ? '(' . htmlspecialchars($bill['discount_code']) . ')' : ''; ?></span>
                            <strong>-<?= formatRupiah($bill['discount_amount']); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div class="bill-summary-row">
                        <span>Biaya Kemasan Higienis</span>
                        <strong><?= formatRupiah($bill['service_fee']); ?></strong>
                    </div>

                    <div class="bill-summary-row">
                        <span>Ongkos Kirim (<?= ucfirst($bill['order_type']); ?>)</span>
                        <strong><?= formatRupiah($bill['delivery_fee']); ?></strong>
                    </div>

                    <div class="bill-summary-row total">
                        <span>Total Tagihan (Grand Total)</span>
                        <span style="color: var(--admin-primary); font-size: 1.35rem;"><?= formatRupiah($bill['grand_total']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Info Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h3>Informasi Pelanggan & Pengantaran</h3>
                    </div>
                </div>
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Nama Pemesan</span>
                            <div style="font-size: 1rem; font-weight: 700;"><?= htmlspecialchars($bill['customer_name']); ?></div>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Nomor WhatsApp / HP</span>
                            <div style="font-size: 1rem; font-weight: 700;">
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $bill['customer_phone']); ?>" target="_blank" style="color: #16a34a; text-decoration: none;">
                                    <?= htmlspecialchars($bill['customer_phone']); ?> &rarr;
                                </a>
                            </div>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Tipe Layanan</span>
                            <div style="font-size: 0.95rem; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($bill['order_type']); ?></div>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Metode Pembayaran</span>
                            <div style="font-size: 0.95rem; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($bill['payment_method']); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($bill['delivery_address'])): ?>
                        <div style="margin-bottom: 12px; background: #f8fafc; padding: 12px; border-radius: 8px;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Alamat Pengantaran</span>
                            <p style="font-size: 0.9rem;"><?= nl2br(htmlspecialchars($bill['delivery_address'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($bill['customer_notes'])): ?>
                        <div style="background: #fff7ed; border-left: 4px solid #ea580c; padding: 12px; border-radius: 6px;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #c2410c; text-transform: uppercase; display: block; margin-bottom: 4px;">Catatan Tambahan Pelanggan</span>
                            <p style="font-size: 0.88rem; color: #9a3412;"><?= nl2br(htmlspecialchars($bill['customer_notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Status Control & Cashier Operations -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <div class="card-heading-group">
                        <h3>Kelola Status & Kasir</h3>
                    </div>
                </div>

                <div style="padding: 20px;">
                    <form action="bill-detail.php?id=<?= $bill['id']; ?>" method="POST">
                        <input type="hidden" name="action" value="save_bill_update">

                        <!-- Status Pembayaran -->
                        <div class="form-group-admin">
                            <label for="payment_status">Status Pembayaran Tagihan</label>
                            <select id="payment_status" name="payment_status" class="form-control-admin" style="font-weight: 700;">
                                <option value="unpaid" <?= ($bill['payment_status'] === 'unpaid') ? 'selected' : ''; ?>>Belum Lunas (Unpaid)</option>
                                <option value="paid" <?= ($bill['payment_status'] === 'paid') ? 'selected' : ''; ?>>Lunas (Paid)</option>
                                <option value="cancelled" <?= ($bill['payment_status'] === 'cancelled') ? 'selected' : ''; ?>>Dibatalkan (Cancelled)</option>
                                <option value="refunded" <?= ($bill['payment_status'] === 'refunded') ? 'selected' : ''; ?>>Dikembalikan (Refunded)</option>
                            </select>
                        </div>

                        <!-- Status Dapur & Pesanan -->
                        <div class="form-group-admin">
                            <label for="order_status">Status Proses Dapur</label>
                            <select id="order_status" name="order_status" class="form-control-admin" style="font-weight: 700;">
                                <option value="pending" <?= ($bill['order_status'] === 'pending') ? 'selected' : ''; ?>>Pending (Menunggu Konfirmasi)</option>
                                <option value="cooking" <?= ($bill['order_status'] === 'cooking') ? 'selected' : ''; ?>>Sedang Dimasak di Dapur</option>
                                <option value="delivering" <?= ($bill['order_status'] === 'delivering') ? 'selected' : ''; ?>>Sedang Diantar / Siap Ambil</option>
                                <option value="completed" <?= ($bill['order_status'] === 'completed') ? 'selected' : ''; ?>>Pesanan Selesai</option>
                                <option value="cancelled" <?= ($bill['order_status'] === 'cancelled') ? 'selected' : ''; ?>>Pesanan Dibatalkan</option>
                            </select>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group-admin">
                                <label for="amount_paid">Uang Diterima (Rp)</label>
                                <input type="number" id="amount_paid" name="amount_paid" class="form-control-admin" value="<?= $bill['amount_paid'] ?: $bill['grand_total']; ?>">
                            </div>
                            <div class="form-group-admin">
                                <label for="change_amount">Kembalian (Rp)</label>
                                <input type="number" id="change_amount" name="change_amount" class="form-control-admin" value="<?= $bill['change_amount']; ?>">
                            </div>
                        </div>

                        <!-- Catatan Internal Kasir -->
                        <div class="form-group-admin">
                            <label for="cashier_notes">Catatan Internal Kasir</label>
                            <textarea id="cashier_notes" name="cashier_notes" rows="4" class="form-control-admin" placeholder="Contoh: Pembayaran tunai pas, diserahkan ke kurir Anton."><?= htmlspecialchars($bill['cashier_notes'] ?? ''); ?></textarea>
                        </div>

                        <?php if ($bill['paid_at']): ?>
                            <div style="font-size: 0.78rem; color: #16a34a; margin-bottom: 16px; font-weight: 600;">
                                &bull; Waktu Pelunasan: <?= date('d M Y H:i', strtotime($bill['paid_at'])); ?>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Simpan Perubahan Tagihan</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Print Action Box -->
            <div class="admin-card" style="padding: 20px; text-align: center;">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">
                    Cetak faktur resmi ini ke printer thermal kasir (80mm) atau printer biasa.
                </p>
                <a href="print-bill.php?id=<?= $bill['id']; ?>" target="_blank" class="btn-admin btn-admin-secondary" style="width: 100%; justify-content: center; padding: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Cetak Faktur (Print View)</span>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

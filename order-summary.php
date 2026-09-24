<?php
$page_title = 'Total Pesanan & Ringkasan';
require_once __DIR__ . '/includes/data.php';

// Validasi jika keranjang kosong
if (empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$order_data = $_SESSION['order_data'];
$cart_items = $_SESSION['cart'];

$subtotal = getCartSubtotal();
$delivery_fee = getDeliveryFee();
$service_fee = getServiceFee();
$discount = getDiscountAmount();
$grand_total = getCartGrandTotal();

require_once __DIR__ . '/includes/header.php';
?>

<main class="order-flow-page">
    <div class="container">
        <!-- Order Progress Stepper -->
        <nav class="order-stepper" aria-label="Tahapan Pemesanan">
            <div class="step-item completed">
                <span class="step-badge">✓</span>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step-divider completed"></div>
            <div class="step-item completed">
                <span class="step-badge">✓</span>
                <span class="step-label">Pengiriman</span>
            </div>
            <div class="step-divider completed"></div>
            <div class="step-item active">
                <span class="step-badge">3</span>
                <span class="step-label">Total Pesanan</span>
            </div>
            <div class="step-divider"></div>
            <div class="step-item">
                <span class="step-badge">4</span>
                <span class="step-label">Pembayaran</span>
            </div>
            <div class="step-divider"></div>
            <div class="step-item">
                <span class="step-badge">5</span>
                <span class="step-label">Struk</span>
            </div>
        </nav>

        <h1 class="page-main-heading">Verifikasi Rincian & Total Pesanan</h1>

        <div class="summary-verification-grid">
            <!-- Left: Order Details & Destination -->
            <div class="verification-details-col">
                <!-- Delivery & Customer Information Card -->
                <div class="card-box mb-4">
                    <div class="card-box-header flex-between">
                        <h3>
                            <span class="badge-icon-svg">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </span>
                            <span>Tujuan & Informasi Pemesan</span>
                        </h3>
                        <a href="checkout.php" class="link-edit">Ubah Data</a>
                    </div>

                    <div class="details-info-grid">
                        <div class="info-block">
                            <span class="info-label">Nama Pemesan</span>
                            <span class="info-value"><?= htmlspecialchars($order_data['customer_name'] ?: 'Pelanggan'); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="info-label">Nomor WhatsApp</span>
                            <span class="info-value"><?= htmlspecialchars($order_data['customer_phone'] ?: '-'); ?></span>
                        </div>
                        <div class="info-block">
                            <span class="info-label">Tipe Layanan</span>
                            <span class="info-value service-highlight">
                                <?php
                                if ($order_data['order_type'] === 'delivery') echo 'Pesan Antar (Delivery)';
                                elseif ($order_data['order_type'] === 'takeaway') echo 'Bungkus Sendiri (Takeaway)';
                                else echo 'Makan di Tempat (Dine-In)';
                                ?>
                            </span>
                        </div>

                        <?php if ($order_data['order_type'] === 'delivery'): ?>
                            <div class="info-block full-width">
                                <span class="info-label">Alamat Pengantaran</span>
                                <span class="info-value"><?= nl2br(htmlspecialchars($order_data['delivery_address'] ?: 'Alamat belum diisi lengkap')); ?></span>
                            </div>
                        <?php elseif ($order_data['order_type'] === 'dinein'): ?>
                            <div class="info-block">
                                <span class="info-label">Nomor Meja</span>
                                <span class="info-value highlight-bold"><?= htmlspecialchars($order_data['table_no'] ?: 'Belum diisi'); ?></span>
                            </div>
                        <?php elseif ($order_data['order_type'] === 'takeaway'): ?>
                            <div class="info-block">
                                <span class="info-label">Waktu Ambil</span>
                                <span class="info-value"><?= htmlspecialchars($order_data['pickup_time'] ?: 'Segera saat matang'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($order_data['notes'])): ?>
                            <div class="info-block full-width">
                                <span class="info-label">Catatan Tambahan</span>
                                <span class="info-value text-muted-note"><?= htmlspecialchars($order_data['notes']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Itemized Food Breakdown with Real Photo Thumbnails -->
                <div class="card-box">
                    <div class="card-box-header flex-between">
                        <h3>
                            <span class="badge-icon-svg">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                            </span>
                            <span>Rincian Menu yang Dipesan</span>
                        </h3>
                        <a href="cart.php" class="link-edit">Ubah Menu</a>
                    </div>

                    <div class="table-responsive">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $id => $entry): ?>
                                    <?php 
                                    if (!isset($menu_items[$id])) continue;
                                    $item = $menu_items[$id];
                                    $qty = $entry['qty'];
                                    $item_sub = $item['price'] * $qty;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="table-food-item">
                                                <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="table-food-thumb" loading="lazy">
                                                <div>
                                                    <strong class="food-title"><?= htmlspecialchars($item['name']); ?></strong>
                                                    <?php if (!empty($entry['note'])): ?>
                                                        <small class="item-note">Catatan: <?= htmlspecialchars($entry['note']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= formatRupiah($item['price']); ?></td>
                                        <td><?= $qty; ?></td>
                                        <td class="text-right font-weight-bold"><?= formatRupiah($item_sub); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Cost Calculation & Action Card -->
            <aside class="verification-summary-col">
                <div class="summary-card sticky-card">
                    <h3 class="summary-card-title">Total Pembayaran Akhir</h3>

                    <div class="summary-breakdown">
                        <div class="summary-line">
                            <span>Subtotal Menu (<?= getCartCount(); ?> item)</span>
                            <span><?= formatRupiah($subtotal); ?></span>
                        </div>

                        <?php if ($discount > 0): ?>
                            <div class="summary-line line-discount">
                                <span>Diskon Promo (<?= $_SESSION['discount_code']; ?>)</span>
                                <span>-<?= formatRupiah($discount); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="summary-line">
                            <span>Ongkos Kirim</span>
                            <span><?= ($delivery_fee === 0) ? '<strong class="text-success">Gratis</strong>' : formatRupiah($delivery_fee); ?></span>
                        </div>

                        <div class="summary-line">
                            <span>Kemasan Ramah Lingkungan</span>
                            <span><?= formatRupiah($service_fee); ?></span>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-line line-grand-total">
                            <div>
                                <span class="total-tag">TOTAL PESANAN</span>
                                <small class="text-muted d-block">Sudah termasuk pajak & kemasan</small>
                            </div>
                            <span class="grand-price highlight-large"><?= formatRupiah($grand_total); ?></span>
                        </div>
                    </div>

                    <div class="summary-actions mt-4">
                        <a href="payment.php" class="btn btn-primary btn-block btn-lg">
                            <span>Lanjut Pilih Pembayaran</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                        <a href="checkout.php" class="btn btn-outline btn-block mt-2">&larr; Kembali ke Data Pengiriman</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

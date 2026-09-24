<?php
$page_title = 'Keranjang Belanja';
require_once __DIR__ . '/includes/data.php';

$cart_items = $_SESSION['cart'] ?? [];
$subtotal = getCartSubtotal();
$discount = getDiscountAmount();
$service_fee = getServiceFee();
$grand_total = getCartGrandTotal();

require_once __DIR__ . '/includes/header.php';
?>

<main class="order-flow-page">
    <div class="container">
        <!-- Order Progress Stepper -->
        <nav class="order-stepper" aria-label="Tahapan Pemesanan">
            <div class="step-item active">
                <span class="step-badge">1</span>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step-divider"></div>
            <div class="step-item">
                <span class="step-badge">2</span>
                <span class="step-label">Pengiriman</span>
            </div>
            <div class="step-divider"></div>
            <div class="step-item">
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

        <h1 class="page-main-heading">Keranjang Pesanan Anda</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-state-card text-center">
                <div class="empty-icon-svg-lg">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="1.6"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <h2>Keranjang Masih Kosong</h2>
                <p>Anda belum menambahkan makanan atau minuman ke keranjang belanja.</p>
                <div class="empty-actions">
                    <a href="menu.php" class="btn btn-primary btn-lg">Pilih Menu Lezat Sekarang</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-layout-grid">
                <!-- Cart Items List Column -->
                <div class="cart-items-column">
                    <div class="cart-card-header">
                        <h2>Daftar Menu Dipilih (<?= count($cart_items); ?> jenis)</h2>
                        <a href="menu.php" class="link-add-more">+ Tambah Menu Lain</a>
                    </div>

                    <div class="cart-item-list">
                        <?php foreach ($cart_items as $id => $cart_entry): ?>
                            <?php 
                            if (!isset($menu_items[$id])) continue;
                            $item = $menu_items[$id];
                            $qty = $cart_entry['qty'];
                            $note = $cart_entry['note'] ?? '';
                            $item_subtotal = $item['price'] * $qty;
                            ?>
                            <div class="cart-item-card">
                                <div class="cart-item-photo-wrap">
                                    <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="cart-item-thumb" loading="lazy">
                                </div>
                                
                                <div class="cart-item-info">
                                    <div class="cart-item-header">
                                        <h3 class="cart-item-title"><?= htmlspecialchars($item['name']); ?></h3>
                                        <form action="includes/data.php" method="POST" class="remove-form">
                                            <input type="hidden" name="action" value="remove_item">
                                            <input type="hidden" name="item_id" value="<?= $id; ?>">
                                            <button type="submit" class="btn-remove" title="Hapus dari keranjang">&times;</button>
                                        </form>
                                    </div>
                                    <span class="cart-item-unit-price"><?= formatRupiah($item['price']); ?> / porsi</span>

                                    <!-- Update Quantity & Note Form -->
                                    <form action="includes/data.php" method="POST" class="cart-item-controls">
                                        <input type="hidden" name="action" value="update_cart">
                                        <input type="hidden" name="item_id" value="<?= $id; ?>">

                                        <div class="qty-control-box">
                                            <button type="button" class="btn-qty" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.form.submit();">-</button>
                                            <input type="number" name="qty" value="<?= $qty; ?>" min="1" max="50" onchange="this.form.submit();">
                                            <button type="button" class="btn-qty" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.form.submit();">+</button>
                                        </div>

                                        <div class="note-input-box">
                                            <input type="text" name="note" value="<?= htmlspecialchars($note); ?>" placeholder="Catatan selera (cth: tanpa sambal, es sedikit)" onchange="this.form.submit();">
                                        </div>

                                        <div class="cart-item-price-sum">
                                            <?= formatRupiah($item_subtotal); ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cart Order Summary Sidebar -->
                <aside class="cart-summary-column">
                    <div class="summary-card">
                        <h3 class="summary-card-title">Ringkasan Biaya</h3>

                        <!-- Kupon Diskon -->
                        <form action="includes/data.php" method="POST" class="coupon-box">
                            <input type="hidden" name="action" value="apply_coupon">
                            <label for="coupon_code" class="coupon-label">Punya Kupon Promo?</label>
                            <div class="coupon-input-group">
                                <input type="text" id="coupon_code" name="coupon_code" placeholder="Gunakan: INAHEMAT" value="<?= htmlspecialchars($_SESSION['discount_code'] ?? ''); ?>">
                                <button type="submit" class="btn btn-outline btn-sm">Gunakan</button>
                            </div>
                            <?php if (!empty($_SESSION['discount_code'])): ?>
                                <small class="text-success font-weight-bold">Kupon <strong><?= htmlspecialchars($_SESSION['discount_code']); ?></strong> aktif (Diskon <?= $_SESSION['discount']; ?>%)</small>
                            <?php endif; ?>
                        </form>

                        <div class="summary-breakdown">
                            <div class="summary-line">
                                <span>Subtotal Menu</span>
                                <span><?= formatRupiah($subtotal); ?></span>
                            </div>

                            <?php if ($discount > 0): ?>
                                <div class="summary-line line-discount">
                                    <span>Diskon Kupon (<?= $_SESSION['discount']; ?>%)</span>
                                    <span>-<?= formatRupiah($discount); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="summary-line">
                                <span>Kemasan Ramah Lingkungan</span>
                                <span><?= formatRupiah($service_fee); ?></span>
                            </div>

                            <div class="summary-line line-note">
                                <span>Ongkos Kirim</span>
                                <span>Dihitung di langkah berikutnya</span>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-line line-grand-total">
                                <span>Estimasi Total</span>
                                <span class="grand-price"><?= formatRupiah($grand_total); ?></span>
                            </div>
                        </div>

                        <div class="summary-actions">
                            <a href="checkout.php" class="btn btn-primary btn-block btn-lg">
                                <span>Lanjut ke Pengiriman (Checkout)</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                            <a href="menu.php" class="btn btn-outline btn-block mt-2">Tambah Menu Lain</a>
                        </div>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

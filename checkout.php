<?php
$page_title = 'Pengiriman & Checkout';
require_once __DIR__ . '/includes/data.php';

// Jika keranjang kosong, kembalikan ke menu
if (empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$order_data = $_SESSION['order_data'];
$subtotal = getCartSubtotal();
$discount = getDiscountAmount();

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
            <div class="step-item active">
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

        <h1 class="page-main-heading">Data Pemesan & Metode Pengiriman</h1>

        <form action="includes/data.php" method="POST" class="checkout-layout-grid" id="checkout-form">
            <input type="hidden" name="action" value="save_checkout">

            <!-- Left Form Column -->
            <div class="checkout-form-column">
                <!-- 1. Tipe Layanan Pesanan -->
                <div class="form-section-card">
                    <h2 class="form-section-title">
                        <span class="section-icon-svg">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
                        </span>
                        <span>1. Pilih Tipe Pesanan</span>
                    </h2>

                    <div class="service-type-selector">
                        <label class="service-type-option <?= ($order_data['order_type'] === 'delivery') ? 'selected' : ''; ?>">
                            <input type="radio" name="order_type" value="delivery" <?= ($order_data['order_type'] === 'delivery') ? 'checked' : ''; ?> onchange="updateOrderTypeView(this.value)">
                            <div class="option-icon-svg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
                            </div>
                            <div class="option-meta">
                                <strong>Pesan Antar (Delivery)</strong>
                                <p>Diantar hangat ke rumah atau kantor Anda (Gratis ongkir &ge; Rp 100rb)</p>
                            </div>
                        </label>

                        <label class="service-type-option <?= ($order_data['order_type'] === 'takeaway') ? 'selected' : ''; ?>">
                            <input type="radio" name="order_type" value="takeaway" <?= ($order_data['order_type'] === 'takeaway') ? 'checked' : ''; ?> onchange="updateOrderTypeView(this.value)">
                            <div class="option-icon-svg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            </div>
                            <div class="option-meta">
                                <strong>Bungkus (Takeaway)</strong>
                                <p>Ambil langsung pesanan di Dapur Ina Aina tanpa antre</p>
                            </div>
                        </label>

                        <label class="service-type-option <?= ($order_data['order_type'] === 'dinein') ? 'selected' : ''; ?>">
                            <input type="radio" name="order_type" value="dinein" <?= ($order_data['order_type'] === 'dinein') ? 'checked' : ''; ?> onchange="updateOrderTypeView(this.value)">
                            <div class="option-icon-svg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                            </div>
                            <div class="option-meta">
                                <strong>Makan di Tempat (Dine-In)</strong>
                                <p>Pesan langsung dari meja Anda di restoran kami</p>
                            </div>
                        </label>
                    </div>

                    <!-- Input Khusus Dine In (No Meja) -->
                    <div id="dinein-fields" class="conditional-fields" style="display: <?= ($order_data['order_type'] === 'dinein') ? 'block' : 'none'; ?>;">
                        <div class="form-group mt-3">
                            <label for="table_no">Nomor Meja Restoran <span class="required">*</span></label>
                            <input type="text" id="table_no" name="table_no" placeholder="cth. Meja 05" value="<?= htmlspecialchars($order_data['table_no']); ?>">
                            <small class="form-hint">Nomor tertera pada stiker meja Dapur Ina Aina.</small>
                        </div>
                    </div>

                    <!-- Input Khusus Takeaway (Jam Ambil) -->
                    <div id="takeaway-fields" class="conditional-fields" style="display: <?= ($order_data['order_type'] === 'takeaway') ? 'block' : 'none'; ?>;">
                        <div class="form-group mt-3">
                            <label for="pickup_time">Perkiraan Waktu Pengambilan</label>
                            <input type="text" id="pickup_time" name="pickup_time" placeholder="cth. 12:30 WIB atau 30 menit lagi" value="<?= htmlspecialchars($order_data['pickup_time']); ?>">
                        </div>
                    </div>
                </div>

                <!-- 2. Informasi Penerima -->
                <div class="form-section-card">
                    <h2 class="form-section-title">
                        <span class="section-icon-svg">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <span>2. Identitas Pemesan</span>
                    </h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name">Nama Lengkap Pemesan <span class="required">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" placeholder="cth. Ibu Ratna Sari" value="<?= htmlspecialchars($order_data['customer_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_phone">Nomor WhatsApp Aktif <span class="required">*</span></label>
                            <input type="tel" id="customer_phone" name="customer_phone" placeholder="cth. 081234567890" value="<?= htmlspecialchars($order_data['customer_phone']); ?>" required>
                            <small class="form-hint">Digunakan kurir untuk konfirmasi kedatangan & nota digital.</small>
                        </div>
                    </div>
                </div>

                <!-- 3. Alamat Pengantaran (Jika Delivery) -->
                <div class="form-section-card" id="delivery-fields" style="display: <?= ($order_data['order_type'] === 'delivery') ? 'block' : 'none'; ?>;">
                    <h2 class="form-section-title">
                        <span class="section-icon-svg">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </span>
                        <span>3. Alamat Pengantaran Lengkap</span>
                    </h2>

                    <div class="form-group">
                        <label for="delivery_address">Alamat Pengiriman & Titik Patokan <span class="required">*</span></label>
                        <textarea id="delivery_address" name="delivery_address" rows="3" placeholder="Tuliskan nama jalan, nomor rumah, blok, RT/RW, dan patokan (cth: Rumah pagar putih depan masjid)..."><?= htmlspecialchars($order_data['delivery_address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan Tambahan untuk Kurir / Dapur</label>
                        <input type="text" id="notes" name="notes" placeholder="cth. Titip di pos satpam, atau tolong siapkan sendok ekstra" value="<?= htmlspecialchars($order_data['notes']); ?>">
                    </div>
                </div>
            </div>

            <!-- Right Column: Brief Cart Recap -->
            <aside class="checkout-summary-column">
                <div class="summary-card">
                    <h3 class="summary-card-title">Ringkasan Pesanan</h3>

                    <div class="checkout-items-preview">
                        <?php foreach ($_SESSION['cart'] as $id => $entry): ?>
                            <?php if (isset($menu_items[$id])): ?>
                                <div class="checkout-item-row">
                                    <span class="item-name"><?= $entry['qty']; ?>x <?= htmlspecialchars($menu_items[$id]['name']); ?></span>
                                    <span class="item-sum"><?= formatRupiah($menu_items[$id]['price'] * $entry['qty']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-breakdown mt-3">
                        <div class="summary-line">
                            <span>Subtotal Menu</span>
                            <span><?= formatRupiah($subtotal); ?></span>
                        </div>

                        <?php if ($discount > 0): ?>
                            <div class="summary-line line-discount">
                                <span>Diskon Promo</span>
                                <span>-<?= formatRupiah($discount); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="summary-line">
                            <span>Biaya Layanan & Kemasan</span>
                            <span><?= formatRupiah(getServiceFee()); ?></span>
                        </div>

                        <div class="summary-line" id="delivery-fee-line">
                            <span>Ongkos Kirim</span>
                            <span id="delivery-fee-val"><?= ($order_data['order_type'] === 'delivery') ? formatRupiah(getDeliveryFee()) : 'Gratis'; ?></span>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-line line-grand-total">
                            <span>Perkiraan Total</span>
                            <span class="grand-price"><?= formatRupiah(getCartGrandTotal()); ?></span>
                        </div>
                    </div>

                    <div class="summary-actions">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <span>Lanjut ke Total Pesanan</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                        <a href="cart.php" class="btn btn-outline btn-block mt-2">&larr; Kembali ke Keranjang</a>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</main>

<script>
function updateOrderTypeView(type) {
    document.querySelectorAll('.service-type-option').forEach(el => el.classList.remove('selected'));
    event.target.closest('.service-type-option').classList.add('selected');

    const deliveryBox = document.getElementById('delivery-fields');
    const dineinBox = document.getElementById('dinein-fields');
    const takeawayBox = document.getElementById('takeaway-fields');
    const deliveryFeeVal = document.getElementById('delivery-fee-val');

    deliveryBox.style.display = (type === 'delivery') ? 'block' : 'none';
    dineinBox.style.display = (type === 'dinein') ? 'block' : 'none';
    takeawayBox.style.display = (type === 'takeaway') ? 'block' : 'none';

    if (type === 'delivery') {
        deliveryFeeVal.innerText = '<?= ($subtotal >= 100000) ? "Gratis" : formatRupiah(10000); ?>';
    } else {
        deliveryFeeVal.innerText = 'Gratis';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

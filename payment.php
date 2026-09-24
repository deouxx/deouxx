<?php
$page_title = 'Pilih Metode Pembayaran';
require_once __DIR__ . '/includes/data.php';

// Validasi jika keranjang kosong
if (empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$grand_total = getCartGrandTotal();
$order_data = $_SESSION['order_data'];

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
            <div class="step-item completed">
                <span class="step-badge">✓</span>
                <span class="step-label">Total Pesanan</span>
            </div>
            <div class="step-divider completed"></div>
            <div class="step-item active">
                <span class="step-badge">4</span>
                <span class="step-label">Pembayaran</span>
            </div>
            <div class="step-divider"></div>
            <div class="step-item">
                <span class="step-badge">5</span>
                <span class="step-label">Struk</span>
            </div>
        </nav>

        <h1 class="page-main-heading">Pilih Metode Pembayaran</h1>

        <form action="includes/data.php" method="POST" class="payment-layout-grid" id="payment-form">
            <input type="hidden" name="action" value="finish_payment">

            <!-- Left: Payment Options Selection -->
            <div class="payment-methods-col">
                <!-- Option 1: TUNAI (CASH) -->
                <div class="payment-option-card <?= ($order_data['payment_method'] === 'cash') ? 'selected' : ''; ?>" id="opt-cash">
                    <label class="payment-header-label">
                        <input type="radio" name="payment_method" value="cash" <?= ($order_data['payment_method'] === 'cash') ? 'checked' : ''; ?> onchange="switchPaymentTab('cash')">
                        <div class="payment-label-info">
                            <span class="payment-icon-svg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="3"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                            </span>
                            <div>
                                <strong class="payment-title">Pembayaran Tunai (Cash / COD)</strong>
                                <p class="payment-desc">Bayar tunai langsung saat pesanan diantar (COD) atau langsung di kasir Dapur Ina Aina.</p>
                            </div>
                        </div>
                        <span class="badge-tag green">Praktis</span>
                    </label>

                    <div class="payment-details-body" id="body-cash" style="display: <?= ($order_data['payment_method'] === 'cash') ? 'block' : 'none'; ?>;">
                        <p class="cash-instruction">
                            Silakan siapkan uang tunai sejumlah <strong><?= formatRupiah($grand_total); ?></strong> saat kurir tiba atau di meja kasir.
                        </p>
                        <div class="form-group mt-3">
                            <label for="cash_amount">Pecahan Uang yang Disiapkan (Opsional):</label>
                            <input type="text" id="cash_amount" name="cash_amount" placeholder="cth. Uang Pas atau Rp 100.000 (Kurir siapkan kembalian)">
                            <small class="form-hint">Membantu kurir kami menyediakan uang kembalian yang tepat.</small>
                        </div>
                    </div>
                </div>

                <!-- Option 2: DEBIT / KARTU KREDIT & NON-TUNAI -->
                <div class="payment-option-card <?= ($order_data['payment_method'] === 'card') ? 'selected' : ''; ?>" id="opt-card">
                    <label class="payment-header-label">
                        <input type="radio" name="payment_method" value="card" <?= ($order_data['payment_method'] === 'card') ? 'checked' : ''; ?> onchange="switchPaymentTab('card')">
                        <div class="payment-label-info">
                            <span class="payment-icon-svg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            </span>
                            <div>
                                <strong class="payment-title">Debit / Kartu Kredit & QRIS Online</strong>
                                <p class="payment-desc">Pembayaran otomatis tanpa ribet menggunakan Kartu Debit, Visa, Mastercard, atau Scan QRIS.</p>
                            </div>
                        </div>
                        <span class="badge-tag blue">Instan & Terverifikasi</span>
                    </label>

                    <div class="payment-details-body" id="body-card" style="display: <?= ($order_data['payment_method'] === 'card') ? 'block' : 'none'; ?>;">
                        <!-- Sub tabs: Kartu vs QRIS vs VA -->
                        <div class="sub-payment-tabs">
                            <button type="button" class="sub-tab-btn active" onclick="showSubTab('card-input', this)">Kartu Debit / Kredit</button>
                            <button type="button" class="sub-tab-btn" onclick="showSubTab('qris-box', this)">QRIS & E-Wallet</button>
                            <button type="button" class="sub-tab-btn" onclick="showSubTab('va-box', this)">Virtual Account Bank</button>
                        </div>

                        <!-- Sub tab 1: Kartu Input -->
                        <div id="sub-card-input" class="sub-tab-content">
                            <div class="card-brands-row">
                                <span class="card-logo">VISA</span>
                                <span class="card-logo">Mastercard</span>
                                <span class="card-logo">GPN</span>
                                <span class="card-logo">JCB</span>
                            </div>

                            <div class="form-group mt-3">
                                <label for="card_name">Nama Pemegang Kartu</label>
                                <input type="text" id="card_name" placeholder="cth. SITI RAHMAWATI">
                            </div>

                            <div class="form-group">
                                <label for="card_number">Nomor Kartu Debit / Kredit</label>
                                <input type="text" id="card_number" maxlength="19" placeholder="4000 1234 5678 9010">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="card_exp">Masa Berlaku (MM/YY)</label>
                                    <input type="text" id="card_exp" placeholder="12/28" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="card_cvv">CVV (3 Angka)</label>
                                    <input type="password" id="card_cvv" placeholder="•••" maxlength="3">
                                </div>
                            </div>
                        </div>

                        <!-- Sub tab 2: QRIS -->
                        <div id="sub-qris-box" class="sub-tab-content" style="display: none;">
                            <div class="qris-display-box text-center">
                                <div class="qris-header">
                                    <span class="qris-brand">QRIS</span>
                                    <span class="qris-sub">Pembayaran Nasional</span>
                                </div>
                                <div class="qris-mockup">
                                    <svg width="180" height="180" viewBox="0 0 100 100" fill="currentColor">
                                        <rect width="100" height="100" fill="#ffffff" />
                                        <rect x="10" y="10" width="25" height="25" fill="#1e293b" />
                                        <rect x="15" y="15" width="15" height="15" fill="#ffffff" />
                                        <rect x="19" y="19" width="7" height="7" fill="#1e293b" />
                                        <rect x="65" y="10" width="25" height="25" fill="#1e293b" />
                                        <rect x="70" y="15" width="15" height="15" fill="#ffffff" />
                                        <rect x="74" y="19" width="7" height="7" fill="#1e293b" />
                                        <rect x="10" y="65" width="25" height="25" fill="#1e293b" />
                                        <rect x="15" y="70" width="15" height="15" fill="#ffffff" />
                                        <rect x="19" y="74" width="7" height="7" fill="#1e293b" />
                                        <rect x="42" y="12" width="6" height="6" fill="#1e293b" />
                                        <rect x="52" y="22" width="6" height="6" fill="#1e293b" />
                                        <rect x="42" y="32" width="6" height="6" fill="#1e293b" />
                                        <rect x="45" y="45" width="12" height="12" fill="#ea580c" />
                                        <rect x="65" y="45" width="6" height="6" fill="#1e293b" />
                                        <rect x="75" y="60" width="6" height="6" fill="#1e293b" />
                                        <rect x="45" y="75" width="6" height="6" fill="#1e293b" />
                                        <rect x="60" y="75" width="6" height="6" fill="#1e293b" />
                                        <rect x="80" y="80" width="8" height="8" fill="#1e293b" />
                                    </svg>
                                </div>
                                <p class="qris-amount">Nominal: <strong><?= formatRupiah($grand_total); ?></strong></p>
                                <p class="qris-apps">BCA Mobile, Mandiri Livin, GoPay, OVO, ShopeePay, Dana</p>
                            </div>
                        </div>

                        <!-- Sub tab 3: Virtual Account -->
                        <div id="sub-va-box" class="sub-tab-content" style="display: none;">
                            <div class="va-item-box">
                                <div class="va-row">
                                    <strong>BCA Virtual Account</strong>
                                    <span class="va-number">8801 0812 3456 7890</span>
                                </div>
                                <div class="va-row mt-2">
                                    <strong>Mandiri Virtual Account</strong>
                                    <span class="va-number">8950 0812 3456 7890</span>
                                </div>
                                <div class="va-row mt-2">
                                    <strong>BRI Virtual Account</strong>
                                    <span class="va-number">1280 0812 3456 7890</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Total & Submit Payment -->
            <aside class="payment-summary-col">
                <div class="summary-card sticky-card">
                    <h3 class="summary-card-title">Total Tagihan Anda</h3>

                    <div class="summary-breakdown">
                        <div class="summary-line">
                            <span>Total yang Harus Dibayar:</span>
                            <span class="grand-price highlight-large"><?= formatRupiah($grand_total); ?></span>
                        </div>

                        <div class="summary-divider"></div>

                        <ul class="payment-guarantee-list">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Tersimpan Otomatis di Database</span>
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Struk Resmi Terbit Otomatis</span>
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Makanan Langsung Diproses di Dapur</span>
                            </li>
                        </ul>
                    </div>

                    <div class="summary-actions mt-4">
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-submit-payment">
                            <span>Selesaikan & Terbitkan Struk</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                        <a href="order-summary.php" class="btn btn-outline btn-block mt-2">&larr; Kembali ke Ringkasan</a>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</main>

<script>
function switchPaymentTab(method) {
    document.getElementById('opt-cash').classList.toggle('selected', method === 'cash');
    document.getElementById('opt-card').classList.toggle('selected', method === 'card');

    document.getElementById('body-cash').style.display = (method === 'cash') ? 'block' : 'none';
    document.getElementById('body-card').style.display = (method === 'card') ? 'block' : 'none';
}

function showSubTab(tabName, btn) {
    document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.getElementById('sub-card-input').style.display = (tabName === 'card-input') ? 'block' : 'none';
    document.getElementById('sub-qris-box').style.display = (tabName === 'qris-box') ? 'block' : 'none';
    document.getElementById('sub-va-box').style.display = (tabName === 'va-box') ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
$page_title = 'Struk & Bukti Pembayaran';
require_once __DIR__ . '/includes/data.php';

// Jika belum ada struk di sesi, buatkan contoh struk interaktif dari database agar halaman selalu bisa ditinjau
if (empty($_SESSION['last_receipt'])) {
    $_SESSION['last_receipt'] = [
        'receipt_id' => 'DIA-' . date('Ymd') . '-789',
        'order_time' => date('d M Y, H:i') . ' WIB',
        'customer' => [
            'order_type' => 'delivery',
            'customer_name' => 'Ibu Ratna Sari',
            'customer_phone' => '0812-3456-7890',
            'delivery_address' => 'Jl. Flamboyan Indah Blok B2 No. 14, Jakarta Selatan',
            'table_no' => '',
            'notes' => 'Tolong pisahkan sambalnya dan sediakan sendok ekstra.',
            'payment_method' => 'cash',
            'cash_amount' => 'Uang Pas'
        ],
        'items' => [
            ['name' => 'Nasi Liwet Komplit Ina Aina', 'price' => 38000, 'qty' => 2, 'subtotal' => 76000, 'note' => 'Sambal dipisah'],
            ['name' => 'Ayam Goreng Lengkuas Rempah', 'price' => 32000, 'qty' => 1, 'subtotal' => 32000, 'note' => 'Paha'],
            ['name' => 'Es Cendol Nangka Gula Aren', 'price' => 16000, 'qty' => 2, 'subtotal' => 32000, 'note' => 'Es sedikit']
        ],
        'subtotal' => 140000,
        'delivery_fee' => 0,
        'service_fee' => 2000,
        'discount' => 21000,
        'discount_code' => 'INAHEMAT',
        'grand_total' => 121000,
        'payment_status' => 'Menunggu Pembayaran (Bayar Tunai saat Makanan Sampai)'
    ];
}

$receipt = $_SESSION['last_receipt'];
$customer = $receipt['customer'];

require_once __DIR__ . '/includes/header.php';
?>

<main class="order-flow-page receipt-page-wrap">
    <div class="container">
        <!-- Order Progress Stepper -->
        <nav class="order-stepper no-print" aria-label="Tahapan Pemesanan">
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
            <div class="step-item completed">
                <span class="step-badge">✓</span>
                <span class="step-label">Pembayaran</span>
            </div>
            <div class="step-divider completed"></div>
            <div class="step-item active">
                <span class="step-badge">5</span>
                <span class="step-label">Struk Resmi</span>
            </div>
        </nav>

        <div class="receipt-success-banner no-print">
            <div class="success-icon-svg">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <h1 class="success-title">Pesanan Berhasil Disimpan!</h1>
            <p class="success-subtitle">
                Terima kasih, <strong><?= htmlspecialchars($customer['customer_name']); ?></strong>! Pesanan Anda telah tercatat di database dan dapur sedang menyiapkan hidangan hangat Anda.
            </p>
        </div>

        <!-- The Physical Thermal / Paper Style Receipt -->
        <div class="receipt-paper" id="receipt-paper">
            <div class="receipt-header text-center">
                <div class="receipt-logo">
                    <div class="receipt-logo-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                    </div>
                    <h2 class="receipt-restaurant-name">DAPUR INA AINA</h2>
                </div>
                <p class="receipt-address">Jl. Flamboyan Raya No. 18, Kebayoran Baru, Jakarta Selatan</p>
                <p class="receipt-contact">WhatsApp: 0812-3456-7890 | Jam: 08:00 - 21:00 WIB</p>
                <div class="receipt-separator-dashed"></div>
            </div>

            <!-- Receipt Metadata -->
            <div class="receipt-meta-grid">
                <div>
                    <span class="rm-label">No. Struk:</span>
                    <strong class="rm-val"><?= htmlspecialchars($receipt['receipt_id']); ?></strong>
                </div>
                <div class="text-right">
                    <span class="rm-label">Tanggal:</span>
                    <span class="rm-val"><?= htmlspecialchars($receipt['order_time']); ?></span>
                </div>
                <div>
                    <span class="rm-label">Pemesan:</span>
                    <span class="rm-val"><?= htmlspecialchars($customer['customer_name']); ?></span>
                </div>
                <div class="text-right">
                    <span class="rm-label">No. WA:</span>
                    <span class="rm-val"><?= htmlspecialchars($customer['customer_phone']); ?></span>
                </div>
                <div class="full-width">
                    <span class="rm-label">Tipe Layanan:</span>
                    <strong class="rm-val">
                        <?php 
                        if ($customer['order_type'] === 'delivery') echo 'Pesan Antar (Delivery)';
                        elseif ($customer['order_type'] === 'takeaway') echo 'Bungkus Sendiri (Takeaway)';
                        else echo 'Makan di Tempat (Meja: ' . htmlspecialchars($customer['table_no'] ?: '-') . ')';
                        ?>
                    </strong>
                </div>

                <?php if ($customer['order_type'] === 'delivery'): ?>
                    <div class="full-width">
                        <span class="rm-label">Alamat Antar:</span>
                        <span class="rm-val"><?= htmlspecialchars($customer['delivery_address']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($customer['notes'])): ?>
                    <div class="full-width">
                        <span class="rm-label">Catatan Pesanan:</span>
                        <span class="rm-val italic"><?= htmlspecialchars($customer['notes']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="receipt-separator-dashed"></div>

            <!-- Receipt Items Table -->
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th class="text-left">Menu</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipt['items'] as $item): ?>
                        <tr>
                            <td class="text-left">
                                <span class="receipt-item-title"><?= htmlspecialchars($item['name']); ?></span>
                                <?php if (!empty($item['note'])): ?>
                                    <small class="receipt-item-sub">Catatan: <?= htmlspecialchars($item['note']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $item['qty']; ?></td>
                            <td class="text-right"><?= number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="text-right"><?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="receipt-separator-dashed"></div>

            <!-- Receipt Totals -->
            <div class="receipt-totals-list">
                <div class="receipt-total-row">
                    <span>Subtotal Makanan</span>
                    <span><?= formatRupiah($receipt['subtotal']); ?></span>
                </div>

                <?php if ($receipt['discount'] > 0): ?>
                    <div class="receipt-total-row text-discount">
                        <span>Diskon Kupon (<?= htmlspecialchars($receipt['discount_code']); ?>)</span>
                        <span>-<?= formatRupiah($receipt['discount']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="receipt-total-row">
                    <span>Ongkos Kirim</span>
                    <span><?= ($receipt['delivery_fee'] === 0) ? 'Gratis' : formatRupiah($receipt['delivery_fee']); ?></span>
                </div>

                <div class="receipt-total-row">
                    <span>Biaya Kemasan & Layanan</span>
                    <span><?= formatRupiah($receipt['service_fee']); ?></span>
                </div>

                <div class="receipt-separator-double"></div>

                <div class="receipt-total-row grand-row">
                    <strong>TOTAL BAYAR</strong>
                    <strong class="receipt-grand-amount"><?= formatRupiah($receipt['grand_total']); ?></strong>
                </div>

                <div class="receipt-total-row payment-info-row">
                    <span>Metode Pembayaran:</span>
                    <strong><?= ($customer['payment_method'] === 'cash') ? 'Tunai (Cash / COD)' : 'Debit / Kredit / QRIS'; ?></strong>
                </div>

                <div class="receipt-total-row">
                    <span>Status:</span>
                    <span class="status-pill <?= ($customer['payment_method'] === 'cash') ? 'pill-pending' : 'pill-paid'; ?>">
                        <?= htmlspecialchars($receipt['payment_status']); ?>
                    </span>
                </div>
            </div>

            <div class="receipt-separator-dashed"></div>

            <!-- Receipt Barcode & Closing Remarks (Tanpa Emotikon) -->
            <div class="receipt-footer text-center">
                <div class="barcode-graphic">
                    <div class="barcode-lines">
                        ||| | ||||| || |||| ||| |||| | ||||| || ||| |||| || | |||| ||
                    </div>
                    <span class="barcode-text"><?= htmlspecialchars($receipt['receipt_id']); ?></span>
                </div>
                <p class="receipt-closing">
                    *** TERIMA KASIH TELAH MEMESAN ***<br>
                    Makanan dimasak penuh cinta & higienis.<br>
                    Selamat Menikmati Hidangan Dapur Ina Aina!
                </p>
            </div>
        </div>

        <!-- Receipt Action Buttons -->
        <div class="receipt-actions-bar no-print">
            <button type="button" class="btn btn-primary btn-lg" onclick="window.print()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak Struk Resmi</span>
            </button>

            <a href="https://wa.me/6281234567890?text=Halo%20Dapur%20Ina%20Aina,%20saya%20sudah%20memesan%20dengan%20No%20Struk%20<?= urlencode($receipt['receipt_id']); ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                <span>Konfirmasi via WhatsApp</span>
            </a>

            <a href="index.php" class="btn btn-outline btn-lg">
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

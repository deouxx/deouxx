<?php
require_once __DIR__ . '/../includes/data.php';

// Proteksi sesi admin / kasir
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$bill_id = (int) ($_GET['id'] ?? 0);
if ($bill_id <= 0) {
    echo "ID Tagihan tidak valid.";
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, o.delivery_address, o.table_no, o.notes as customer_notes, o.discount_code, o.cash_amount
    FROM bills b
    LEFT JOIN orders o ON b.order_id = o.id
    WHERE b.id = ?
");
$stmt->execute([$bill_id]);
$bill = $stmt->fetch();

if (!$bill) {
    echo "Tagihan tidak ditemukan.";
    exit;
}

// Ambil Item
$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$bill['order_id']]);
$items = $itemsStmt->fetchAll();

$cashier_name = !empty($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Kasir Utama';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota - <?= htmlspecialchars($bill['bill_number']); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000000;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Action bar for screen */
        .screen-action-bar {
            width: 100%;
            max-width: 380px;
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-print {
            flex: 1;
            padding: 10px 16px;
            background-color: #ea580c;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(234, 88, 12, 0.3);
        }

        .btn-print:hover {
            background-color: #c2410c;
        }

        .btn-close {
            padding: 10px 16px;
            background-color: #ffffff;
            color: #334155;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            cursor: pointer;
        }

        /* Thermal POS Receipt Paper Wrapper */
        .receipt-paper {
            width: 100%;
            max-width: 380px;
            background-color: #ffffff;
            padding: 24px 20px;
            border-radius: 4px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .store-sub {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .store-addr {
            font-size: 11px;
            line-height: 1.3;
        }

        .divider-dashed {
            border-top: 1px dashed #475569;
            margin: 10px 0;
        }

        .divider-double {
            border-top: 2px dashed #0f172a;
            margin: 10px 0;
        }

        .meta-line {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            line-height: 1.4;
        }

        .item-row {
            margin-bottom: 6px;
        }

        .item-main {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }

        .item-sub {
            font-size: 11px;
            color: #334155;
            padding-left: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            line-height: 1.4;
        }

        .summary-row.total-bold {
            font-size: 15px;
            font-weight: bold;
            margin: 4px 0;
        }

        .status-badge-print {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 6px;
            border: 1px dashed #000000;
            margin: 12px 0 8px;
            letter-spacing: 1px;
        }

        .barcode-box {
            text-align: center;
            margin-top: 12px;
        }

        .barcode-stripes {
            letter-spacing: 2px;
            font-size: 14px;
            font-weight: bold;
        }

        .closing-text {
            text-align: center;
            font-size: 11px;
            margin-top: 10px;
            line-height: 1.4;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .screen-action-bar {
                display: none !important;
            }

            .receipt-paper {
                max-width: 80mm !important;
                width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 10px 0 !important;
            }
        }
    </style>
</head>
<body>

    <div class="screen-action-bar">
        <button type="button" class="btn-print" onclick="window.print();">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Cetak Nota Sekarang</span>
        </button>
        <button type="button" class="btn-close" onclick="window.close();">Tutup</button>
    </div>

    <div class="receipt-paper">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="store-name">DAPUR INA AINA</div>
            <div class="store-sub">CITA RASA OTENTIK RUMAHAN</div>
            <div class="store-addr">
                Jl. Panglima Polim No. 45, Kebayoran Baru<br>
                Jakarta Selatan &bull; WA: 0812-3456-7890
            </div>
        </div>

        <div class="divider-double"></div>

        <!-- Meta Information -->
        <div class="meta-line">
            <span>NO. BILL:</span>
            <strong><?= htmlspecialchars($bill['bill_number']); ?></strong>
        </div>
        <div class="meta-line">
            <span>NO. STRUK:</span>
            <span><?= htmlspecialchars($bill['receipt_id']); ?></span>
        </div>
        <div class="meta-line">
            <span>WAKTU:</span>
            <span><?= date('d/m/Y H:i', strtotime($bill['created_at'])); ?> WIB</span>
        </div>
        <div class="meta-line">
            <span>KASIR:</span>
            <span><?= htmlspecialchars($cashier_name); ?></span>
        </div>
        <div class="meta-line">
            <span>PELANGGAN:</span>
            <strong><?= htmlspecialchars($bill['customer_name']); ?></strong>
        </div>
        <div class="meta-line">
            <span>LAYANAN:</span>
            <strong><?= strtoupper(htmlspecialchars($bill['order_type'])); ?></strong>
        </div>

        <?php if (!empty($bill['delivery_address'])): ?>
            <div style="font-size: 11px; margin-top: 4px; line-height: 1.2;">
                <span>ALAMAT: <?= htmlspecialchars($bill['delivery_address']); ?></span>
            </div>
        <?php endif; ?>

        <div class="divider-dashed"></div>

        <!-- Item Rows -->
        <div style="margin-bottom: 8px;">
            <?php foreach ($items as $it): ?>
                <div class="item-row">
                    <div class="item-main">
                        <span><?= $it['qty']; ?>x <?= htmlspecialchars($it['menu_name']); ?></span>
                        <span><?= number_format($it['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="item-sub">
                        @ <?= number_format($it['price'], 0, ',', '.'); ?>
                        <?php if (!empty($it['note'])): ?>
                            | (<?= htmlspecialchars($it['note']); ?>)
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="divider-dashed"></div>

        <!-- Summary Totals -->
        <div class="summary-row">
            <span>SUBTOTAL:</span>
            <span>Rp <?= number_format($bill['subtotal'], 0, ',', '.'); ?></span>
        </div>

        <?php if ($bill['discount_amount'] > 0): ?>
            <div class="summary-row">
                <span>DISKON PROMO:</span>
                <span>-Rp <?= number_format($bill['discount_amount'], 0, ',', '.'); ?></span>
            </div>
        <?php endif; ?>

        <div class="summary-row">
            <span>BIAYA KEMASAN:</span>
            <span>Rp <?= number_format($bill['service_fee'], 0, ',', '.'); ?></span>
        </div>

        <div class="summary-row">
            <span>ONGKOS KIRIM:</span>
            <span>Rp <?= number_format($bill['delivery_fee'], 0, ',', '.'); ?></span>
        </div>

        <div class="divider-double"></div>

        <div class="summary-row total-bold">
            <span>TOTAL TAGIHAN:</span>
            <span>Rp <?= number_format($bill['grand_total'], 0, ',', '.'); ?></span>
        </div>

        <div class="summary-row">
            <span>METODE BAYAR:</span>
            <span><?= strtoupper(htmlspecialchars($bill['payment_method'])); ?></span>
        </div>

        <?php if ($bill['amount_paid'] > 0): ?>
            <div class="summary-row">
                <span>BAYAR (TUNAI):</span>
                <span>Rp <?= number_format($bill['amount_paid'], 0, ',', '.'); ?></span>
            </div>
            <?php if ($bill['change_amount'] > 0): ?>
                <div class="summary-row">
                    <span>KEMBALIAN:</span>
                    <span>Rp <?= number_format($bill['change_amount'], 0, ',', '.'); ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Payment Status Box -->
        <div class="status-badge-print">
            *** <?= ($bill['payment_status'] === 'paid') ? 'L U N A S' : 'BELUM DIBAYAR (COD/TAGIHAN)' ?> ***
        </div>

        <div class="barcode-box">
            <div class="barcode-stripes">
                ||| | ||||| || |||| ||| |||| | ||||| || ||| |||| || |
            </div>
            <div style="font-size: 11px; margin-top: 2px;">
                <?= htmlspecialchars($bill['bill_number']); ?>
            </div>
        </div>

        <div class="closing-text">
            TERIMA KASIH TELAH MEMESAN<br>
            DI DAPUR INA AINA<br>
            *** Selamat Menikmati Hidangan Segar ***
        </div>
    </div>

</body>
</html>

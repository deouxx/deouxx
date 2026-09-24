<?php
/**
 * Dapur Ina Aina - Data Store & Session Manager dengan Database SQLite
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Inisialisasi User Session
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = null;
}

// Inisialisasi Keranjang Belanja
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Inisialisasi Diskon
if (!isset($_SESSION['discount'])) {
    $_SESSION['discount'] = 0;
    $_SESSION['discount_code'] = '';
}

// Inisialisasi Data Pesanan
if (!isset($_SESSION['order_data'])) {
    $_SESSION['order_data'] = [
        'order_type' => 'delivery',
        'table_no' => '',
        'pickup_time' => '',
        'customer_name' => '',
        'customer_phone' => '',
        'delivery_address' => '',
        'notes' => '',
        'payment_method' => 'cash',
        'cash_amount' => '',
    ];
}

// Inisialisasi Riwayat Struk
if (!isset($_SESSION['last_receipt'])) {
    $_SESSION['last_receipt'] = null;
}

// Ambil Menu dari Database SQLite
$menu_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY id ASC");
    while ($row = $stmt->fetch()) {
        $menu_items[$row['id']] = $row;
    }
} catch (Exception $e) {
    // Fallback jika terjadi error
    $menu_items = [];
}

// Ambil Kategori dari Database
$categories = [];
try {
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Helper Functions
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getCartCount() {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['qty'];
        }
    }
    return $count;
}

function getCartSubtotal() {
    global $menu_items;
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $item) {
            if (isset($menu_items[$id])) {
                $total += $menu_items[$id]['price'] * $item['qty'];
            }
        }
    }
    return $total;
}

function getDeliveryFee() {
    $order_type = $_SESSION['order_data']['order_type'] ?? 'delivery';
    if ($order_type === 'delivery') {
        $subtotal = getCartSubtotal();
        // Gratis ongkir untuk pesanan >= Rp 100.000
        return ($subtotal >= 100000) ? 0 : 10000;
    }
    return 0;
}

function getServiceFee() {
    return 2000;
}

function getDiscountAmount() {
    $subtotal = getCartSubtotal();
    $discount_percent = $_SESSION['discount'] ?? 0;
    if ($discount_percent > 0) {
        return (int) round(($subtotal * $discount_percent) / 100);
    }
    return 0;
}

function getCartGrandTotal() {
    $subtotal = getCartSubtotal();
    if ($subtotal === 0) return 0;
    
    $delivery = getDeliveryFee();
    $service = getServiceFee();
    $discount = getDiscountAmount();
    
    $grand = $subtotal + $delivery + $service - $discount;
    return max(0, $grand);
}

// Action Handler untuk Keranjang & Transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_to_cart') {
        $id = (int) ($_POST['item_id'] ?? 0);
        $qty = max(1, (int) ($_POST['qty'] ?? 1));
        $note = trim($_POST['note'] ?? '');

        if (isset($menu_items[$id])) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] += $qty;
                if (!empty($note)) {
                    $_SESSION['cart'][$id]['note'] = $note;
                }
            } else {
                $_SESSION['cart'][$id] = [
                    'id' => $id,
                    'qty' => $qty,
                    'note' => $note
                ];
            }
            $_SESSION['flash_msg'] = 'Sukses menambahkan ' . htmlspecialchars($menu_items[$id]['name']) . ' ke keranjang!';

            // Respon JSON untuk AJAX request tanpa reload halaman
            if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Sukses menambahkan ' . $menu_items[$id]['name'] . ' ke keranjang!',
                    'cart_count' => getCartCount(),
                    'subtotal' => getCartSubtotal(),
                    'subtotal_formatted' => formatRupiah(getCartSubtotal()),
                    'item_name' => $menu_items[$id]['name']
                ]);
                exit;
            }
        }
        $redirect = $_POST['redirect'] ?? '../menu.php';
        // Tambah ../ jika redirect tidak mulai dengan http atau ../
        if (!str_starts_with($redirect, 'http') && !str_starts_with($redirect, '../') && !str_starts_with($redirect, '/')) {
            $redirect = '../' . $redirect;
        }
        header('Location: ' . $redirect);
        exit;
    }

    if ($action === 'update_cart') {
        $id = (int) ($_POST['item_id'] ?? 0);
        $qty = (int) ($_POST['qty'] ?? 1);
        $note = trim($_POST['note'] ?? '');

        if (isset($_SESSION['cart'][$id])) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id]['qty'] = $qty;
                $_SESSION['cart'][$id]['note'] = $note;
            }
        }
        header('Location: ../cart.php');
        exit;
    }

    if ($action === 'remove_item') {
        $id = (int) ($_POST['item_id'] ?? 0);
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            $_SESSION['flash_msg'] = 'Item telah dihapus dari keranjang.';
        }
        header('Location: ../cart.php');
        exit;
    }

    if ($action === 'apply_coupon') {
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        // Verifikasi kupon dari database
        $cpnStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1");
        $cpnStmt->execute([$code]);
        $coupon = $cpnStmt->fetch();

        if ($coupon) {
            $_SESSION['discount'] = (int) $coupon['discount_percent'];
            $_SESSION['discount_code'] = $coupon['code'];
            $_SESSION['flash_msg'] = 'Kupon ' . htmlspecialchars($coupon['code']) . ' berhasil digunakan (Diskon ' . $coupon['discount_percent'] . '%).';
        } else {
            $_SESSION['flash_msg_error'] = 'Kupon tidak valid atau sudah kedaluwarsa. Gunakan kupon: INAHEMAT';
        }
        header('Location: ../cart.php');
        exit;
    }

    if ($action === 'save_checkout') {
        $_SESSION['order_data']['order_type'] = $_POST['order_type'] ?? 'delivery';
        $_SESSION['order_data']['table_no'] = trim($_POST['table_no'] ?? '');
        $_SESSION['order_data']['pickup_time'] = trim($_POST['pickup_time'] ?? '');
        $_SESSION['order_data']['customer_name'] = trim($_POST['customer_name'] ?? '');
        $_SESSION['order_data']['customer_phone'] = trim($_POST['customer_phone'] ?? '');
        $_SESSION['order_data']['delivery_address'] = trim($_POST['delivery_address'] ?? '');
        $_SESSION['order_data']['notes'] = trim($_POST['notes'] ?? '');

        header('Location: ../order-summary.php');
        exit;
    }

    if ($action === 'finish_payment') {
        $payment_method = $_POST['payment_method'] ?? 'cash';
        $cash_amount = trim($_POST['cash_amount'] ?? '');

        $_SESSION['order_data']['payment_method'] = $payment_method;
        $_SESSION['order_data']['cash_amount'] = $cash_amount;

        $receipt_id = 'DIA-' . date('Ymd') . '-' . rand(100, 999);
        $order_time = date('d M Y, H:i') . ' WIB';
        $user_id = $_SESSION['user']['id'] ?? null;

        $subtotal = getCartSubtotal();
        $delivery_fee = getDeliveryFee();
        $service_fee = getServiceFee();
        $discount_amount = getDiscountAmount();
        $discount_code = $_SESSION['discount_code'] ?? '';
        $grand_total = getCartGrandTotal();
        $payment_status = ($payment_method === 'cash') 
            ? 'Menunggu Pembayaran (Bayar Tunai saat Makanan Sampai)' 
            : 'Lunas (Terverifikasi Otomatis)';

        // Simpan Transaksi Permanen ke Database (Tabel orders & order_items)
        try {
            $pdo->beginTransaction();

            $orderSql = "INSERT INTO orders (
                receipt_id, user_id, order_type, customer_name, customer_phone, 
                delivery_address, table_no, notes, subtotal, delivery_fee, 
                service_fee, discount_amount, discount_code, grand_total, 
                payment_method, cash_amount, payment_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmtOrder = $pdo->prepare($orderSql);
            $stmtOrder->execute([
                $receipt_id,
                $user_id,
                $_SESSION['order_data']['order_type'],
                $_SESSION['order_data']['customer_name'],
                $_SESSION['order_data']['customer_phone'],
                $_SESSION['order_data']['delivery_address'],
                $_SESSION['order_data']['table_no'],
                $_SESSION['order_data']['notes'],
                $subtotal,
                $delivery_fee,
                $service_fee,
                $discount_amount,
                $discount_code,
                $grand_total,
                $payment_method,
                $cash_amount,
                $payment_status
            ]);

            $order_id = $pdo->lastInsertId();

            // Simpan setiap item ke order_items
            $items_snapshot = [];
            $itemSql = "INSERT INTO order_items (order_id, menu_id, menu_name, price, qty, subtotal, note) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtItem = $pdo->prepare($itemSql);

            foreach ($_SESSION['cart'] as $id => $item) {
                if (isset($menu_items[$id])) {
                    $item_name = $menu_items[$id]['name'];
                    $price = $menu_items[$id]['price'];
                    $qty = $item['qty'];
                    $line_total = $price * $qty;
                    $item_note = $item['note'] ?? '';

                    $stmtItem->execute([
                        $order_id,
                        $id,
                        $item_name,
                        $price,
                        $qty,
                        $line_total,
                        $item_note
                    ]);

                    $items_snapshot[] = [
                        'id' => $id,
                        'name' => $item_name,
                        'price' => $price,
                        'qty' => $qty,
                        'subtotal' => $line_total,
                        'note' => $item_note,
                        'image_url' => $menu_items[$id]['image_url']
                    ];
                }
            }

            // 3. Buat entitas Tagihan / Bill resmi untuk Kasir & Admin
            $bill_number = 'BILL-' . date('Ymd') . '-' . str_pad($order_id, 3, '0', STR_PAD_LEFT);
            $is_paid = (stripos($payment_status, 'Lunas') !== false);
            $bill_pay_status = $is_paid ? 'paid' : 'unpaid';
            $bill_order_status = $is_paid ? 'confirmed' : 'pending';
            $paid_at = $is_paid ? date('Y-m-d H:i:s') : null;
            $amount_paid = $is_paid ? $grand_total : 0;
            $cashier_name = !empty($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Kasir Utama';

            $stmtBill = $pdo->prepare("
                INSERT INTO bills (
                    bill_number, order_id, receipt_id, customer_name, customer_phone,
                    order_type, subtotal, tax_amount, service_fee, delivery_fee,
                    discount_amount, grand_total, payment_method, payment_status,
                    order_status, amount_paid, change_amount, cashier_notes, paid_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtBill->execute([
                $bill_number,
                $order_id,
                $receipt_id,
                $_SESSION['order_data']['customer_name'],
                $_SESSION['order_data']['customer_phone'],
                $_SESSION['order_data']['order_type'] ?? 'delivery',
                $subtotal,
                0,
                $service_fee,
                $delivery_fee,
                $discount_amount,
                $grand_total,
                $payment_method,
                $bill_pay_status,
                $bill_order_status,
                $amount_paid,
                0,
                'Faktur diterbitkan via Web Checkout oleh ' . $cashier_name . (!empty($_SESSION['order_data']['notes']) ? ' | Catatan: ' . $_SESSION['order_data']['notes'] : ''),
                $paid_at
            ]);

            $pdo->commit();

            // Simpan ke sesi untuk tampilan struk instan
            $_SESSION['last_receipt'] = [
                'order_db_id' => $order_id,
                'receipt_id' => $receipt_id,
                'bill_number' => $bill_number,
                'order_time' => $order_time,
                'customer' => $_SESSION['order_data'],
                'items' => $items_snapshot,
                'subtotal' => $subtotal,
                'delivery_fee' => $delivery_fee,
                'service_fee' => $service_fee,
                'discount' => $discount_amount,
                'discount_code' => $discount_code,
                'grand_total' => $grand_total,
                'payment_status' => $payment_status
            ];

            // Kosongkan keranjang
            $_SESSION['cart'] = [];
            $_SESSION['discount'] = 0;
            $_SESSION['discount_code'] = '';

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_msg_error'] = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
            header('Location: ../payment.php');
            exit;
        }

        header('Location: ../receipt.php');
        exit;
    }
}

// Helper Role Kasir & Admin
function isAdmin() {
    return !empty($_SESSION['user']) && isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['admin', 'kasir']);
}

function isSuperAdmin() {
    return !empty($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}


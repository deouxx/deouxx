<?php
/**
 * Dapur Ina Aina - Database Connection & Migration (SQLite & MySQL .env Support)
 */

// Simple .env Parser
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Load .env dari root direktori proyek
loadEnv(__DIR__ . '/../.env');

$db_driver = $_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?? 'sqlite';

try {
    if (strtolower($db_driver) === 'mysql') {
        $db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $db_port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
        $db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'dapur_ina';
        $db_user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
        $db_pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '';

        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
    } else {
        // Fallback default: SQLite (Lokal)
        $db_dir = __DIR__ . '/../database';
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
        $db_file = $db_dir . '/dapur_ina.sqlite';
        $pdo = new PDO('sqlite:' . $db_file);
        $pdo->exec('PRAGMA foreign_keys = ON;');
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Inisialisasi tabel jika belum ada
function initDatabase($pdo) {
    $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $autoInc    = ($driverName === 'mysql') ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id {$autoInc},
            name VARCHAR(150) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'customer',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categories (
            id {$autoInc},
            slug VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            description TEXT
        );

        CREATE TABLE IF NOT EXISTS menu_items (
            id {$autoInc},
            category_slug VARCHAR(50) NOT NULL,
            name VARCHAR(150) NOT NULL,
            price INTEGER NOT NULL,
            image_url TEXT NOT NULL,
            description TEXT NOT NULL,
            badge VARCHAR(50) DEFAULT '',
            spice_level INTEGER DEFAULT 0,
            is_available INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS coupons (
            id {$autoInc},
            code VARCHAR(30) NOT NULL UNIQUE,
            discount_percent INTEGER NOT NULL,
            is_active INTEGER DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS orders (
            id {$autoInc},
            receipt_id VARCHAR(50) NOT NULL UNIQUE,
            user_id INTEGER NULL,
            order_type VARCHAR(30) NOT NULL,
            customer_name VARCHAR(150) NOT NULL,
            customer_phone VARCHAR(30) NOT NULL,
            delivery_address TEXT,
            table_no VARCHAR(30),
            notes TEXT,
            subtotal INTEGER NOT NULL,
            delivery_fee INTEGER NOT NULL,
            service_fee INTEGER NOT NULL,
            discount_amount INTEGER DEFAULT 0,
            discount_code VARCHAR(30) DEFAULT '',
            grand_total INTEGER NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            cash_amount VARCHAR(100) DEFAULT '',
            payment_status VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS order_items (
            id {$autoInc},
            order_id INTEGER NOT NULL,
            menu_id INTEGER NOT NULL,
            menu_name VARCHAR(150) NOT NULL,
            price INTEGER NOT NULL,
            qty INTEGER NOT NULL,
            subtotal INTEGER NOT NULL,
            note TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS bills (
            id {$autoInc},
            bill_number VARCHAR(50) NOT NULL UNIQUE,
            order_id INTEGER NOT NULL,
            receipt_id VARCHAR(50) NOT NULL,
            customer_name VARCHAR(150) NOT NULL,
            customer_phone VARCHAR(30) NOT NULL,
            order_type VARCHAR(30) NOT NULL,
            subtotal INTEGER NOT NULL,
            tax_amount INTEGER DEFAULT 0,
            service_fee INTEGER NOT NULL,
            delivery_fee INTEGER NOT NULL,
            discount_amount INTEGER DEFAULT 0,
            grand_total INTEGER NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid',
            order_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            amount_paid INTEGER DEFAULT 0,
            change_amount INTEGER DEFAULT 0,
            cashier_notes TEXT,
            paid_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );
    ");

    // Cek apakah data menu sudah ada
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM menu_items");
    $row = $stmt->fetch();
    $count = (int) ($row['cnt'] ?? 0);

    if ($count === 0) {
        // Seed Categories
        $categories = [
            ['makanan', 'Makanan Utama', 'Olahan lauk kaya bumbu rempah tradisional'],
            ['paket', 'Paket Nasi Kotak', 'Pilihan praktis nasi komplit untuk makan siang & katering'],
            ['camilan', 'Camilan & Sambal', 'Gorengan renyah dan aneka ulekan sambal khas'],
            ['minuman', 'Minuman Segar', 'Minuman segar tradisional pelepas dahaga']
        ];
        $catStmt = $pdo->prepare("INSERT INTO categories (slug, name, description) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $catStmt->execute($cat);
        }

        // Seed Real Food Items
        $items = [
            ['makanan', 'Nasi Liwet Komplit Ina Aina', 38000, 'assets/images/dish-1.jpg', 'Nasi liwet gurih wangi daun jeruk, ayam suwir lengkuas, teri medan renyah, tahu, tempe bacem, lalapan segar & sambal bajak khas.', 'Best Seller', 2],
            ['makanan', 'Ayam Goreng Lengkuas Rempah', 32000, 'assets/images/dish-2.jpg', 'Potongan ayam pejantan yang diungkep bumbu rempah tradisional dan digoreng dengan taburan remah lengkuas yang gurih renyah.', 'Favorit', 1],
            ['makanan', 'Rendang Daging Sapi Dapur Ibu', 42000, 'assets/images/dish-3.jpg', 'Daging sapi pilihan dimasak lambat 6 jam dengan santan kelapa tua murni dan rempah kaya aroma. Daging sangat empuk meresap.', 'Chef Special', 2],
            ['makanan', 'Ikan Gurame Bakar Madu Pedas', 48000, 'assets/images/dish-4.jpg', 'Gurame segar dibakar dengan olesan bumbu bakar madu istimewa, disajikan dengan sambal kecap rawit dan irisan jeruk limau.', 'Segar', 2],
            ['makanan', 'Sop Buntut Sapi Kuah Bening', 45000, 'assets/images/dish-5.jpg', 'Buntut sapi empuk dalam kaldu rempah bening nan segar, dilengkapi wortel, kentang, taburan bawang goreng, dan emping.', '', 1],
            ['paket', 'Paket Nasi Kotak Ayam Bakar', 30000, 'assets/images/dish-6.jpg', 'Nasi putih pulen, ayam bakar manis gurih, urap sayur kelapa sangrai, kerupuk udang, dan sambal terasi.', 'Hemat', 2],
            ['paket', 'Paket Nasi Timbel Komplit', 35000, 'assets/images/dish-7.jpg', 'Nasi timbel bungkus daun pisang hangat, empal daging serundeng, ikan asin jambal, sayur asem seger, dan sambal dadak.', 'Tradisional', 3],
            ['camilan', 'Bakwan Jagung Manis Renyah (Isi 4)', 18000, 'assets/images/dish-8.jpg', 'Jagung manis pipil segar digoreng renyah dengan racikan seledri dan daun bawang, disajikan bersama cabai rawit hijau.', 'Gurih', 0],
            ['camilan', 'Tahu Gejrot Khas Cirebon', 16000, 'assets/images/dish-9.jpg', 'Tahu sumedang kopong disiram kuah asam manis pedas gula merah, bawang merah segar, dan ulekan cabai rawit.', 'Pedas Manis', 3],
            ['camilan', 'Sambal Bawang Dapur Ina (Toples)', 15000, 'assets/images/dish-10.jpg', 'Sambal bawang ulek kasar dengan aroma minyak kelapa wangi. Cocok untuk semua lauk dan tahan disimpan.', 'Pedas Mantap', 4],
            ['minuman', 'Es Cendol Nangka Gula Aren', 16000, 'assets/images/dish-11.jpg', 'Cendol kenyal alami pandan suji, santan murni gurih, gula aren asli, dan potongan buah nangka harum manis.', 'Segar Manis', 0],
            ['minuman', 'Es Kelapa Jeruk Murni', 18000, 'assets/images/dish-12.jpg', 'Perpaduan kelapa muda keruk segar dengan perasan jeruk manis alami yang melepas dahaga seketika.', 'Favorit', 0],
            ['minuman', 'Es Teh Manis Melati Jumbo', 8000, 'assets/images/dish-13.jpg', 'Seduhan teh melati tradisional wangi sepet legit, disajikan dingin dalam porsi jumbo.', '', 0],
            ['minuman', 'Wedang Uwuh Rempah Hangat', 15000, 'assets/images/dish-14.jpg', 'Minuman rempah khas Yogyakarta dengan jahe geprek, secang, cengkeh, kayu manis, dan gula batu.', 'Hangat Sehat', 0]
        ];

        $itemStmt = $pdo->prepare("INSERT INTO menu_items (category_slug, name, price, image_url, description, badge, spice_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($items as $it) {
            $itemStmt->execute($it);
        }

        // Seed Coupon
        $cpnStmt = $pdo->prepare("INSERT INTO coupons (code, discount_percent, is_active) VALUES (?, ?, ?)");
        $cpnStmt->execute(['INAHEMAT', 15, 1]);
        $cpnStmt->execute(['DAPURINA', 10, 1]);
    }

    // 1. Seed Akun Admin & Kasir Default jika belum ada
    $adminCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $adminCheck->execute(['admin@dapurina.com']);
    if (!$adminCheck->fetch()) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $insAdmin = $pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $insAdmin->execute(['Admin Dapur Ina', '081234567899', 'admin@dapurina.com', $adminPass, 'admin']);
    }

    $kasirCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $kasirCheck->execute(['kasir@dapurina.com']);
    if (!$kasirCheck->fetch()) {
        $kasirPass = password_hash('kasir123', PASSWORD_DEFAULT);
        $insKasir = $pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $insKasir->execute(['Kasir Utama', '081298765432', 'kasir@dapurina.com', $kasirPass, 'kasir']);
    }
}

initDatabase($pdo);

-- ============================================================================
-- DAPUR INA AINA - DATABASE SCHEMA (SQLITE & MYSQL COMPATIBLE)
-- ============================================================================

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS menu_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(30) NOT NULL UNIQUE,
    discount_percent INTEGER NOT NULL,
    is_active INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
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


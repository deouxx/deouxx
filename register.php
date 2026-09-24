<?php
$page_title = 'Daftar Akun Baru';
require_once __DIR__ . '/includes/data.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'Harap isi semua kolom pendaftaran yang wajib.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal harus 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        try {
            // Cek apakah email sudah terdaftar
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'Alamat email ini sudah terdaftar. Silakan masuk ke akun Anda.';
            } else {
                // Hash kata sandi
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$name, $phone, $email, $hashedPassword]);
                $userId = $pdo->lastInsertId();

                // Simpan ke sesi user
                $_SESSION['user'] = [
                    'id' => $userId,
                    'name' => htmlspecialchars($name),
                    'phone' => htmlspecialchars($phone),
                    'email' => htmlspecialchars($email),
                ];

                // Otomatis isi data pemesan di checkout
                if (empty($_SESSION['order_data']['customer_name'])) {
                    $_SESSION['order_data']['customer_name'] = $name;
                    $_SESSION['order_data']['customer_phone'] = $phone;
                }

                $_SESSION['flash_msg'] = 'Selamat datang di Dapur Ina Aina, ' . htmlspecialchars($name) . '! Akun Anda berhasil terdaftar di database.';
                header('Location: menu.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem database: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="auth-page">
    <div class="container auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon-circle">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7.5" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </div>
                <h1 class="auth-title">Daftar Akun Pelanggan</h1>
                <p class="auth-subtitle">Bergabunglah untuk menikmati kemudahan pemesanan masakan rumahan segar dan promo khusus Dapur Ina Aina.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error mb-4">
                    <span class="alert-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </span>
                    <span><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="name" name="name" placeholder="cth. Siti Rahmawati" value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Nomor WhatsApp / HP <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="081234567890" value="<?= htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Kata Sandi <span class="required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Konfirmasi Kata Sandi <span class="required">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi" required>
                    </div>
                </div>

                <div class="form-agreement">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" required checked>
                        <span>Saya menyetujui syarat & ketentuan pemesanan di Dapur Ina Aina.</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <span>Daftar Sekarang</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>

            <div class="auth-footer">
                <p>Sudah memiliki akun Dapur Ina Aina? <a href="login.php" class="link-bold">Masuk di sini</a></p>
                <div class="auth-divider"><span>atau</span></div>
                <a href="menu.php" class="btn btn-outline btn-block">Pesan Langsung Tanpa Akun (Tamu / Guest)</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

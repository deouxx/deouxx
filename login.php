<?php
$page_title = 'Masuk ke Akun';
require_once __DIR__ . '/includes/data.php';

// Handler Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION['user'] = null;
    $_SESSION['flash_msg'] = 'Anda telah berhasil keluar akun.';
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Silakan masukkan email / nomor WhatsApp dan kata sandi Anda.';
    } else {
        try {
            // Cek di database users
            $userStmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
            $userStmt->execute([$identifier, $identifier]);
            $user = $userStmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login Berhasil dari Database
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => htmlspecialchars($user['name']),
                    'phone' => htmlspecialchars($user['phone']),
                    'email' => htmlspecialchars($user['email']),
                    'role' => $user['role'] ?? 'customer'
                ];

                if (empty($_SESSION['order_data']['customer_name'])) {
                    $_SESSION['order_data']['customer_name'] = $user['name'];
                    $_SESSION['order_data']['customer_phone'] = $user['phone'];
                }

                $_SESSION['flash_msg'] = 'Selamat datang kembali, ' . htmlspecialchars($user['name']) . '!';
                
                // Jika Admin atau Kasir, langsung arahkan ke Dashboard
                if (in_array($user['role'] ?? '', ['admin', 'kasir'])) {
                    header('Location: admin/index.php');
                    exit;
                }

                $redirect = !empty($_SESSION['cart']) ? 'cart.php' : 'menu.php';
                header('Location: ' . $redirect);
                exit;
            } elseif ($user) {
                $error = 'Kata sandi yang Anda masukkan salah. Silakan coba lagi.';
            } else {
                // Jika belum ada di database, sediakan mode login fleksibel / demo
                $displayName = 'Pelanggan Dapur Ina';
                if (strpos($identifier, '@') !== false) {
                    $parts = explode('@', $identifier);
                    $displayName = ucwords(str_replace(['.', '_'], ' ', $parts[0]));
                }
                
                $_SESSION['user'] = [
                    'id' => 0,
                    'name' => $displayName,
                    'phone' => (strpos($identifier, '@') === false) ? $identifier : '081234567890',
                    'email' => (strpos($identifier, '@') !== false) ? $identifier : 'user@dapurina.com',
                ];

                if (empty($_SESSION['order_data']['customer_name'])) {
                    $_SESSION['order_data']['customer_name'] = $displayName;
                    $_SESSION['order_data']['customer_phone'] = $_SESSION['user']['phone'];
                }

                $_SESSION['flash_msg'] = 'Selamat datang di Dapur Ina Aina!';
                $redirect = !empty($_SESSION['cart']) ? 'cart.php' : 'menu.php';
                header('Location: ' . $redirect);
                exit;
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
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
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                </div>
                <h1 class="auth-title">Masuk ke Dapur Ina Aina</h1>
                <p class="auth-subtitle">Masuk untuk melihat pesanan Anda, melacak pengantaran, dan memesan hidangan favorit lebih cepat.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error mb-4">
                    <span class="alert-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </span>
                    <span><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="identifier">Email atau Nomor WhatsApp <span class="required">*</span></label>
                    <input type="text" id="identifier" name="identifier" placeholder="cth. siti@email.com atau 081234567890" value="<?= htmlspecialchars($_POST['identifier'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password">Kata Sandi <span class="required">*</span></label>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Masukkan kata sandi Anda" required>
                </div>

                <div class="form-row-checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" checked>
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <span>Masuk ke Akun</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </form>

            <div class="auth-footer">
                <p>Belum memiliki akun? <a href="register.php" class="link-bold">Daftar sekarang</a></p>
                <div class="auth-divider"><span>atau</span></div>
                <a href="menu.php" class="btn btn-outline btn-block">Pesan Langsung Tanpa Akun (Tamu / Guest)</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

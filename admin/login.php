<?php
require_once __DIR__ . '/../includes/data.php';

// Jika sudah login sebagai admin atau kasir, langsung ke dashboard
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Silakan isi email dan kata sandi petugas.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'kasir') LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => htmlspecialchars($user['name']),
                    'email' => htmlspecialchars($user['email']),
                    'phone' => htmlspecialchars($user['phone']),
                    'role' => $user['role']
                ];
                $_SESSION['flash_msg'] = 'Selamat datang, ' . htmlspecialchars($user['name']) . '! Sesi POS aktif.';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email atau kata sandi petugas tidak valid.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas Admin & Kasir - Dapur Ina Aina</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="admin.css">
    <style>
        body.admin-login-body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card-admin {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        .login-header-admin {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-brand-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #ea580c, #c2410c);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 16px rgba(234, 88, 12, 0.35);
        }

        .login-title-admin {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .login-subtitle-admin {
            font-size: 0.85rem;
            color: #64748b;
        }

        .login-preset-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 22px;
        }

        .preset-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 8px;
            display: block;
        }

        .preset-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-preset {
            flex: 1;
            padding: 6px 10px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-preset:hover {
            border-color: #ea580c;
            color: #ea580c;
            background-color: #fff7ed;
        }
    </style>
</head>
<body class="admin-login-body">

    <div class="login-card-admin">
        <div class="login-header-admin">
            <div class="login-brand-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
            </div>
            <h1 class="login-title-admin">Dapur Ina Aina</h1>
            <p class="login-subtitle-admin">Portal Masuk Petugas Kasir & Administrator</p>
        </div>

        <!-- Presets demo untuk kemudahan pengujian -->
        <div class="login-preset-box">
            <span class="preset-title">Pilih Akun Petugas Cepat:</span>
            <div class="preset-buttons">
                <button type="button" class="btn-preset" onclick="fillAdmin()">Admin Toko</button>
                <button type="button" class="btn-preset" onclick="fillKasir()">Kasir Utama</button>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; margin-bottom: 20px; border-left: 4px solid #ef4444;">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group-admin">
                <label for="email">Alamat Email Petugas</label>
                <input type="email" id="email" name="email" class="form-control-admin" placeholder="admin@dapurina.com" required value="<?= htmlspecialchars($_POST['email'] ?? 'admin@dapurina.com'); ?>">
            </div>

            <div class="form-group-admin">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-control-admin" placeholder="Masukkan kata sandi..." required value="<?= htmlspecialchars($_POST['password'] ?? 'admin123'); ?>">
            </div>

            <button type="submit" class="btn-admin btn-admin-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 0.95rem; margin-top: 10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                <span>Masuk ke Dashboard</span>
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 18px; border-top: 1px solid #f1f5f9;">
            <a href="../index.php" style="font-size: 0.82rem; font-weight: 700; color: #64748b; text-decoration: none;">
                &larr; Kembali ke Website Restoran
            </a>
        </div>
    </div>

    <script>
    function fillAdmin() {
        document.getElementById('email').value = 'admin@dapurina.com';
        document.getElementById('password').value = 'admin123';
    }

    function fillKasir() {
        document.getElementById('email').value = 'kasir@dapurina.com';
        document.getElementById('password').value = 'kasir123';
    }
    </script>
</body>
</html>

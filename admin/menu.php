<?php
// POST handler HARUS di atas sebelum include header (yang output HTML)
require_once __DIR__ . '/../includes/data.php';

if (!isAdmin() || !isSuperAdmin()) {
    // Kasir tidak punya akses ke halaman ini
    header('Location: index.php');
    exit;
}

// Handler Aksi CRUD Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Toggle Ketersediaan Stok
    if ($action === 'toggle_availability') {
        $item_id       = (int) ($_POST['item_id'] ?? 0);
        $current_status = (int) ($_POST['current_status'] ?? 1);
        $new_status    = ($current_status === 1) ? 0 : 1;
        try {
            $pdo->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?")
                ->execute([$new_status, $item_id]);
            $_SESSION['flash_msg'] = 'Status stok menu berhasil diperbarui!';
        } catch (Exception $e) {
            $_SESSION['flash_msg_error'] = 'Gagal update stok: ' . $e->getMessage();
        }
        header('Location: menu.php');
        exit;
    }

    // 2. Tambah Menu Baru
    if ($action === 'add_menu') {
        $name      = trim($_POST['name'] ?? '');
        $category  = $_POST['category_slug'] ?? 'makanan';
        $price     = (int) ($_POST['price'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? 'assets/images/dish-1.jpg');
        $desc      = trim($_POST['description'] ?? '');
        $badge     = trim($_POST['badge'] ?? '');

        if (!empty($name) && $price > 0) {
            try {
                $pdo->prepare("INSERT INTO menu_items (category_slug, name, price, image_url, description, badge, spice_level, is_available) VALUES (?, ?, ?, ?, ?, ?, 0, 1)")
                    ->execute([$category, $name, $price, $image_url, $desc, $badge]);
                $_SESSION['flash_msg'] = 'Menu baru "' . htmlspecialchars($name) . '" berhasil ditambahkan!';
            } catch (Exception $e) {
                $_SESSION['flash_msg_error'] = 'Gagal tambah menu: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_msg_error'] = 'Nama menu dan harga wajib diisi.';
        }
        header('Location: menu.php');
        exit;
    }

    // 3. Edit Menu
    if ($action === 'edit_menu') {
        $item_id  = (int) ($_POST['item_id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $category = $_POST['category_slug'] ?? 'makanan';
        $price    = (int) ($_POST['price'] ?? 0);
        $desc     = trim($_POST['description'] ?? '');
        $badge    = trim($_POST['badge'] ?? '');

        if ($item_id > 0 && !empty($name) && $price > 0) {
            try {
                $pdo->prepare("UPDATE menu_items SET name = ?, category_slug = ?, price = ?, description = ?, badge = ? WHERE id = ?")
                    ->execute([$name, $category, $price, $desc, $badge, $item_id]);
                $_SESSION['flash_msg'] = 'Data menu berhasil diperbarui!';
            } catch (Exception $e) {
                $_SESSION['flash_msg_error'] = 'Gagal edit menu: ' . $e->getMessage();
            }
        }
        header('Location: menu.php');
        exit;
    }

    // 4. Hapus Menu
    if ($action === 'delete_menu') {
        $item_id = (int) ($_POST['item_id'] ?? 0);
        if ($item_id > 0) {
            try {
                $pdo->prepare("DELETE FROM menu_items WHERE id = ?")
                    ->execute([$item_id]);
                $_SESSION['flash_msg'] = 'Menu berhasil dihapus dari katalog!';
            } catch (Exception $e) {
                $_SESSION['flash_msg_error'] = 'Gagal hapus: ' . $e->getMessage();
            }
        }
        header('Location: menu.php');
        exit;
    }
}

// Baru include header setelah semua redirect selesai
$page_title = 'Katalog Menu & Manajemen Stok';
require_once __DIR__ . '/includes/header.php';


// Filter Kategori & Pencarian
$active_cat = $_GET['cat'] ?? 'semua';
$search_q = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM menu_items WHERE 1=1";
$params = [];

if ($active_cat !== 'semua') {
    $sql .= " AND category_slug = ?";
    $params[] = $active_cat;
}

if (!empty($search_q)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $like = '%' . $search_q . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$menus = $stmt->fetchAll();
?>

<main class="admin-content">
    <div class="admin-card">
        <!-- Header -->
        <div class="admin-card-header">
            <div class="card-heading-group">
                <h2>Katalog Menu Dapur Ina Aina</h2>
                <p>Kelola daftar hidangan, harga jual, foto asli, dan ketersediaan stok dapur</p>
            </div>
            <div class="card-header-actions">
                <button type="button" class="btn-admin btn-admin-primary" onclick="openModal('addMenuModal')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Tambah Menu Baru</span>
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="admin-filter-bar">
            <form action="menu.php" method="GET" class="admin-search-wrap">
                <input type="hidden" name="cat" value="<?= htmlspecialchars($active_cat); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" class="admin-search-input" placeholder="Cari masakan atau bahan..." value="<?= htmlspecialchars($search_q); ?>">
            </form>

            <div class="filter-pills-scroll">
                <a href="menu.php?cat=semua<?= !empty($search_q) ? '&q=' . urlencode($search_q) : ''; ?>" class="filter-pill <?= ($active_cat === 'semua') ? 'active' : ''; ?>">
                    Semua (<?= count($menus); ?>)
                </a>
                <a href="menu.php?cat=makanan<?= !empty($search_q) ? '&q=' . urlencode($search_q) : ''; ?>" class="filter-pill <?= ($active_cat === 'makanan') ? 'active' : ''; ?>">
                    Makanan Utama
                </a>
                <a href="menu.php?cat=paket<?= !empty($search_q) ? '&q=' . urlencode($search_q) : ''; ?>" class="filter-pill <?= ($active_cat === 'paket') ? 'active' : ''; ?>">
                    Paket Nasi Kotak
                </a>
                <a href="menu.php?cat=camilan<?= !empty($search_q) ? '&q=' . urlencode($search_q) : ''; ?>" class="filter-pill <?= ($active_cat === 'camilan') ? 'active' : ''; ?>">
                    Camilan & Sambal
                </a>
                <a href="menu.php?cat=minuman<?= !empty($search_q) ? '&q=' . urlencode($search_q) : ''; ?>" class="filter-pill <?= ($active_cat === 'minuman') ? 'active' : ''; ?>">
                    Minuman Segar
                </a>
            </div>
        </div>

        <!-- Menu Table -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Foto & Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga Satuan</th>
                        <th>Badge</th>
                        <th>Status Stok</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menus)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                Belum ada menu yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($menus as $m): ?>
                            <tr>
                                <td>
                                    <div class="item-name-cell">
                                        <img src="../<?= htmlspecialchars($m['image_url']); ?>" alt="<?= htmlspecialchars($m['name']); ?>" class="item-thumb-micro" style="width: 50px; height: 50px;">
                                        <div>
                                            <strong style="color: var(--text-main); font-size: 0.95rem;"><?= htmlspecialchars($m['name']); ?></strong>
                                            <span style="display: block; font-size: 0.78rem; color: var(--text-muted); max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($m['description']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; background: #f8fafc; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border-ui);">
                                        <?= htmlspecialchars($m['category_slug']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="font-size: 0.95rem; color: var(--text-main);"><?= formatRupiah($m['price']); ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($m['badge'])): ?>
                                        <span style="background: var(--admin-primary-light); color: var(--admin-primary); font-weight: 700; font-size: 0.75rem; padding: 3px 8px; border-radius: 999px;">
                                            <?= htmlspecialchars($m['badge']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Toggle Stok Button -->
                                    <form action="menu.php" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="action" value="toggle_availability">
                                        <input type="hidden" name="item_id" value="<?= $m['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?= $m['is_available']; ?>">
                                        <?php if ($m['is_available'] == 1): ?>
                                            <button type="submit" class="btn-admin btn-admin-success" style="padding: 4px 10px; font-size: 0.75rem;" title="Klik untuk ubah jadi Habis">
                                                <span>Tersedia</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn-admin btn-admin-danger" style="padding: 4px 10px; font-size: 0.75rem;" title="Klik untuk ubah jadi Tersedia">
                                                <span>Habis / Kosong</span>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <!-- Edit Button -->
                                        <button type="button" class="btn-action-icon" title="Edit Menu" onclick='editMenu(<?= json_encode($m); ?>)'>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="menu.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu <?= htmlspecialchars($m['name']); ?>?');" style="display: inline-block;">
                                            <input type="hidden" name="action" value="delete_menu">
                                            <input type="hidden" name="item_id" value="<?= $m['id']; ?>">
                                            <button type="submit" class="btn-action-icon delete" title="Hapus Menu">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal: Tambah Menu Baru -->
<div class="admin-modal-overlay" id="addMenuModal">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3>Tambah Menu Kuliner Baru</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('addMenuModal')">&times;</button>
        </div>
        <form action="menu.php" method="POST" class="admin-modal-body">
            <input type="hidden" name="action" value="add_menu">

            <div class="form-group-admin">
                <label for="new_name">Nama Masakan / Minuman *</label>
                <input type="text" id="new_name" name="name" class="form-control-admin" placeholder="Contoh: Bebek Goreng Kremes Sambal Ijo" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group-admin">
                    <label for="new_category">Kategori *</label>
                    <select id="new_category" name="category_slug" class="form-control-admin" required>
                        <option value="makanan">Makanan Utama</option>
                        <option value="paket">Paket Nasi Kotak</option>
                        <option value="camilan">Camilan & Sambal</option>
                        <option value="minuman">Minuman Segar</option>
                    </select>
                </div>

                <div class="form-group-admin">
                    <label for="new_price">Harga Jual (Rp) *</label>
                    <input type="number" id="new_price" name="price" class="form-control-admin" placeholder="35000" min="1000" required>
                </div>
            </div>

            <div class="form-group-admin">
                <label for="new_badge">Badge Khusus (Opsional)</label>
                <input type="text" id="new_badge" name="badge" class="form-control-admin" placeholder="Contoh: Best Seller, Favorit">
            </div>

            <div class="form-group-admin">
                <label for="new_image">Pilihan Foto Makanan Nyata</label>
                <select id="new_image" name="image_url" class="form-control-admin">
                    <option value="assets/images/dish-1.jpg">Nasi Liwet Komplit</option>
                    <option value="assets/images/dish-2.jpg">Ayam Goreng Lengkuas</option>
                    <option value="assets/images/dish-3.jpg">Rendang Daging Sapi</option>
                    <option value="assets/images/dish-4.jpg">Ikan Gurame Bakar</option>
                    <option value="assets/images/dish-5.jpg">Sop Buntut Sapi</option>
                    <option value="assets/images/dish-6.jpg">Paket Nasi Kotak</option>
                    <option value="assets/images/dish-7.jpg">Paket Nasi Timbel</option>
                    <option value="assets/images/dish-8.jpg">Bakwan Jagung</option>
                    <option value="assets/images/dish-9.jpg">Tahu Gejrot Cirebon</option>
                    <option value="assets/images/dish-10.jpg">Sambal Bawang</option>
                    <option value="assets/images/dish-11.jpg">Es Cendol Nangka</option>
                    <option value="assets/images/dish-12.jpg">Es Kelapa Jeruk</option>
                    <option value="assets/images/dish-13.jpg">Es Teh Melati</option>
                    <option value="assets/images/dish-14.jpg">Wedang Uwuh</option>
                </select>
            </div>

            <div class="form-group-admin">
                <label for="new_desc">Deskripsi & Racikan Menu</label>
                <textarea id="new_desc" name="description" rows="3" class="form-control-admin" placeholder="Jelaskan aroma, rasa, lauk pelengkap, dan racikan rempah..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-admin btn-admin-secondary" onclick="closeModal('addMenuModal')">Batal</button>
                <button type="submit" class="btn-admin btn-admin-primary">Simpan Menu Baru</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Menu -->
<div class="admin-modal-overlay" id="editMenuModal">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h3>Edit Informasi Menu</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('editMenuModal')">&times;</button>
        </div>
        <form action="menu.php" method="POST" class="admin-modal-body">
            <input type="hidden" name="action" value="edit_menu">
            <input type="hidden" id="edit_id" name="item_id">

            <div class="form-group-admin">
                <label for="edit_name">Nama Menu *</label>
                <input type="text" id="edit_name" name="name" class="form-control-admin" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group-admin">
                    <label for="edit_category">Kategori *</label>
                    <select id="edit_category" name="category_slug" class="form-control-admin" required>
                        <option value="makanan">Makanan Utama</option>
                        <option value="paket">Paket Nasi Kotak</option>
                        <option value="camilan">Camilan & Sambal</option>
                        <option value="minuman">Minuman Segar</option>
                    </select>
                </div>

                <div class="form-group-admin">
                    <label for="edit_price">Harga Jual (Rp) *</label>
                    <input type="number" id="edit_price" name="price" class="form-control-admin" required>
                </div>
            </div>

            <div class="form-group-admin">
                <label for="edit_badge">Badge</label>
                <input type="text" id="edit_badge" name="badge" class="form-control-admin">
            </div>

            <div class="form-group-admin">
                <label for="edit_desc">Deskripsi</label>
                <textarea id="edit_desc" name="description" rows="3" class="form-control-admin"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-admin btn-admin-secondary" onclick="closeModal('editMenuModal')">Batal</button>
                <button type="submit" class="btn-admin btn-admin-primary">Perbarui Menu</button>
            </div>
        </form>
    </div>
</div>

<script>
function editMenu(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_category').value = item.category_slug;
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_badge').value = item.badge || '';
    document.getElementById('edit_desc').value = item.description || '';
    openModal('editMenuModal');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

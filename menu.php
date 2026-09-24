<?php
$page_title = 'Daftar Makanan dan Minuman';
require_once __DIR__ . '/includes/data.php';

$active_cat = $_GET['cat'] ?? 'semua';
$search_query = trim($_GET['q'] ?? '');

// Filter items dari database array
$filtered_menu = [];
foreach ($menu_items as $item) {
    if ($active_cat !== 'semua' && $item['category_slug'] !== $active_cat) {
        continue;
    }
    if (!empty($search_query)) {
        if (stripos($item['name'], $search_query) === false && stripos($item['description'], $search_query) === false) {
            continue;
        }
    }
    $filtered_menu[] = $item;
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="menu-page">
    <!-- Menu Page Header -->
    <section class="menu-hero-header">
        <div class="container text-center">
            <span class="sub-badge">Pilihan Selera Nusantara</span>
            <h1 class="main-title">Daftar Makanan & Minuman</h1>
            <p class="sub-text">Semua masakan disajikan hangat dari dapur dengan resep rumahan segar setiap hari.</p>
            
            <!-- Search & Filter Form (Ikon SVG, Tanpa Emotikon) -->
            <form action="menu.php" method="GET" class="menu-search-bar">
                <input type="hidden" name="cat" value="<?= htmlspecialchars($active_cat); ?>">
                <div class="search-input-wrap">
                    <span class="search-icon-svg">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" name="q" placeholder="Cari masakan, lauk, sambal, atau minuman..." value="<?= htmlspecialchars($search_query); ?>">
                    <?php if (!empty($search_query)): ?>
                        <a href="menu.php?cat=<?= urlencode($active_cat); ?>" class="clear-search" aria-label="Hapus pencarian">&times;</a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Cari Menu</button>
            </form>
        </div>
    </section>

    <!-- Category Tabs -->
    <section class="menu-filter-bar">
        <div class="container">
            <div class="filter-tabs-scroll">
                <a href="menu.php?cat=semua<?= !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" class="filter-tab <?= ($active_cat === 'semua') ? 'active' : ''; ?>">
                    Semua Menu (<?= count($menu_items); ?>)
                </a>
                <a href="menu.php?cat=makanan<?= !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" class="filter-tab <?= ($active_cat === 'makanan') ? 'active' : ''; ?>">
                    Makanan Utama
                </a>
                <a href="menu.php?cat=paket<?= !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" class="filter-tab <?= ($active_cat === 'paket') ? 'active' : ''; ?>">
                    Paket Nasi Kotak
                </a>
                <a href="menu.php?cat=camilan<?= !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" class="filter-tab <?= ($active_cat === 'camilan') ? 'active' : ''; ?>">
                    Camilan & Sambal
                </a>
                <a href="menu.php?cat=minuman<?= !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" class="filter-tab <?= ($active_cat === 'minuman') ? 'active' : ''; ?>">
                    Minuman Segar
                </a>
            </div>
        </div>
    </section>

    <!-- Menu Cards Grid (Foto Nyata, Tanpa AI, Tanpa Emotikon) -->
    <section class="menu-listing-section">
        <div class="container">
            <?php if (empty($filtered_menu)): ?>
                <div class="empty-state-box text-center">
                    <div class="empty-icon-svg">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    </div>
                    <h3>Menu Tidak Ditemukan</h3>
                    <p>Maaf, kata kunci pencarian "<strong><?= htmlspecialchars($search_query); ?></strong>" tidak sesuai dengan menu yang tersedia.</p>
                    <a href="menu.php" class="btn btn-primary mt-3">Tampilkan Semua Menu</a>
                </div>
            <?php else: ?>
                <div class="menu-grid">
                    <?php foreach ($filtered_menu as $item): ?>
                        <div class="food-card" id="item-<?= $item['id']; ?>">
                            <div class="food-card-header">
                                <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="food-card-photo" loading="lazy">
                                <?php if (!empty($item['badge'])): ?>
                                    <span class="food-badge"><?= htmlspecialchars($item['badge']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="food-card-body">
                                <span class="food-category-tag"><?= htmlspecialchars($item['category_slug']); ?></span>
                                <h3 class="food-name"><?= htmlspecialchars($item['name']); ?></h3>
                                <p class="food-desc"><?= htmlspecialchars($item['description']); ?></p>

                                <div class="food-card-footer">
                                    <div class="food-price-wrap">
                                        <span class="price-label">Harga Satuan</span>
                                        <span class="food-price"><?= formatRupiah($item['price']); ?></span>
                                    </div>

                                    <form action="includes/data.php" method="POST" class="add-cart-form">
                                        <input type="hidden" name="action" value="add_to_cart">
                                        <input type="hidden" name="item_id" value="<?= $item['id']; ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <input type="hidden" name="redirect" value="menu.php?cat=<?= urlencode($active_cat); ?>">
                                        
                                        <button type="submit" class="btn btn-primary btn-sm btn-order">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                            <span>+ Tambah</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Floating Mobile Cart Bar -->
    <aside id="floating-cart-bar" class="floating-cart-bar" aria-label="Ringkasan Keranjang Belanja" style="<?= (getCartCount() > 0) ? '' : 'display: none;'; ?>">
        <div class="container floating-cart-inner">
            <div class="floating-cart-info">
                <span id="floating-cart-count" class="floating-cart-count"><?= getCartCount(); ?> Menu Terpilih</span>
                <span id="floating-cart-total" class="floating-cart-total"><?= formatRupiah(getCartSubtotal()); ?></span>
            </div>
            <a href="cart.php" class="btn btn-primary btn-cart-jump">
                <span>Lihat Keranjang &rarr;</span>
            </a>
        </div>
    </aside>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

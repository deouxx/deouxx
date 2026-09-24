<?php
$page_title = 'Beranda - Masakan Rumahan Lezat & Halal';
require_once __DIR__ . '/includes/data.php';

// Ambil 4 menu unggulan dari database
$featured_ids = [1, 2, 3, 11];
$featured_items = [];
foreach ($featured_ids as $fid) {
    if (isset($menu_items[$fid])) {
        $featured_items[] = $menu_items[$fid];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-culinary">
        <div class="container hero-grid">
            <div class="hero-text-content">
                <div class="hero-eyebrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="inline-svg"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>100% Halal & Resep Asli Nusantara</span>
                </div>
                <h1 class="hero-main-title">
                    Kehangatan <span class="highlight-text">Masakan Rumahan</span> Penuh Cita Rasa
                </h1>
                <p class="hero-description">
                    Nikmati kelezatan masakan rumahan autentik dari Dapur Ina Aina. Diracik dengan bumbu rempah pilihan, bahan segar harian, dan dimasak higienis untuk santap hangat keluarga Anda.
                </p>

                <div class="hero-actions">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <span>Pesan Makanan Sekarang</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                    <a href="#about" class="btn btn-outline btn-lg">Cerita Dapur Kami</a>
                </div>

                <div class="hero-trust-badges">
                    <div class="trust-item">
                        <span class="trust-icon-svg">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </span>
                        <div>
                            <strong>Pengantaran Cepat</strong>
                            <p>Tiba hangat dalam 30-45 menit</p>
                        </div>
                    </div>
                    <div class="trust-item">
                        <span class="trust-icon-svg">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </span>
                        <div>
                            <strong>Bahan Segar Harian</strong>
                            <p>Tanpa bahan pengawet</p>
                        </div>
                    </div>
                    <div class="trust-item">
                        <span class="trust-icon-svg">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </span>
                        <div>
                            <strong>Rating 4.9 / 5.0</strong>
                            <p>Dari 2.500+ ulasan pelanggan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Food Showcase Visual (Foto Asli, Tanpa Emotikon, Tanpa AI) -->
            <div class="hero-visual-card">
                <div class="dish-showcase">
                    <div class="dish-image-wrapper">
                        <img src="assets/images/dish-1.jpg" alt="Nasi Liwet Komplit Ina Aina" class="dish-hero-photo" loading="lazy">
                        <span class="dish-photo-badge">Menu Favorit</span>
                    </div>

                    <div class="dish-title-badge">
                        <div class="star-rating">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#facc15" stroke="#facc15"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <span>4.9 (500+ Terjual)</span>
                        </div>
                        <h3>Nasi Liwet Komplit Ina Aina</h3>
                        <p class="dish-price">Rp 38.000</p>
                    </div>

                    <!-- Floating Mini Card 1 -->
                    <div class="floating-promo top-badge">
                        <div class="promo-icon-svg">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2"><path d="M12 2c1 3 2.5 4.5 4.5 5.5 2.5 1.5 3.5 3.5 3.5 6.5a8 8 0 1 1-16 0c0-3 1-5 3.5-6.5C9.5 6.5 11 5 12 2z"></path></svg>
                        </div>
                        <div>
                            <strong>Menu Paling Dicari!</strong>
                            <p>Terjual 300+ porsi setiap hari</p>
                        </div>
                    </div>

                    <!-- Floating Mini Card 2 -->
                    <div class="floating-promo bottom-badge">
                        <div class="promo-icon-svg">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
                        </div>
                        <div>
                            <strong>Kupon Gratis Ongkir</strong>
                            <p>Gunakan kode: <span class="promo-code">INAHEMAT</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kategori Menu Populer -->
    <section class="categories-section">
        <div class="container">
            <div class="section-title-wrap text-center">
                <span class="sub-badge">Kategori Hidangan</span>
                <h2 class="main-title">Pilihan Menu Spesial Hari Ini</h2>
                <p class="sub-text">Dari olahan lauk kaya bumbu rempah hingga minuman segar pelepas dahaga.</p>
            </div>

            <div class="category-grid">
                <a href="menu.php?cat=makanan" class="category-card">
                    <div class="category-img-wrap">
                        <img src="assets/images/dish-2.jpg" alt="Makanan Utama" loading="lazy">
                    </div>
                    <h3 class="category-title">Makanan Utama</h3>
                    <p class="category-count">Ayam Lengkuas, Rendang, Gurame, dll</p>
                </a>

                <a href="menu.php?cat=paket" class="category-card">
                    <div class="category-img-wrap">
                        <img src="assets/images/dish-6.jpg" alt="Paket Nasi Kotak" loading="lazy">
                    </div>
                    <h3 class="category-title">Paket Nasi Kotak</h3>
                    <p class="category-count">Praktis & Lengkap untuk makan siang / acara</p>
                </a>

                <a href="menu.php?cat=camilan" class="category-card">
                    <div class="category-img-wrap">
                        <img src="assets/images/dish-8.jpg" alt="Camilan & Sambal" loading="lazy">
                    </div>
                    <h3 class="category-title">Camilan & Sambal</h3>
                    <p class="category-count">Bakwan Jagung, Tahu Gejrot, Sambal Bawang</p>
                </a>

                <a href="menu.php?cat=minuman" class="category-card">
                    <div class="category-img-wrap">
                        <img src="assets/images/dish-11.jpg" alt="Minuman Segar" loading="lazy">
                    </div>
                    <h3 class="category-title">Minuman Segar</h3>
                    <p class="category-count">Es Cendol Nangka, Kelapa Jeruk, Wedang</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Menu Unggulan (Best Seller) -->
    <section class="featured-section">
        <div class="container">
            <div class="section-title-wrap flex-between">
                <div>
                    <span class="sub-badge">Paling Disukai</span>
                    <h2 class="main-title">Menu Andalan Dapur Ina Aina</h2>
                </div>
                <a href="menu.php" class="btn btn-outline btn-sm">Lihat Semua Menu &rarr;</a>
            </div>

            <div class="menu-grid">
                <?php foreach ($featured_items as $item): ?>
                    <div class="food-card">
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
                                    <span class="price-label">Harga</span>
                                    <span class="food-price"><?= formatRupiah($item['price']); ?></span>
                                </div>

                                <form action="includes/data.php" method="POST">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="item_id" value="<?= $item['id']; ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="redirect" value="cart.php">
                                    <button type="submit" class="btn btn-primary btn-sm btn-order">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        <span>Pesan</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Alur Pemesanan 4 Langkah Mudah -->
    <section class="steps-section">
        <div class="container">
            <div class="section-title-wrap text-center">
                <span class="sub-badge">Mudah & Cepat</span>
                <h2 class="main-title">Cara Pesan di Dapur Ina Aina</h2>
                <p class="sub-text">Hanya butuh beberapa menit sampai hidangan favorit Anda dipersiapkan dengan cinta.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon-svg">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <h3>Pilih Menu Favorit</h3>
                    <p>Buka daftar makanan & minuman, pilih lauk dan minuman yang Anda inginkan lalu klik pesan.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon-svg">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </div>
                    <h3>Atur di Keranjang</h3>
                    <p>Sesuaikan jumlah porsi, tulis catatan selera (misal: sambal dipisah), dan gunakan kupon promo.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon-svg">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18.5" cy="17.5" r="3.5"></circle><circle cx="5.5" cy="17.5" r="3.5"></circle><circle cx="15" cy="5" r="1"></circle><path d="M12 17.5V14l-3-3 4-3 2 3h2"></path></svg>
                    </div>
                    <h3>Pilih Pengiriman</h3>
                    <p>Pilih antar ke alamat (Delivery), makan di tempat (Dine-In), atau ambil sendiri di dapur kami.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon-svg">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    </div>
                    <h3>Bayar & Terima Struk</h3>
                    <p>Bayar tunai (COD/kasir) atau transfer/QRIS, dapatkan struk resmi dan hidangan lezat siap dinikmati.</p>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="menu.php" class="btn btn-primary btn-lg">Mulai Pilih Menu Sekarang</a>
            </div>
        </div>
    </section>

    <!-- Tentang Kami Section -->
    <section id="about" class="about-dapur-section">
        <div class="container about-grid">
            <div class="about-image-column">
                <div class="about-card-banner">
                    <div class="banner-badge">Resep Asli Keluarga</div>
                    <div class="banner-photo-wrap">
                        <img src="assets/images/dish-3.jpg" alt="Masakan Dapur Ina Aina" class="about-kitchen-photo" loading="lazy">
                    </div>
                    <h3>Dapur Ina Aina</h3>
                    <p class="banner-sub">Berdiri sejak 2018 di Jakarta Selatan</p>
                    <div class="banner-stats">
                        <div class="b-stat">
                            <strong>15.000+</strong>
                            <span>Porsi Terkirim</span>
                        </div>
                        <div class="b-stat">
                            <strong>100%</strong>
                            <span>Bahan Alami</span>
                        </div>
                        <div class="b-stat">
                            <strong>Halal</strong>
                            <span>Terverifikasi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="about-text-column">
                <span class="sub-badge">Cerita Kami</span>
                <h2 class="main-title">Menghadirkan Rasa Kangen Masakan Ibu ke Meja Makan Anda</h2>
                <p class="paragraph">
                    Dapur Ina Aina bermula dari dapur rumah sederhana dengan satu impian: menyajikan hidangan rumahan yang kaya rempah, sehat, dan dimasak tanpa jalan pintas. Setiap sambal diulek dengan rempah segar, santan diperas dari kelapa pilihan, dan lauk dimasak dengan teknik tradisional warisan keluarga.
                </p>
                <div class="value-checks">
                    <div class="check-item">
                        <span class="check-icon-svg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </span>
                        <div>
                            <strong>Bahan Segar Setiap Hari</strong>
                            <p>Belanja subuh ke pasar lokal pilihan untuk menjamin kesegaran sayur, daging, dan ikan.</p>
                        </div>
                    </div>
                    <div class="check-item">
                        <span class="check-icon-svg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </span>
                        <div>
                            <strong>Higienis & Bersih</strong>
                            <p>Standar kebersihan ketat dari persiapan bahan hingga pengemasan ramah lingkungan.</p>
                        </div>
                    </div>
                    <div class="check-item">
                        <span class="check-icon-svg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </span>
                        <div>
                            <strong>Siap Melayani Katering & Acara</strong>
                            <p>Melayani pesanan nasi kotak harian, rapat kantor, pengajian, dan syukuran keluarga.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kontak & Konsultasi Pesanan -->
    <section id="contact" class="contact-dapur-section">
        <div class="container">
            <div class="contact-box-container">
                <div class="contact-left">
                    <span class="sub-badge light">Layanan Pelanggan</span>
                    <h2>Punya Pertanyaan atau Ingin Pesan Katering Nasi Kotak?</h2>
                    <p>Hubungi tim Dapur Ina Aina langsung melalui pesan WhatsApp atau kunjungi lokasi dapur kami.</p>

                    <div class="contact-actions-row">
                        <a href="https://wa.me/6281234567890?text=Halo%20Dapur%20Ina%20Aina,%20saya%20ingin%20tanya%20menu" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            <span>Chat WhatsApp: 0812-3456-7890</span>
                        </a>
                        <a href="menu.php" class="btn btn-outline-light btn-lg">Buka Daftar Menu Online</a>
                    </div>
                </div>

                <div class="contact-right">
                    <div class="contact-detail-card">
                        <h4>Lokasi Dapur</h4>
                        <p>Jl. Flamboyan Raya No. 18, Kebayoran Baru, Jakarta Selatan (Patokan dekat Taman Flamboyan)</p>
                        
                        <h4>Jam Masak & Pengantaran</h4>
                        <p>Senin – Minggu: 08.00 – 21.00 WIB (Pesanan terakhir jam 20.30 WIB)</p>

                        <h4>Pesan Jumlah Besar (Katering)</h4>
                        <p>Untuk pesanan di atas 30 porsi, mohon konfirmasi H-1 agar persiapan bumbu lebih maksimal.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

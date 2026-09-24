    <!-- Site Footer (Tanpa Emotikon, Menggunakan SVG Bersih) -->
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col brand-col">
                <div class="footer-brand">
                    <div class="logo-badge">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                            <line x1="6" y1="1" x2="6" y2="4"></line>
                            <line x1="10" y1="1" x2="10" y2="4"></line>
                            <line x1="14" y1="1" x2="14" y2="4"></line>
                        </svg>
                    </div>
                    <span class="brand-title">Dapur Ina Aina<span class="brand-dot">.</span></span>
                </div>
                <p class="footer-desc">
                    Menyajikan kehangatan hidangan masakan rumahan autentik dengan bumbu rempah pilihan, higienis, dan 100% halal. Dimasak segar setiap hari untuk keluarga Anda.
                </p>
                <div class="footer-halal-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>100% Halal Certified & Fresh Homemade</span>
                </div>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Navigasi Utama</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="menu.php">Daftar Makanan & Minuman</a></li>
                    <li><a href="cart.php">Keranjang Belanja</a></li>
                    <li><a href="index.php#about">Cerita Dapur Kami</a></li>
                    <li><a href="index.php#contact">Hubungi Kami</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Layanan Kami</h4>
                <ul class="footer-links">
                    <li><a href="menu.php?cat=makanan">Masakan Lauk Rumahan</a></li>
                    <li><a href="menu.php?cat=paket">Paket Nasi Kotak & Bento</a></li>
                    <li><a href="menu.php?cat=camilan">Camilan Tradisional & Gorengan</a></li>
                    <li><a href="menu.php?cat=minuman">Minuman Segar Tradisional</a></li>
                    <li><a href="menu.php">Pesanan Katering & Syukuran</a></li>
                </ul>
            </div>

            <div class="footer-col contact-col">
                <h4 class="footer-heading">Jam & Lokasi Dapur</h4>
                <ul class="footer-contact-info">
                    <li>
                        <span class="icon-svg">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </span>
                        <div>
                            <strong>Jam Operasional:</strong><br>
                            Setiap Hari: 08:00 - 21:00 WIB
                        </div>
                    </li>
                    <li>
                        <span class="icon-svg">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </span>
                        <div>
                            <strong>Alamat Dapur:</strong><br>
                            Jl. Flamboyan Raya No. 18, Kebayoran Baru, Jakarta Selatan
                        </div>
                    </li>
                    <li>
                        <span class="icon-svg">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        </span>
                        <div>
                            <strong>WhatsApp Pesanan:</strong><br>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener">+62 812-3456-7890</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <p>&copy; <?= date('Y'); ?> <strong>Dapur Ina Aina</strong>. Seluruh Hak Cipta Dilindungi Undang-Undang.</p>
                <div class="footer-payments">
                    <span>Metode Pembayaran:</span>
                    <span class="pay-tag">Tunai / COD</span>
                    <span class="pay-tag">QRIS</span>
                    <span class="pay-tag">BCA</span>
                    <span class="pay-tag">Mandiri</span>
                    <span class="pay-tag">Debit/Kredit</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script JS -->
    <script src="script.js"></script>
</body>
</html>

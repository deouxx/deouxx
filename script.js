/**
 * Dapur Ina Aina - Modern Culinary Interactions
 */
document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('header');
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');

    // 1. Mobile Menu Toggle
    function toggleMenu(forceClose = false) {
        if (!menuToggle || !navMenu) return;
        const shouldOpen = forceClose ? false : !navMenu.classList.contains('is-active');

        if (shouldOpen) {
            menuToggle.classList.add('is-open');
            navMenu.classList.add('is-active');
            menuToggle.setAttribute('aria-expanded', 'true');
        } else {
            menuToggle.classList.remove('is-open');
            navMenu.classList.remove('is-active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });
    }

    // Dismiss when clicking outside
    document.addEventListener('click', (e) => {
        if (navMenu && navMenu.classList.contains('is-active')) {
            if (!header.contains(e.target)) {
                toggleMenu(true);
            }
        }
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navMenu && navMenu.classList.contains('is-active')) {
            toggleMenu(true);
        }
    });

    // Close on nav link click
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            toggleMenu(true);
        });
    });

    // 2. Sticky Navbar state on scroll
    const handleScroll = () => {
        if (window.scrollY > 25) {
            header?.classList.add('is-scrolled');
        } else {
            header?.classList.remove('is-scrolled');
        }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();

    // 3. Auto-hide alerts after 4.5 seconds
    const alerts = document.querySelectorAll('.global-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4500);
    });

    // 4. Toast Notification Utility
    function showToast(message) {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'toast-item';
        toast.innerHTML = `
            <span class="toast-icon-svg">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </span>
            <span class="toast-text">${message}</span>
        `;
        toastContainer.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 350);
        }, 3200);
    }

    // 5. AJAX Add to Cart with Instant Feedback
    document.querySelectorAll('.add-cart-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            
            const formData = new FormData(form);
            formData.append('ajax', '1');

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                const response = await fetch(form.getAttribute('action') || 'includes/data.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        // Update navbar cart badge counter
                        const cartCounter = document.getElementById('cart-counter');
                        if (cartCounter) {
                            cartCounter.textContent = data.cart_count;
                            cartCounter.classList.remove('badge-bounce');
                            void cartCounter.offsetWidth; // Trigger DOM reflow
                            cartCounter.classList.add('badge-bounce');
                        }

                        // Update floating cart bar
                        const floatingCartBar = document.getElementById('floating-cart-bar');
                        const floatingCartCount = document.getElementById('floating-cart-count');
                        const floatingCartTotal = document.getElementById('floating-cart-total');
                        if (floatingCartBar && floatingCartCount && floatingCartTotal) {
                            floatingCartCount.textContent = `${data.cart_count} Menu Terpilih`;
                            floatingCartTotal.textContent = data.subtotal_formatted;
                            floatingCartBar.style.display = 'block';
                        }

                        // Button feedback state
                        if (submitBtn) {
                            submitBtn.classList.add('btn-added-state');
                            submitBtn.innerHTML = `
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Ditambahkan!</span>
                            `;
                            setTimeout(() => {
                                submitBtn.classList.remove('btn-added-state');
                                submitBtn.innerHTML = originalBtnHtml;
                                submitBtn.disabled = false;
                            }, 1400);
                        }

                        showToast(`<strong>${data.item_name}</strong> ditambahkan ke keranjang!`);
                    } else {
                        form.submit();
                    }
                } else {
                    form.submit();
                }
            } catch (err) {
                form.submit();
            }
        });
    });
});

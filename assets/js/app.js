const BM = (() => {
    const BASE = '/basta-masarap';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function showToast(message, type = 'success') {
        const stack = document.getElementById('toastStack');
        if (!stack) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        stack.appendChild(toast);
        setTimeout(() => toast.remove(), 3200);
    }

    async function api(url, payload) {
        const res = await fetch(BASE + url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ...payload, csrf_token: CSRF })
        });
        let data;
        try { data = await res.json(); } catch (e) { data = { success: false, message: 'Unexpected server response.' }; }
        if (!res.ok && !data.message) data.message = 'Something went wrong. Please try again.';
        return data;
    }

    function initNav() {
        const toggle = document.getElementById('mobileToggle');
        const links = document.getElementById('navLinks');
        if (toggle && links) {
            toggle.addEventListener('click', () => links.classList.toggle('open'));
        }
        const userToggle = document.getElementById('userMenuToggle');
        const userMenu = document.getElementById('userMenu');
        if (userToggle && userMenu) {
            userToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('open');
            });
            document.addEventListener('click', () => userMenu.classList.remove('open'));
        }
    }

    function initModals() {
        document.querySelectorAll('[data-modal-open]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = document.getElementById(btn.dataset.modalOpen);
                if (modal) modal.classList.add('open');
            });
        });
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', () => btn.closest('.modal-overlay')?.classList.remove('open'));
        });
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('open'); });
        });
    }

    function updateCartBadge(count) {
        let badge = document.querySelector('.cart-badge');
        const cartBtn = document.querySelector('.icon-btn[title]');
        if (count > 0) {
            if (!badge && cartBtn) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                cartBtn.appendChild(badge);
            }
            if (badge) badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    async function addToCart(dishId, qty = 1) {
        const data = await api('/ajax/cart.php', { action: 'add', dish_id: dishId, qty });
        if (data.success) {
            updateCartBadge(data.cart_count);
            showToast(data.message || 'Added to cart!');
        } else {
            showToast(data.message || 'Could not add to cart.', 'error');
            if (data.require_login) setTimeout(() => window.location.href = BASE + '/login.php', 900);
        }
        return data;
    }

    function initAddToCartButtons() {
        document.querySelectorAll('[data-add-cart]').forEach(btn => {
            btn.addEventListener('click', () => addToCart(btn.dataset.addCart, 1));
        });
    }

    function initCartPage() {
        const container = document.getElementById('cartItems');
        if (!container) return;

        container.addEventListener('click', async (e) => {
            const row = e.target.closest('.cart-item');
            if (!row) return;
            const dishId = row.dataset.dishId;

            if (e.target.matches('[data-qty-increase]') || e.target.matches('[data-qty-decrease]')) {
                const delta = e.target.matches('[data-qty-increase]') ? 1 : -1;
                const data = await api('/ajax/cart.php', { action: 'update', dish_id: dishId, delta });
                if (data.success) location.reload(); else showToast(data.message, 'error');
            }
            if (e.target.matches('[data-remove-item]')) {
                const data = await api('/ajax/cart.php', { action: 'remove', dish_id: dishId });
                if (data.success) location.reload(); else showToast(data.message, 'error');
            }
        });
    }

    function initReactions() {
        document.querySelectorAll('[data-react]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const dishId = btn.dataset.dishId;
                const type = btn.dataset.react;
                const data = await api('/ajax/react.php', { dish_id: dishId, type });
                if (data.success) {
                    document.querySelectorAll(`[data-dish-id="${dishId}"][data-react]`).forEach(b => b.classList.remove('active'));
                    if (data.reaction) btn.classList.add('active');
                    const likeCountEl = document.querySelector(`[data-like-count="${dishId}"]`);
                    const dislikeCountEl = document.querySelector(`[data-dislike-count="${dishId}"]`);
                    if (likeCountEl) likeCountEl.textContent = data.likes;
                    if (dislikeCountEl) dislikeCountEl.textContent = data.dislikes;
                } else {
                    showToast(data.message || 'Please log in first.', 'error');
                }
            });
        });
    }

    function initFavoriteWishlist() {
        document.querySelectorAll('[data-favorite]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const data = await api('/ajax/favorite.php', { dish_id: btn.dataset.dishId });
                if (data.success) {
                    btn.classList.toggle('active', data.active);
                    showToast(data.message);
                } else showToast(data.message || 'Please log in first.', 'error');
            });
        });
        document.querySelectorAll('[data-wishlist]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const data = await api('/ajax/wishlist.php', { dish_id: btn.dataset.dishId });
                if (data.success) {
                    btn.classList.toggle('active', data.active);
                    showToast(data.message);
                } else showToast(data.message || 'Please log in first.', 'error');
            });
        });
    }

    function initMenuFilters() {
        const form = document.getElementById('menuFilterForm');
        if (!form) return;
        let debounceTimer;
        const search = form.querySelector('[name="search"]');
        if (search) {
            search.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => form.submit(), 450);
            });
        }
        form.querySelectorAll('select').forEach(sel => sel.addEventListener('change', () => form.submit()));
    }

    function initStarRating() {
        document.querySelectorAll('.star-rating-input').forEach(group => {
            const stars = group.querySelectorAll('span');
            const input = document.getElementById(group.dataset.for);
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const val = parseInt(star.dataset.value, 10);
                    input.value = val;
                    stars.forEach(s => s.classList.toggle('filled', parseInt(s.dataset.value, 10) <= val));
                });
            });
        });
    }

    function initFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach(field => {
                    const errorEl = field.parentElement.querySelector('.form-error');
                    if (!field.value.trim()) {
                        valid = false;
                        if (!errorEl) {
                            const err = document.createElement('div');
                            err.className = 'form-error';
                            err.textContent = 'This field is required.';
                            field.parentElement.appendChild(err);
                        }
                    } else if (errorEl) {
                        errorEl.remove();
                    }
                });
                const pw = form.querySelector('[name="password"]');
                const pw2 = form.querySelector('[name="confirm_password"]');
                if (pw && pw2 && pw.value !== pw2.value) {
                    valid = false;
                    showToast('Passwords do not match.', 'error');
                }
                if (!valid) e.preventDefault();
            });
        });
    }

    function init() {
        initNav();
        initModals();
        initAddToCartButtons();
        initCartPage();
        initReactions();
        initFavoriteWishlist();
        initMenuFilters();
        initStarRating();
        initFormValidation();
    }

    document.addEventListener('DOMContentLoaded', init);

    return { addToCart, showToast, api };
})();

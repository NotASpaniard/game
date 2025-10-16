// Main JavaScript for GameStore
// Utility Functions
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

// AJAX Helper
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, message: 'Có lỗi xảy ra khi tải dữ liệu' };
    }
}

// Notification System - Import từ file riêng
// Đã được di chuyển vào assets/js/notifications.js
class NotificationManager {
    static show(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        // Style notification
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '8px',
            color: 'white',
            zIndex: '9999',
            minWidth: '300px',
            maxWidth: '400px',
            backgroundColor: this.getColor(type),
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease'
        });

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    static getIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    static getColor(type) {
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        return colors[type] || '#3b82f6';
    }
}

// Cart Management
class CartManager {
    static async addToCart(productId, quantity = 1) {
        try {
            const result = await fetchData('api/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            });

            if (result.success) {
                NotificationManager.show('Đã thêm vào giỏ hàng!', 'success');
                this.updateCartCount();
            } else {
                NotificationManager.show(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            NotificationManager.show('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
        }
    }

    static async removeFromCart(productId) {
        try {
            const result = await fetchData('api/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            });

            if (result.success) {
                NotificationManager.show('Đã xóa khỏi giỏ hàng', 'success');
                this.updateCartCount();
            }
        } catch (error) {
            console.error('Remove from cart error:', error);
        }
    }

    static async updateCartCount() {
        try {
            const result = await fetchData('api/cart.php?action=count');
            const cartCount = $('.cart-count');
            if (cartCount) {
                cartCount.textContent = result.count || 0;
                cartCount.style.display = result.count > 0 ? 'block' : 'none';
            }
        } catch (error) {
            console.error('Update cart count error:', error);
        }
    }
}

// Wishlist Management
class WishlistManager {
    static async toggleWishlist(productId) {
        try {
            const result = await fetchData('api/wishlist.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'toggle',
                    product_id: productId
                })
            });

            if (result.success) {
                const message = result.added ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích';
                NotificationManager.show(message, 'success');
                this.updateWishlistButton(productId, result.added);
            }
        } catch (error) {
            console.error('Wishlist toggle error:', error);
        }
    }

    static updateWishlistButton(productId, isAdded) {
        const button = $(`.wishlist-btn[data-product-id="${productId}"]`);
        if (button) {
            const icon = button.querySelector('i');
            if (isAdded) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('active');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('active');
            }
        }
    }
}

// Search Functionality
class SearchManager {
    static init() {
        const searchInput = $('.search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    }

    static async performSearch(query) {
        if (query.length < 2) return;

        try {
            const result = await fetchData(`api/search.php?q=${encodeURIComponent(query)}`);
            this.displaySearchResults(result);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    static displaySearchResults(results) {
        // Implement search results display
        console.log('Search results:', results);
    }
}

// Product Quick View
class QuickViewManager {
    static async show(productId) {
        try {
            const result = await fetchData(`api/product.php?id=${productId}&action=quickview`);
            if (result.success) {
                this.displayModal(result.product);
            }
        } catch (error) {
            console.error('Quick view error:', error);
        }
    }

    static displayModal(product) {
        const modal = document.createElement('div');
        modal.className = 'quick-view-modal active';
        modal.innerHTML = `
            <div class="quick-view-content">
                <div class="quick-view-header">
                    <h3>${product.name}</h3>
                    <button class="close-btn" onclick="this.closest('.quick-view-modal').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="quick-view-body">
                    <div class="product-image">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                    <div class="product-details">
                        <div class="product-price">
                            <span class="price-current">${this.formatPrice(product.price)}đ</span>
                        </div>
                        <p class="product-description">${product.description}</p>
                        <div class="product-actions">
                            <button class="btn btn-primary add-to-cart-btn" data-product-id="${product.id}">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                            </button>
                            <button class="btn btn-outline wishlist-btn" data-product-id="${product.id}">
                                <i class="fas fa-heart"></i> Yêu thích
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Add event listeners
        modal.querySelector('.add-to-cart-btn').addEventListener('click', (e) => {
            CartManager.addToCart(e.target.dataset.productId);
        });

        modal.querySelector('.wishlist-btn').addEventListener('click', (e) => {
            WishlistManager.toggleWishlist(e.target.dataset.productId);
        });
    }

    static formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
}

// Form Validation
class FormValidator {
    static init() {
        const forms = $$('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    }

    static validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            if (!this.validateInput(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    static validateInput(input) {
        const value = input.value.trim();
        const type = input.type;
        let isValid = true;

        // Required validation
        if (input.hasAttribute('required') && !value) {
            this.showError(input, 'Trường này là bắt buộc');
            isValid = false;
        }

        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showError(input, 'Email không hợp lệ');
                isValid = false;
            }
        }

        // Phone validation
        if (type === 'tel' && value) {
            const phoneRegex = /^[0-9+\-\s()]+$/;
            if (!phoneRegex.test(value)) {
                this.showError(input, 'Số điện thoại không hợp lệ');
                isValid = false;
            }
        }

        // Password validation
        if (type === 'password' && value) {
            if (value.length < 6) {
                this.showError(input, 'Mật khẩu phải có ít nhất 6 ký tự');
                isValid = false;
            }
        }

        if (isValid) {
            this.clearError(input);
        }

        return isValid;
    }

    static showError(input, message) {
        this.clearError(input);

        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        error.style.color = 'var(--error-color)';
        error.style.fontSize = '0.75rem';
        error.style.marginTop = '0.25rem';

        input.parentNode.appendChild(error);
        input.style.borderColor = 'var(--error-color)';
    }

    static clearError(input) {
        const existingError = input.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        input.style.borderColor = '';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.querySelector('.nav');

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }

    // Initialize cart
    CartManager.updateCartCount();

    // Initialize search
    SearchManager.init();

    // Initialize form validation
    FormValidator.init();

    // Add to cart buttons
    $$('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            CartManager.addToCart(productId);
        });
    });

    // Wishlist buttons
    $$('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            WishlistManager.toggleWishlist(productId);
        });
    });

    // Quick view buttons
    $$('.quick-view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            QuickViewManager.show(productId);
        });
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quick-view-modal')) {
            e.target.remove();
        }
    });

    // Smooth scrolling for anchor links
    $$('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});

// Export for global access
window.CartManager = CartManager;
window.WishlistManager = WishlistManager;
window.NotificationManager = NotificationManager;
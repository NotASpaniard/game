// Cart Page JavaScript

// Cart Management
class CartPageManager {
    static init() {
        this.bindEvents();
        this.updateCartSummary();
    }

    static bindEvents() {
        // Quantity controls
        $$('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = btn.dataset.productId;
                const isIncrease = btn.classList.contains('increase');
                this.updateQuantity(productId, isIncrease);
            });
        });

        // Quantity input changes
        $$('.quantity-input').forEach(input => {
            input.addEventListener('change', (e) => {
                const productId = input.dataset.productId;
                const quantity = parseInt(input.value);
                this.setQuantity(productId, quantity);
            });
        });

        // Remove item buttons
        $$('.remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = btn.dataset.productId;
                this.removeItem(productId);
            });
        });

        // Move to wishlist buttons
        $$('.move-to-wishlist').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = btn.dataset.productId;
                this.moveToWishlist(productId);
            });
        });

        // Clear cart button
        const clearCartBtn = $('#clear-cart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', () => {
                this.clearCart();
            });
        }

        // Checkout form
        const checkoutForm = $('#checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.processCheckout(checkoutForm);
            });
        }
    }

    static async updateQuantity(productId, isIncrease) {
        const input = $(`.quantity-input[data-product-id="${productId}"]`);
        if (!input) return;

        const currentQuantity = parseInt(input.value);
        const newQuantity = isIncrease ? currentQuantity + 1 : Math.max(1, currentQuantity - 1);

        await this.setQuantity(productId, newQuantity);
    }

    static async setQuantity(productId, quantity) {
        if (quantity < 1) {
            await this.removeItem(productId);
            return;
        }

        const cartItem = $(`.cart-item[data-product-id="${productId}"]`);
        if (!cartItem) return;

        cartItem.classList.add('updating');

        try {
            const result = await fetchData('api/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: quantity
                })
            });

            if (result.success) {
                // Update UI
                const input = cartItem.querySelector('.quantity-input');
                const totalPrice = cartItem.querySelector('.total-price');
                const pricePerUnit = cartItem.querySelector('.price-per-unit').textContent;
                const price = parseInt(pricePerUnit.replace(/[^\d]/g, ''));
                const newTotal = price * quantity;

                input.value = quantity;
                totalPrice.textContent = this.formatPrice(newTotal) + 'đ';

                this.updateCartSummary();
                NotificationManager.show('Đã cập nhật số lượng', 'success');
            } else {
                NotificationManager.show(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Update quantity error:', error);
            NotificationManager.show('Có lỗi xảy ra khi cập nhật', 'error');
        } finally {
            cartItem.classList.remove('updating');
        }
    }

    static async removeItem(productId) {
        const cartItem = $(`.cart-item[data-product-id="${productId}"]`);
        if (!cartItem) return;

        if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            return;
        }

        cartItem.classList.add('updating');

        try {
            const result = await fetchData('api/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            });

            if (result.success) {
                cartItem.classList.add('removing');
                setTimeout(() => {
                    cartItem.remove();
                    this.updateCartSummary();
                    this.checkEmptyCart();
                }, 300);

                NotificationManager.show('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
            } else {
                NotificationManager.show(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Remove item error:', error);
            NotificationManager.show('Có lỗi xảy ra khi xóa sản phẩm', 'error');
        } finally {
            cartItem.classList.remove('updating');
        }
    }

    static async moveToWishlist(productId) {
        try {
            const result = await fetchData('api/wishlist.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId
                })
            });

            if (result.success) {
                NotificationManager.show('Đã thêm vào danh sách yêu thích', 'success');
                // Optionally remove from cart
                await this.removeItem(productId);
            } else {
                NotificationManager.show(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Move to wishlist error:', error);
            NotificationManager.show('Có lỗi xảy ra', 'error');
        }
    }

    static async clearCart() {
        if (!confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?')) {
            return;
        }

        try {
            const result = await fetchData('api/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'clear'
                })
            });

            if (result.success) {
                location.reload();
            } else {
                NotificationManager.show(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Clear cart error:', error);
            NotificationManager.show('Có lỗi xảy ra', 'error');
        }
    }

    static async processCheckout(form) {
        const formData = new FormData(form);
        const checkoutBtn = $('#checkout-btn');
        const originalText = checkoutBtn.textContent;

        // Validate form
        const shippingAddress = formData.get('shipping_address').trim();
        const shippingPhone = formData.get('shipping_phone').trim();

        if (!shippingAddress || !shippingPhone) {
            NotificationManager.show('Vui lòng nhập đầy đủ thông tin giao hàng', 'error');
            return;
        }

        // Show loading state
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

        try {
            // Get cart items
            const cartResult = await fetchData('api/cart.php?action=get');
            if (!cartResult.success) {
                throw new Error('Không thể lấy thông tin giỏ hàng');
            }

            const cartItems = cartResult.items.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity
            }));

            // Create order
            const orderResult = await fetchData('api/orders.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'create',
                    cart_items: cartItems,
                    shipping_address: shippingAddress,
                    shipping_phone: shippingPhone,
                    payment_method: formData.get('payment_method'),
                    notes: formData.get('notes')
                })
            });

            if (orderResult.success) {
                NotificationManager.show('Đặt hàng thành công!', 'success');

                // Redirect to orders page
                setTimeout(() => {
                    window.location.href = 'don-hang.php';
                }, 2000);
            } else {
                throw new Error(orderResult.message || 'Có lỗi xảy ra khi đặt hàng');
            }
        } catch (error) {
            console.error('Checkout error:', error);
            NotificationManager.show(error.message || 'Có lỗi xảy ra khi đặt hàng', 'error');
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = originalText;
        }
    }

    static updateCartSummary() {
        const cartItems = $$('.cart-item');
        let subtotal = 0;

        cartItems.forEach(item => {
            const totalPrice = item.querySelector('.total-price');
            const price = parseInt(totalPrice.textContent.replace(/[^\d]/g, ''));
            subtotal += price;
        });

        const subtotalElement = $('#subtotal');
        const totalElement = $('#total-amount');

        if (subtotalElement) {
            subtotalElement.textContent = this.formatPrice(subtotal) + 'đ';
        }

        if (totalElement) {
            totalElement.textContent = this.formatPrice(subtotal) + 'đ';
        }
    }

    static checkEmptyCart() {
        const cartItems = $$('.cart-item');
        if (cartItems.length === 0) {
            // Show empty cart message
            const cartLayout = $('.cart-layout');
            if (cartLayout) {
                cartLayout.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-content">
                            <i class="fas fa-shopping-cart"></i>
                            <h2>Giỏ hàng trống</h2>
                            <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                            <a href="../san-pham/" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                            </a>
                        </div>
                    </div>
                `;
            }
        }
    }

    static formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
}

// Form Validation
class CheckoutFormValidator {
    static init() {
        this.bindEvents();
    }

    static bindEvents() {
        const form = $('#checkout-form');
        if (!form) return;

        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                this.clearFieldError(input);
            });
        });
    }

    static validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');

        if (isRequired && !value) {
            this.showFieldError(field, 'Trường này là bắt buộc');
            return false;
        }

        if (field.type === 'tel' && value) {
            const phoneRegex = /^[0-9+\-\s()]+$/;
            if (!phoneRegex.test(value)) {
                this.showFieldError(field, 'Số điện thoại không hợp lệ');
                return false;
            }
        }

        this.clearFieldError(field);
        return true;
    }

    static showFieldError(field, message) {
        this.clearFieldError(field);

        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        error.style.cssText = `
            color: var(--error-color);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        `;

        field.parentNode.appendChild(error);
        field.style.borderColor = 'var(--error-color)';
    }

    static clearFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.style.borderColor = '';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    CartPageManager.init();
    CheckoutFormValidator.init();

    // Initialize cart count
    CartManager.updateCartCount();
});

// Export for global access
window.CartPageManager = CartPageManager;
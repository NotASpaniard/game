// Products Page JavaScript

// Filter Management
class FilterManager {
    static init() {
        this.bindEvents();
        this.initMobileFilters();
    }

    static bindEvents() {
        // Filter form submission
        const filterForm = $('.filters-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }

        // Real-time filtering for some inputs
        const searchInput = $('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.applyFilters();
                }, 500);
            });
        }

        // Price range filtering
        const priceInputs = $$('input[name="price_min"], input[name="price_max"]');
        priceInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.applyFilters();
            });
        });
    }

    static initMobileFilters() {
        // Create filter toggle button for mobile
        const filterToggle = document.createElement('button');
        filterToggle.className = 'filter-toggle';
        filterToggle.innerHTML = '<i class="fas fa-filter"></i> Bộ lọc';
        filterToggle.addEventListener('click', () => {
            const filtersCard = $('.filters-card');
            filtersCard.classList.toggle('active');
        });

        const productsHeader = $('.products-header');
        if (productsHeader) {
            productsHeader.appendChild(filterToggle);
        }
    }

    static applyFilters() {
        const form = $('.filters-form');
        const formData = new FormData(form);
        const params = new URLSearchParams();

        // Add all form data to URL params
        for (let [key, value] of formData.entries()) {
            if (value.trim()) {
                params.append(key, value);
            }
        }

        // Redirect to filtered results
        window.location.href = `index.php?${params.toString()}`;
    }

    static clearFilters() {
        const form = $('.filters-form');
        form.reset();
        window.location.href = 'index.php';
    }
}

// Product Grid Management
class ProductGridManager {
    static init() {
        this.bindProductEvents();
        this.initInfiniteScroll();
    }

    static bindProductEvents() {
        // Add to cart buttons
        $$('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                CartManager.addToCart(productId);
            });
        });

        // Wishlist buttons
        $$('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                WishlistManager.toggleWishlist(productId);
            });
        });

        // Quick view buttons
        $$('.quick-view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                QuickViewManager.show(productId);
            });
        });
    }

    static initInfiniteScroll() {
        let isLoading = false;
        let currentPage = 1;
        const totalPages = parseInt($('.pagination') ? .dataset.totalPages || '1');

        if (currentPage >= totalPages) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isLoading && currentPage < totalPages) {
                    this.loadMoreProducts(++currentPage);
                }
            });
        }, { threshold: 0.1 });

        const sentinel = document.createElement('div');
        sentinel.className = 'scroll-sentinel';
        sentinel.style.height = '20px';
        document.querySelector('.products-grid').appendChild(sentinel);
        observer.observe(sentinel);
    }

    static async loadMoreProducts(page) {
        if (isLoading) return;

        isLoading = true;
        const loadingSpinner = document.createElement('div');
        loadingSpinner.className = 'loading-spinner';
        loadingSpinner.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        `;
        document.body.appendChild(loadingSpinner);

        try {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('ajax', '1');

            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.products.length > 0) {
                this.appendProducts(data.products);
            }
        } catch (error) {
            console.error('Error loading more products:', error);
            NotificationManager.show('Có lỗi xảy ra khi tải thêm sản phẩm', 'error');
        } finally {
            isLoading = false;
            loadingSpinner.remove();
        }
    }

    static appendProducts(products) {
        const grid = $('.products-grid');
        const productHTML = products.map(product => this.createProductHTML(product)).join('');
        grid.insertAdjacentHTML('beforeend', productHTML);

        // Re-bind events for new products
        this.bindProductEvents();

        // Animate new products
        const newProducts = grid.querySelectorAll('.product-card:not(.animated)');
        newProducts.forEach((card, index) => {
            card.classList.add('animated');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';

            setTimeout(() => {
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    static createProductHTML(product) {
            return `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}" loading="lazy">
                    ${product.featured ? '<div class="product-badge">Nổi bật</div>' : ''}
                    ${product.verified ? '<div class="product-badge verified">Đã xác thực</div>' : ''}
                    <div class="product-actions">
                        <button class="action-btn wishlist-btn" data-product-id="${product.id}">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="action-btn quick-view-btn" data-product-id="${product.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-title">
                        <a href="chi-tiet.php?id=${product.id}">${product.name}</a>
                    </h3>
                    <p class="product-game">${product.game_name}</p>
                    <p class="product-category">${product.item_type_name}</p>
                    <div class="product-price">
                        <span class="price-current">${this.formatPrice(product.price)}đ</span>
                        ${product.original_price ? `<span class="price-sale">${this.formatPrice(product.original_price)}đ</span>` : ''}
                    </div>
                    <div class="product-seller">
                        <img src="${product.seller_avatar || '../assets/images/default-avatar.png'}" 
                             alt="${product.seller_name}" class="seller-avatar">
                        <span class="seller-name">${product.seller_name}</span>
                    </div>
                    <div class="product-meta">
                        <span class="condition condition-${product.condition}">
                            ${this.capitalizeFirst(product.condition)}
                        </span>
                        <span class="rarity rarity-${product.rarity}">
                            ${this.capitalizeFirst(product.rarity)}
                        </span>
                    </div>
                    <button class="btn btn-primary add-to-cart-btn" data-product-id="${product.id}">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                    </button>
                </div>
            </div>
        `;
    }
    
    static formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
    
    static capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}

// Product Comparison
class ProductComparison {
    static init() {
        this.selectedProducts = new Set();
        this.bindEvents();
    }
    
    static bindEvents() {
        // Add comparison buttons to product cards
        $$('.product-card').forEach(card => {
            const compareBtn = document.createElement('button');
            compareBtn.className = 'action-btn compare-btn';
            compareBtn.innerHTML = '<i class="fas fa-balance-scale"></i>';
            compareBtn.title = 'So sánh sản phẩm';
            
            const actions = card.querySelector('.product-actions');
            if (actions) {
                actions.appendChild(compareBtn);
            }
            
            compareBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleProduct(card.dataset.productId);
            });
        });
    }
    
    static toggleProduct(productId) {
        if (this.selectedProducts.has(productId)) {
            this.selectedProducts.delete(productId);
        } else {
            if (this.selectedProducts.size >= 3) {
                NotificationManager.show('Chỉ có thể so sánh tối đa 3 sản phẩm', 'warning');
                return;
            }
            this.selectedProducts.add(productId);
        }
        
        this.updateComparisonUI();
    }
    
    static updateComparisonUI() {
        // Update comparison buttons
        $$('.compare-btn').forEach(btn => {
            const productId = btn.closest('.product-card').dataset.productId;
            if (this.selectedProducts.has(productId)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Show/hide comparison bar
        if (this.selectedProducts.size > 0) {
            this.showComparisonBar();
        } else {
            this.hideComparisonBar();
        }
    }
    
    static showComparisonBar() {
        let bar = $('.comparison-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'comparison-bar';
            bar.style.cssText = `
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: var(--white);
                border: 1px solid var(--border-color);
                border-radius: var(--radius-lg);
                padding: var(--spacing-md);
                box-shadow: var(--shadow-lg);
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
            `;
            document.body.appendChild(bar);
        }
        
        bar.innerHTML = `
            <span>Đã chọn ${this.selectedProducts.size} sản phẩm để so sánh</span>
            <button class="btn btn-primary" onclick="ProductComparison.compare()">
                So sánh
            </button>
            <button class="btn btn-outline" onclick="ProductComparison.clear()">
                Xóa tất cả
            </button>
        `;
    }
    
    static hideComparisonBar() {
        const bar = $('.comparison-bar');
        if (bar) {
            bar.remove();
        }
    }
    
    static compare() {
        if (this.selectedProducts.size < 2) {
            NotificationManager.show('Cần ít nhất 2 sản phẩm để so sánh', 'warning');
            return;
        }
        
        const productIds = Array.from(this.selectedProducts);
        window.open(`so-sanh.php?products=${productIds.join(',')}`, '_blank');
    }
    
    static clear() {
        this.selectedProducts.clear();
        this.updateComparisonUI();
    }
}

// Price Range Slider
class PriceRangeSlider {
    static init() {
        const priceInputs = $$('input[name="price_min"], input[name="price_max"]');
        if (priceInputs.length === 2) {
            this.createSlider(priceInputs[0], priceInputs[1]);
        }
    }
    
    static createSlider(minInput, maxInput) {
        const container = document.createElement('div');
        container.className = 'price-slider';
        container.style.cssText = `
            margin-top: var(--spacing-sm);
            padding: var(--spacing-md);
        `;
        
        const slider = document.createElement('input');
        slider.type = 'range';
        slider.min = '0';
        slider.max = '10000000';
        slider.step = '100000';
        slider.style.cssText = `
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: var(--border-color);
            outline: none;
            -webkit-appearance: none;
        `;
        
        // Style the slider
        slider.style.background = `linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) 50%, var(--border-color) 50%, var(--border-color) 100%)`;
        
        slider.addEventListener('input', (e) => {
            const value = parseInt(e.target.value);
            maxInput.value = value;
            this.updateSliderBackground(slider, value);
        });
        
        container.appendChild(slider);
        minInput.parentNode.insertBefore(container, minInput.nextSibling);
    }
    
    static updateSliderBackground(slider, value) {
        const max = parseInt(slider.max);
        const percentage = (value / max) * 100;
        slider.style.background = `linear-gradient(to right, var(--primary-color) 0%, var(--primary-color) ${percentage}%, var(--border-color) ${percentage}%, var(--border-color) 100%)`;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    FilterManager.init();
    ProductGridManager.init();
    ProductComparison.init();
    PriceRangeSlider.init();
    
    // Initialize cart count
    CartManager.updateCartCount();
    
    // Add smooth scrolling to pagination
    $$('.pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            
            // Show loading state
            const grid = $('.products-grid');
            grid.classList.add('loading');
            
            // Navigate to new page
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        });
    });
});

// Export for global access
window.FilterManager = FilterManager;
window.ProductGridManager = ProductGridManager;
window.ProductComparison = ProductComparison;
// Home Page Specific JavaScript

// Hero Section Animations
class HeroAnimations {
    static init() {
        this.animateStats();
        this.animateCards();
    }

    static animateStats() {
        const stats = $$('.stat-number');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateNumber(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        stats.forEach(stat => observer.observe(stat));
    }

    static animateNumber(element) {
        const target = parseInt(element.textContent.replace(/[^\d]/g, ''));
        const duration = 2000;
        const start = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(progress * target);

            element.textContent = this.formatNumber(current) + element.textContent.replace(/[\d,]/g, '').replace(/[^\d]/g, '');

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    static formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    static animateCards() {
        const cards = $$('.product-card, .category-card, .feature-item');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }
}

// Product Filtering
class ProductFilter {
    static init() {
        this.bindEvents();
        this.loadProducts();
    }

    static bindEvents() {
        // Category filters
        $$('.category-filter').forEach(filter => {
            filter.addEventListener('click', (e) => {
                e.preventDefault();
                this.filterByCategory(filter.dataset.category);
            });
        });

        // Price range filter
        const priceRange = $('.price-range');
        if (priceRange) {
            priceRange.addEventListener('input', () => {
                this.filterByPrice(priceRange.value);
            });
        }

        // Search filter
        const searchInput = $('.product-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.filterBySearch(e.target.value);
                }, 300);
            });
        }
    }

    static async loadProducts() {
        try {
            const response = await fetch('api/products.php?featured=1&limit=8');
            const data = await response.json();

            if (data.success) {
                this.displayProducts(data.products);
            }
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    static displayProducts(products) {
            const container = $('.products-grid');
            if (!container) return;

            container.innerHTML = products.map(product => `
            <div class="product-card" data-category="${product.category_id}" data-price="${product.price}">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}" loading="lazy">
                    <div class="product-badge">Nổi bật</div>
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
                        <a href="san-pham/chi-tiet.php?id=${product.id}">${product.name}</a>
                    </h3>
                    <p class="product-category">${product.category_name}</p>
                    <div class="product-price">
                        <span class="price-current">${this.formatPrice(product.price)}đ</span>
                        ${product.sale_price ? `<span class="price-sale">${this.formatPrice(product.sale_price)}đ</span>` : ''}
                    </div>
                    <button class="btn btn-primary add-to-cart-btn" data-product-id="${product.id}">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                    </button>
                </div>
            </div>
        `).join('');
        
        // Re-bind event listeners
        this.bindProductEvents();
    }
    
    static bindProductEvents() {
        // Add to cart
        $$('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                CartManager.addToCart(btn.dataset.productId);
            });
        });
        
        // Wishlist
        $$('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                WishlistManager.toggleWishlist(btn.dataset.productId);
            });
        });
        
        // Quick view
        $$('.quick-view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                QuickViewManager.show(btn.dataset.productId);
            });
        });
    }
    
    static filterByCategory(categoryId) {
        const products = $$('.product-card');
        products.forEach(product => {
            if (categoryId === 'all' || product.dataset.category === categoryId) {
                product.style.display = 'block';
                product.style.animation = 'fadeInUp 0.5s ease';
            } else {
                product.style.display = 'none';
            }
        });
        
        // Update active filter
        $$('.category-filter').forEach(filter => {
            filter.classList.remove('active');
        });
        $(`.category-filter[data-category="${categoryId}"]`).classList.add('active');
    }
    
    static filterByPrice(maxPrice) {
        const products = $$('.product-card');
        products.forEach(product => {
            const price = parseFloat(product.dataset.price);
            if (price <= maxPrice) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    }
    
    static filterBySearch(query) {
        const products = $$('.product-card');
        const searchTerm = query.toLowerCase();
        
        products.forEach(product => {
            const title = product.querySelector('.product-title').textContent.toLowerCase();
            const category = product.querySelector('.product-category').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || category.includes(searchTerm)) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    }
    
    static formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }
}

// Newsletter Subscription
class NewsletterManager {
    static init() {
        const form = $('.newsletter-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.subscribe(form);
            });
        }
    }
    
    static async subscribe(form) {
        const email = form.querySelector('input[type="email"]').value;
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.textContent;
        
        // Show loading state
        button.textContent = 'Đang đăng ký...';
        button.disabled = true;
        
        try {
            const response = await fetch('api/newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });
            
            const data = await response.json();
            
            if (data.success) {
                NotificationManager.show('Đăng ký thành công!', 'success');
                form.reset();
            } else {
                NotificationManager.show(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Newsletter subscription error:', error);
            NotificationManager.show('Có lỗi xảy ra khi đăng ký', 'error');
        } finally {
            button.textContent = originalText;
            button.disabled = false;
        }
    }
}

// Scroll to Top
class ScrollToTop {
    static init() {
        this.createButton();
        this.bindEvents();
    }
    
    static createButton() {
        const button = document.createElement('button');
        button.className = 'scroll-to-top';
        button.innerHTML = '<i class="fas fa-chevron-up"></i>';
        button.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;
        
        document.body.appendChild(button);
    }
    
    static bindEvents() {
        const button = $('.scroll-to-top');
        
        // Show/hide button based on scroll position
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                button.style.opacity = '1';
                button.style.visibility = 'visible';
            } else {
                button.style.opacity = '0';
                button.style.visibility = 'hidden';
            }
        });
        
        // Scroll to top when clicked
        button.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Lazy Loading for Images
class LazyLoader {
    static init() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            $$('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    HeroAnimations.init();
    ProductFilter.init();
    NewsletterManager.init();
    ScrollToTop.init();
    LazyLoader.init();
    
    // Add smooth scrolling to all anchor links
    $$('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to buttons
    $$('.btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.type === 'submit' && this.form) {
                this.classList.add('loading');
                setTimeout(() => {
                    this.classList.remove('loading');
                }, 2000);
            }
        });
    });
});

// Export for global access
window.HeroAnimations = HeroAnimations;
window.ProductFilter = ProductFilter;
window.NewsletterManager = NewsletterManager;
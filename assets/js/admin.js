// Admin Panel JavaScript

// Admin Dashboard Manager
class AdminDashboard {
    static init() {
        this.bindEvents();
        this.initCharts();
        this.loadRecentActivity();
    }

    static bindEvents() {
        // Sidebar toggle for mobile
        const sidebarToggle = $('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                const sidebar = $('.admin-sidebar');
                const toggle = $('.sidebar-toggle');

                if (sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Auto-refresh stats every 30 seconds
        setInterval(() => {
            this.refreshStats();
        }, 30000);
    }

    static toggleSidebar() {
        const sidebar = $('.admin-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    }

    static async refreshStats() {
        try {
            const response = await fetch('api/dashboard.php?action=stats');
            const data = await response.json();

            if (data.success) {
                this.updateStatsDisplay(data.stats);
            }
        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    static updateStatsDisplay(stats) {
        // Update stat cards
        const statCards = $$('.stat-card');
        statCards.forEach(card => {
            const icon = card.querySelector('.stat-icon i');
            if (icon) {
                const iconClass = icon.className;
                let value = 0;

                if (iconClass.includes('fa-users')) {
                    value = stats.total_users || 0;
                } else if (iconClass.includes('fa-box')) {
                    value = stats.total_products || 0;
                } else if (iconClass.includes('fa-shopping-cart')) {
                    value = stats.total_orders || 0;
                } else if (iconClass.includes('fa-dollar-sign')) {
                    value = stats.total_revenue || 0;
                }

                const valueElement = card.querySelector('h3');
                if (valueElement) {
                    valueElement.textContent = this.formatNumber(value);
                }
            }
        });
    }

    static formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    static initCharts() {
        // Initialize any charts if needed
        // This would integrate with Chart.js or similar library
    }

    static async loadRecentActivity() {
        try {
            const response = await fetch('api/dashboard.php?action=activity');
            const data = await response.json();

            if (data.success) {
                this.updateActivityDisplay(data.activity);
            }
        } catch (error) {
            console.error('Error loading activity:', error);
        }
    }

    static updateActivityDisplay(activity) {
        // Update recent orders, products, users, etc.
        console.log('Activity data:', activity);
    }
}

// Data Table Manager
class DataTableManager {
    static init() {
        this.bindEvents();
    }

    static bindEvents() {
        // Search functionality
        const searchInputs = $$('.data-table-search');
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.filterTable(e.target);
            });
        });

        // Sort functionality
        const sortButtons = $$('.data-table-sort');
        sortButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.sortTable(e.target);
            });
        });

        // Pagination
        const paginationLinks = $$('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage(link.href);
            });
        });
    }

    static filterTable(searchInput) {
        const table = searchInput.closest('.data-table').querySelector('tbody');
        const searchTerm = searchInput.value.toLowerCase();
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    static sortTable(sortButton) {
        const table = sortButton.closest('.data-table');
        const tbody = table.querySelector('tbody');
        const column = sortButton.dataset.column;
        const currentSort = sortButton.dataset.sort || 'asc';
        const newSort = currentSort === 'asc' ? 'desc' : 'asc';

        // Update sort indicators
        $$('.data-table-sort').forEach(btn => {
            btn.classList.remove('sort-asc', 'sort-desc');
            btn.dataset.sort = '';
        });

        sortButton.classList.add(`sort-${newSort}`);
        sortButton.dataset.sort = newSort;

        // Sort rows
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const aValue = a.querySelector(`[data-column="${column}"]`) ? .textContent || '';
            const bValue = b.querySelector(`[data-column="${column}"]`) ? .textContent || '';

            if (newSort === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });

        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }

    static async loadPage(url) {
        try {
            const response = await fetch(url);
            const data = await response.text();

            // Update table content
            const tableContainer = $('.data-table-container');
            if (tableContainer) {
                tableContainer.innerHTML = data;
                this.bindEvents(); // Re-bind events for new content
            }
        } catch (error) {
            console.error('Error loading page:', error);
            NotificationManager.show('Có lỗi xảy ra khi tải dữ liệu', 'error');
        }
    }
}

// Form Manager
class AdminFormManager {
    static init() {
        this.bindEvents();
    }

    static bindEvents() {
        // Form validation
        const forms = $$('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });

        // Auto-save functionality
        const autoSaveForms = $$('form[data-autosave]');
        autoSaveForms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    this.autoSave(form);
                });
            });
        });

        // Image upload preview
        const imageInputs = $$('input[type="file"][accept*="image"]');
        imageInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.previewImage(e.target);
            });
        });
    }

    static validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'Trường này là bắt buộc');
                isValid = false;
            } else {
                this.clearFieldError(input);
            }
        });

        return isValid;
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

    static async autoSave(form) {
        const formData = new FormData(form);
        const url = form.dataset.autosave;

        try {
            await fetch(url, {
                method: 'POST',
                body: formData
            });

            NotificationManager.show('Đã tự động lưu', 'success');
        } catch (error) {
            console.error('Auto-save error:', error);
        }
    }

    static previewImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = input.parentNode.querySelector('.image-preview');
                if (preview) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
                }
            };
            reader.readAsDataURL(file);
        }
    }
}

// Modal Manager
class AdminModalManager {
    static init() {
        this.bindEvents();
    }

    static bindEvents() {
        // Open modal buttons
        $$('.modal-trigger').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = button.dataset.modal;
                this.openModal(modalId);
            });
        });

        // Close modal buttons
        $$('.modal-close').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeModal(button.closest('.modal'));
            });
        });

        // Close modal on backdrop click
        $$('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = $('.modal.open');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
    }

    static openModal(modalId) {
        const modal = $(`#${modalId}`);
        if (modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    static closeModal(modal) {
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    AdminDashboard.init();
    DataTableManager.init();
    AdminFormManager.init();
    AdminModalManager.init();
});

// Export for global access
window.AdminDashboard = AdminDashboard;
window.DataTableManager = DataTableManager;
window.AdminFormManager = AdminFormManager;
window.AdminModalManager = AdminModalManager;
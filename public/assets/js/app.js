/**
 * Eurobillr - Main Application JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize confirm dialogs
    initConfirmDialogs();
    
    // Initialize auto-save forms
    initAutoSave();
    
    // Initialize keyboard shortcuts
    initKeyboardShortcuts();
    
    // Initialize search
    initSearch();
    
    // Initialize number formatting
    initNumberFormatting();
});

/**
 * Tooltips initialization
 */
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', showTooltip);
        el.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'fixed z-50 px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-lg';
    tooltip.textContent = e.target.dataset.tooltip;
    tooltip.style.left = e.pageX + 10 + 'px';
    tooltip.style.top = e.pageY + 10 + 'px';
    document.body.appendChild(tooltip);
    e.target._tooltip = tooltip;
}

function hideTooltip(e) {
    if (e.target._tooltip) {
        e.target._tooltip.remove();
        e.target._tooltip = null;
    }
}

/**
 * Confirm dialogs for destructive actions
 */
function initConfirmDialogs() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            const message = this.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

/**
 * Auto-save forms with debounce
 */
function initAutoSave() {
    document.querySelectorAll('[data-auto-save]').forEach(form => {
        let timeout;
        form.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    saveForm(this);
                }, 1000);
            }
        });
    });
}

async function saveForm(form) {
    const formData = new FormData(form);
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const result = await response.json();
        if (result.success) {
            showNotification('Saved', 'success');
        }
    } catch (error) {
        console.error('Auto-save failed:', error);
    }
}

/**
 * Keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N: New invoice
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = '/invoices/create';
        }
        
        // Ctrl/Cmd + K: Quick search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.querySelector('input[type="search"]')?.focus();
        }
        
        // Escape: Close modals
        if (e.key === 'Escape') {
            document.querySelectorAll('[role="dialog"]').forEach(modal => {
                modal.close();
            });
        }
    });
}

/**
 * Search functionality with debouncing
 */
function initSearch() {
    const searchInput = document.querySelector('[data-search]');
    if (!searchInput) return;
    
    let timeout;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            performSearch(this.value);
        }, 300);
    });
}

async function performSearch(query) {
    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        renderSearchResults(results);
    } catch (error) {
        console.error('Search failed:', error);
    }
}

function renderSearchResults(results) {
    const container = document.querySelector('[data-search-results]');
    if (!container) return;
    
    if (results.length === 0) {
        container.innerHTML = '<div class="p-4 text-sm text-gray-500">No results found</div>';
        return;
    }
    
    container.innerHTML = results.map(item => `
        <a href="${item.url}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
            <div class="font-medium text-gray-900">${escapeHtml(item.title)}</div>
            <div class="text-sm text-gray-500">${escapeHtml(item.subtitle)}</div>
        </a>
    `).join('');
}

/**
 * Number formatting for currency inputs
 */
function initNumberFormatting() {
    document.querySelectorAll('[data-currency-input]').forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/[^0-9.-]/g, '')) || 0;
            this.value = formatCurrency(value, this.dataset.currency || 'EUR');
        });
    });
}

function formatCurrency(amount, currency) {
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Notification system
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    requestAnimationFrame(() => {
        notification.classList.remove('translate-x-full');
    });
    
    // Remove after 4 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

/**
 * Helper: Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Modal helper functions
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.showModal();
        modal.querySelector('[autofocus]')?.focus();
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.close();
    }
}

/**
 * Form validation helper
 */
function validateForm(form) {
    const inputs = form.querySelectorAll('[required]');
    let valid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            valid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    return valid;
}

/**
 * AJAX helper
 */
async function ajax(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const response = await fetch(url, { ...defaultOptions, ...options });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
}

// Export for use in other scripts
window.Eurobillr = {
    showNotification,
    openModal,
    closeModal,
    validateForm,
    ajax,
    formatCurrency
};

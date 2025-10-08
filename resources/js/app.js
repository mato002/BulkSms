// Bulk SMS Laravel JavaScript

// CSRF Token setup for AJAX requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

// API Helper Functions
const API = {
    baseURL: '/api',
    
    // Send SMS
    sendSMS: async (data) => {
        try {
            const response = await fetch(`${API.baseURL}/sms/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('SMS sending failed:', error);
            throw error;
        }
    },
    
    // Get Balance
    getBalance: async () => {
        try {
            const response = await fetch(`${API.baseURL}/balance`);
            return await response.json();
        } catch (error) {
            console.error('Failed to get balance:', error);
            throw error;
        }
    },
    
    // Get Contacts
    getContacts: async () => {
        try {
            const response = await fetch(`${API.baseURL}/contacts`);
            return await response.json();
        } catch (error) {
            console.error('Failed to get contacts:', error);
            throw error;
        }
    },
    
    // Get Campaigns
    getCampaigns: async () => {
        try {
            const response = await fetch(`${API.baseURL}/campaigns`);
            return await response.json();
        } catch (error) {
            console.error('Failed to get campaigns:', error);
            throw error;
        }
    }
};

// Utility Functions
const Utils = {
    // Show loading spinner
    showLoading: (element) => {
        if (element) {
            element.innerHTML = '<div class="loading"></div>';
        }
    },
    
    // Show success message
    showSuccess: (message) => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.insertBefore(alert, document.body.firstChild);
        setTimeout(() => alert.remove(), 5000);
    },
    
    // Show error message
    showError: (message) => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.insertBefore(alert, document.body.firstChild);
        setTimeout(() => alert.remove(), 5000);
    },
    
    // Format phone number
    formatPhone: (phone) => {
        return phone.replace(/\D/g, '');
    },
    
    // Validate phone number
    validatePhone: (phone) => {
        const cleaned = Utils.formatPhone(phone);
        return cleaned.length >= 10;
    },
    
    // Format currency
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('en-KE', {
            style: 'currency',
            currency: 'KES'
        }).format(amount);
    }
};

// SMS Form Handler
const SMSForm = {
    init: () => {
        const form = document.getElementById('sms-form');
        if (form) {
            form.addEventListener('submit', SMSForm.handleSubmit);
        }
    },
    
    handleSubmit: async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = {
            recipient: formData.get('recipient'),
            message: formData.get('message'),
            sender_id: formData.get('sender_id') || 'BULKSMS'
        };
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            Utils.showLoading(submitBtn);
            
            const result = await API.sendSMS(data);
            
            if (result.success) {
                Utils.showSuccess('SMS sent successfully!');
                e.target.reset();
            } else {
                Utils.showError(result.message || 'Failed to send SMS');
            }
        } catch (error) {
            Utils.showError('Network error. Please try again.');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
};

// Balance Display
const BalanceDisplay = {
    init: () => {
        const balanceElement = document.getElementById('balance-display');
        if (balanceElement) {
            BalanceDisplay.updateBalance();
        }
    },
    
    updateBalance: async () => {
        try {
            const balance = await API.getBalance();
            const balanceElement = document.getElementById('balance-display');
            if (balanceElement) {
                balanceElement.textContent = Utils.formatCurrency(balance.balance || 0);
            }
        } catch (error) {
            console.error('Failed to update balance:', error);
        }
    }
};

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    SMSForm.init();
    BalanceDisplay.init();
    
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
});

// Export for use in other scripts
window.BulkSMS = {
    API,
    Utils,
    SMSForm,
    BalanceDisplay
};




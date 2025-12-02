/**
 * Mastercard Gateway Checkout Handler
 * 
 * Manages checkout flow, API calls, and Checkout.js integration
 */

class CheckoutHandler {
    constructor(config) {
        this.config = {
            apiBaseUrl: config.apiBaseUrl || '/api',
            gatewayUrl: config.gatewayUrl,
            ...config
        };
        this.sessionId = null;
        this.successIndicator = null;
        this.orderId = null;
    }

    /**
     * Initialize checkout session
     */
    async initiateCheckout(orderData) {
        try {
            const response = await fetch(`${this.config.apiBaseUrl}/initiate-checkout.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to initiate checkout');
            }

            this.sessionId = data.sessionId;
            this.successIndicator = data.successIndicator;
            this.orderId = data.orderId;

            // Store success indicator in session storage for receipt page
            sessionStorage.setItem('successIndicator', this.successIndicator);
            sessionStorage.setItem('orderId', this.orderId);

            return data;

        } catch (error) {
            console.error('Checkout initiation error:', error);
            throw error;
        }
    }

    /**
     * Configure Checkout.js
     */
    configureCheckout() {
        if (!this.sessionId) {
            throw new Error('No session ID available. Call initiateCheckout() first.');
        }

        if (typeof Checkout === 'undefined') {
            throw new Error('Checkout.js library not loaded');
        }

        Checkout.configure({
            session: {
                id: this.sessionId
            }
        });
    }

    /**
     * Show embedded payment page
     */
    showEmbeddedPage(targetSelector) {
        this.configureCheckout();
        Checkout.showEmbeddedPage(targetSelector);
    }

    /**
     * Show payment page (redirect)
     */
    showPaymentPage() {
        this.configureCheckout();
        Checkout.showPaymentPage();
    }

    /**
     * Get order result
     */
    async getOrderResult(orderId) {
        try {
            const response = await fetch(
                `${this.config.apiBaseUrl}/get-order-result.php?orderId=${encodeURIComponent(orderId)}`
            );

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to get order result');
            }

            return data;

        } catch (error) {
            console.error('Get order result error:', error);
            throw error;
        }
    }

    /**
     * Perform subsequent operation (CAPTURE, REFUND, VOID)
     */
    async performOperation(operation, orderId, transactionId, additionalData = {}) {
        try {
            const requestData = {
                operation,
                orderId,
                transactionId,
                ...additionalData
            };

            const response = await fetch(`${this.config.apiBaseUrl}/subsequent-operations.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Operation failed');
            }

            return data;

        } catch (error) {
            console.error('Operation error:', error);
            throw error;
        }
    }

    /**
     * Verify payment result
     */
    verifyPaymentResult(resultIndicator) {
        const storedSuccessIndicator = sessionStorage.getItem('successIndicator');
        return resultIndicator === storedSuccessIndicator;
    }
}

/**
 * Callback Functions for Checkout.js
 */

function errorCallback(error) {
    console.error('Payment error:', error);
    
    // Show error message
    showAlert('error', 'Payment Error: ' + (error.message || 'An error occurred during payment'));
    
    // Hide loading overlay if visible
    hideLoading();
}

function cancelCallback() {
    console.log('Payment cancelled by user');
    
    const confirmed = confirm('Are you sure you want to cancel this payment?');
    
    if (confirmed) {
        showAlert('warning', 'Payment cancelled');
        // Optionally redirect to home page or clear form
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }
}

function completeCallback(resultIndicator, sessionVersion) {
    console.log('Payment complete:', { resultIndicator, sessionVersion });
    
    // The gateway will redirect to returnUrl with resultIndicator
    // So this callback might not always be called
    showAlert('success', 'Payment completed! Redirecting...');
}

function timeoutCallback() {
    console.log('Payment session timeout');
    
    showAlert('error', 'Payment session has timed out. Please try again.');
    
    setTimeout(() => {
        window.location.reload();
    }, 3000);
}

/**
 * Utility Functions
 */

function showLoading(message = 'Processing...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = `
        <div class="loading-content">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <span>${message}</span>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alert, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}

function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

function formatDateTime(timestamp) {
    if (!timestamp) return 'N/A';
    
    const date = new Date(timestamp);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

/**
 * Form Validation
 */

function validateOrderForm(formData) {
    const errors = [];
    
    if (!formData.amount || parseFloat(formData.amount) <= 0) {
        errors.push('Please enter a valid amount');
    }
    
    if (!formData.currency) {
        errors.push('Please select a currency');
    }
    
    if (!formData.description) {
        errors.push('Please enter a description');
    }
    
    return errors;
}

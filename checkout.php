<?php
/**
 * Mastercard Gateway Checkout Page
 * 
 * Demonstrates both Embedded Page and Payment Page implementations
 */

require_once __DIR__ . '/config.php';
$config = require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Mastercard Gateway</title>
    <link rel="stylesheet" href="assets/css/checkout-styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <div class="logo-circle logo-mc-red">M</div>
                <div class="logo-circle logo-mc-orange">C</div>
            </div>
            <h1>Secure Checkout</h1>
            <p>Powered by Mastercard Payment Gateway</p>
        </div>

        <form id="orderForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="amount">Amount (LKR) *</label>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        step="0.01" 
                        min="0.01" 
                        placeholder="100.00" 
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="operation">Transaction Type</label>
                    <select id="operation" name="operation">
                        <option value="PURCHASE" selected>Purchase (Direct payment)</option>
                        <option value="VERIFY">Verify (Card verification only)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <input 
                    type="text" 
                    id="description" 
                    name="description" 
                    placeholder="Online Purchase" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="customerEmail">Customer Email (Optional)</label>
                <input 
                    type="email" 
                    id="customerEmail" 
                    name="customerEmail" 
                    placeholder="customer@example.com"
                >
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-primary" id="btnEmbeddedPage">
                    Pay with Embedded Page
                </button>
                <button type="button" class="btn btn-secondary" id="btnPaymentPage">
                    Pay with Payment Page
                </button>
            </div>
        </form>

        <!-- Embedded Page Container -->
        <div id="embed-target" style="display: none;"></div>
    </div>

    <!-- Include Checkout.js from Gateway -->
    <script 
        src="<?php echo $config['gateway_url']; ?>/static/checkout/checkout.min.js"
        data-error="errorCallback"
        data-cancel="cancelCallback"
        data-complete="completeCallback"  
        data-timeout="timeoutCallback"
        data-beforeRedirect="Checkout.saveFormFields"
        data-afterRedirect="Checkout.restoreFormFields">
    </script>

    <!-- Include Checkout Handler -->
    <script src="assets/js/checkout-handler.js"></script>

    <script>
        // Initialize checkout handler
        const checkoutHandler = new CheckoutHandler({
            apiBaseUrl: 'api',
            gatewayUrl: '<?php echo $config['gateway_url']; ?>'
        });

        // Generate unique order ID
        function generateOrderId() {
            return 'ORDER-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();
        }

        // Handle Embedded Page button click
        document.getElementById('btnEmbeddedPage').addEventListener('click', async function() {
            try {
                // Validate form
                const form = document.getElementById('orderForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Get form data
                const orderData = {
                    orderId: generateOrderId(),
                    amount: document.getElementById('amount').value,
                    currency: 'LKR', // Only LKR is supported
                    description: document.getElementById('description').value,
                    operation: document.getElementById('operation').value,
                    returnUrl: window.location.origin + window.location.pathname.replace('checkout.php', 'receipt.php')
                };

                // Add customer email if provided
                const email = document.getElementById('customerEmail').value;
                if (email) {
                    orderData.customer = {
                        email: email
                    };
                }

                // Show loading
                showLoading('Initializing payment...');

                // Disable buttons
                document.getElementById('btnEmbeddedPage').disabled = true;
                document.getElementById('btnPaymentPage').disabled = true;

                // Initiate checkout
                await checkoutHandler.initiateCheckout(orderData);

                // Hide loading
                hideLoading();

                // Show embedded container
                document.getElementById('embed-target').style.display = 'block';

                // Show embedded page
                checkoutHandler.showEmbeddedPage('#embed-target');

                // Scroll to embedded page
                document.getElementById('embed-target').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });

            } catch (error) {
                hideLoading();
                showAlert('error', error.message);
                
                // Re-enable buttons
                document.getElementById('btnEmbeddedPage').disabled = false;
                document.getElementById('btnPaymentPage').disabled = false;
            }
        });

        // Handle Payment Page button click
        document.getElementById('btnPaymentPage').addEventListener('click', async function() {
            try {
                // Validate form
                const form = document.getElementById('orderForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Get form data
                const orderData = {
                    orderId: generateOrderId(),
                    amount: document.getElementById('amount').value,
                    currency: 'LKR', // Only LKR is supported
                    description: document.getElementById('description').value,
                    operation: document.getElementById('operation').value,
                    returnUrl: window.location.origin + window.location.pathname.replace('checkout.php', 'receipt.php')
                };

                // Add customer email if provided
                const email = document.getElementById('customerEmail').value;
                if (email) {
                    orderData.customer = {
                        email: email
                    };
                }

                // Show loading
                showLoading('Redirecting to payment page...');

                // Disable buttons
                document.getElementById('btnEmbeddedPage').disabled = true;
                document.getElementById('btnPaymentPage').disabled = true;

                // Initiate checkout
                await checkoutHandler.initiateCheckout(orderData);

                // Show payment page (will redirect)
                checkoutHandler.showPaymentPage();

            } catch (error) {
                hideLoading();
                showAlert('error', error.message);
                
                // Re-enable buttons
                document.getElementById('btnEmbeddedPage').disabled = false;
                document.getElementById('btnPaymentPage').disabled = false;
            }
        });

        // Auto-fill demo data for testing
        if (window.location.search.includes('demo=1')) {
            document.getElementById('amount').value = '1000.00';
            document.getElementById('description').value = 'Demo Purchase';
            document.getElementById('customerEmail').value = 'demo@example.com';
        }
    </script>
</body>
</html>

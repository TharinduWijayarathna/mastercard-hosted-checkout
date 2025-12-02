<?php
/**
 * Payment Receipt Page
 * 
 * Displays payment result after completion
 */

require_once __DIR__ . '/config.php';
$config = require __DIR__ . '/config.php';

// Get result indicator from query string
$resultIndicator = $_GET['resultIndicator'] ?? null;
$orderId = $_GET['orderId'] ?? null;

// If no parameters, try to get from session storage via JavaScript
$showResult = $resultIndicator && $orderId;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Mastercard Gateway</title>
    <link rel="stylesheet" href="assets/css/checkout-styles.css">
</head>
<body>
    <div class="container">
        <div class="receipt" id="receiptContainer">
            <div class="receipt-header">
                <div class="logo">
                    <div class="logo-circle logo-mc-red">M</div>
                    <div class="logo-circle logo-mc-orange">C</div>
                </div>
                <h1>Payment Receipt</h1>
                <div id="statusContainer"></div>
            </div>

            <div id="receiptContent">
                <!-- Content will be loaded via JavaScript -->
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner"></div>
                    <p>Loading receipt...</p>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button class="btn btn-secondary" onclick="window.print()">Print Receipt</button>
                <button class="btn btn-primary" onclick="window.location.href='checkout.php'">New Payment</button>
            </div>
        </div>
    </div>

    <script src="assets/js/checkout-handler.js"></script>
    <script>
        // Initialize checkout handler
        const checkoutHandler = new CheckoutHandler({
            apiBaseUrl: 'api',
            gatewayUrl: '<?php echo $config['gateway_url']; ?>'
        });

        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        let resultIndicator = urlParams.get('resultIndicator');
        let orderId = urlParams.get('orderId');

        // If not in URL, try session storage
        if (!orderId) {
            orderId = sessionStorage.getItem('orderId');
        }
        if (!resultIndicator) {
            resultIndicator = urlParams.get('resultIndicator');
        }

        async function loadReceipt() {
            try {
                if (!orderId) {
                    showError('No order ID found. Please complete a payment first.');
                    return;
                }

                // Verify payment result if we have result indicator
                let paymentSuccess = true;
                if (resultIndicator) {
                    paymentSuccess = checkoutHandler.verifyPaymentResult(resultIndicator);
                }

                // Show status
                const statusContainer = document.getElementById('statusContainer');
                if (paymentSuccess) {
                    statusContainer.innerHTML = '<span class="receipt-status success">✓ Payment Successful</span>';
                } else {
                    statusContainer.innerHTML = '<span class="receipt-status failed">✗ Payment Failed</span>';
                }

                // Get order details
                const orderData = await checkoutHandler.getOrderResult(orderId);

                // Display receipt
                displayReceipt(orderData, paymentSuccess);

            } catch (error) {
                console.error('Error loading receipt:', error);
                showError(error.message);
            }
        }

        function displayReceipt(orderData, success) {
            const order = orderData.order;
            const transactions = orderData.transactions || [];
            const paymentMethod = orderData.paymentMethod;

            let html = '<div class="receipt-details">';

            // Order Information
            html += '<h3 style="margin-bottom: 15px;">Order Information</h3>';
            html += `
                <div class="receipt-row">
                    <span class="receipt-label">Order ID</span>
                    <span class="receipt-value">${order.id || 'N/A'}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Description</span>
                    <span class="receipt-value">${order.description || 'N/A'}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Date & Time</span>
                    <span class="receipt-value">${formatDateTime(order.creationTime)}</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Status</span>
                    <span class="receipt-value">${order.status || 'N/A'}</span>
                </div>
            `;

            html += '</div>';

            // Amount Details
            html += '<div class="receipt-total">';
            html += `
                <div class="receipt-row">
                    <span class="receipt-label">Total Amount</span>
                    <span class="receipt-value">${formatCurrency(order.amount, order.currency)}</span>
                </div>
            `;

            if (order.totalAuthorizedAmount) {
                html += `
                    <div class="receipt-row">
                        <span class="receipt-label">Authorized</span>
                        <span class="receipt-value">${formatCurrency(order.totalAuthorizedAmount, order.currency)}</span>
                    </div>
                `;
            }

            if (order.totalCapturedAmount) {
                html += `
                    <div class="receipt-row">
                        <span class="receipt-label">Captured</span>
                        <span class="receipt-value">${formatCurrency(order.totalCapturedAmount, order.currency)}</span>
                    </div>
                `;
            }

            if (order.totalRefundedAmount) {
                html += `
                    <div class="receipt-row">
                        <span class="receipt-label">Refunded</span>
                        <span class="receipt-value">${formatCurrency(order.totalRefundedAmount, order.currency)}</span>
                    </div>
                `;
            }

            html += '</div>';

            // Payment Method
            if (paymentMethod && paymentMethod.card) {
                html += '<div class="card-info">';
                html += '<h3 style="margin-bottom: 15px;">Payment Method</h3>';
                html += `
                    <div class="receipt-row">
                        <span class="receipt-label">Card Number</span>
                        <span class="receipt-value">${paymentMethod.card.number || 'N/A'}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-label">Card Type</span>
                        <span class="receipt-value">${paymentMethod.card.scheme || paymentMethod.card.brand || 'N/A'}</span>
                    </div>
                `;
                html += '</div>';
            }

            // Transactions
            if (transactions.length > 0) {
                html += '<div class="transaction-list">';
                html += '<h3 style="margin-bottom: 15px;">Transaction History</h3>';

                transactions.forEach(txn => {
                    html += `
                        <div class="transaction-item">
                            <div class="transaction-header">
                                <span class="transaction-type">${txn.type || 'N/A'}</span>
                                <span class="transaction-time">${formatDateTime(txn.timestamp)}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Transaction ID</span>
                                <span class="receipt-value">${txn.id || 'N/A'}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label">Amount</span>
                                <span class="receipt-value">${formatCurrency(txn.amount, txn.currency)}</span>
                            </div>
                    `;

                    if (txn.authorizationCode) {
                        html += `
                            <div class="receipt-row">
                                <span class="receipt-label">Auth Code</span>
                                <span class="receipt-value">${txn.authorizationCode}</span>
                            </div>
                        `;
                    }

                    if (txn.receipt) {
                        html += `
                            <div class="receipt-row">
                                <span class="receipt-label">Receipt Number</span>
                                <span class="receipt-value">${txn.receipt}</span>
                            </div>
                        `;
                    }

                    html += '</div>';
                });

                html += '</div>';
            }

            document.getElementById('receiptContent').innerHTML = html;
        }

        function showError(message) {
            document.getElementById('statusContainer').innerHTML = 
                '<span class="receipt-status failed">✗ Error</span>';
            
            document.getElementById('receiptContent').innerHTML = `
                <div class="alert alert-error">
                    <p><strong>Error:</strong> ${message}</p>
                    <p style="margin-top: 10px;">
                        <a href="checkout.php" class="btn btn-primary">Return to Checkout</a>
                    </p>
                </div>
            `;
        }

        // Load receipt on page load
        window.addEventListener('load', loadReceipt);
    </script>
</body>
</html>

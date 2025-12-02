<?php
/**
 * Modal/Lightbox Example for Hosted Checkout
 * 
 * Demonstrates using Bootstrap modal for Lightbox-style payment
 */

require_once __DIR__ . '/../config.php';
$config = require __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Checkout Example - Mastercard Gateway</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/checkout-styles.css">
    
    <style>
        .modal-dialog {
            max-width: 600px;
        }
        .modal-body {
            min-height: 400px;
        }
        #hco-embedded {
            min-height: 350px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Modal Checkout Example</h4>
                    </div>
                    <div class="card-body">
                        <p>Click the button below to open the payment form in a modal dialog.</p>
                        
                        <form id="orderForm">
                            <div class="form-group">
                                <label for="amount">Amount (LKR)</label>
                                <input type="number" class="form-control" id="amount" value="1000.00" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" class="form-control" id="description" value="Modal Payment" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="operation">Transaction Type</label>
                                <select class="form-control" id="operation">
                                    <option value="PURCHASE" selected>Purchase</option>
                                    <option value="VERIFY">Verify</option>
                                </select>
                            </div>
                            
                            <button type="button" class="btn btn-primary btn-lg btn-block" id="btnOpenModal">
                                Open Payment Modal
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Secure Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="hco-embedded"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    
    <!-- Checkout.js -->
    <script src="<?php echo $config['gateway_url']; ?>/static/checkout/checkout.min.js"></script>
    
    <!-- Checkout Handler -->
    <script src="../assets/js/checkout-handler.js"></script>

    <script>
        // Initialize checkout handler
        const checkoutHandler = new CheckoutHandler({
            apiBaseUrl: '../api',
            gatewayUrl: '<?php echo $config['gateway_url']; ?>'
        });

        // Handle modal open
        $('#btnOpenModal').on('click', async function() {
            try {
                // Validate form
                const form = document.getElementById('orderForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Get form data
                const orderData = {
                    orderId: 'ORDER-' + Date.now(),
                    amount: $('#amount').val(),
                    currency: 'LKR', // Only LKR is supported
                    description: $('#description').val(),
                    operation: $('#operation').val() || 'PURCHASE',
                    returnUrl: window.location.origin + window.location.pathname.replace('examples/modal-example.php', 'receipt.php')
                };

                // Show loading
                showLoading('Initializing payment...');

                // Initiate checkout
                await checkoutHandler.initiateCheckout(orderData);

                hideLoading();

                // Show modal
                $('#paymentModal').modal('show');

            } catch (error) {
                hideLoading();
                alert('Error: ' + error.message);
            }
        });

        // When modal is shown, initialize embedded page
        $('#paymentModal').on('shown.bs.modal', function() {
            // Configure and show embedded page with modal callback
            Checkout.configure({
                session: {
                    id: checkoutHandler.sessionId
                }
            });

            Checkout.showEmbeddedPage('#hco-embedded', function() {
                // This callback tells Checkout.js how to show the modal
                $('#paymentModal').modal('show');
            });
        });

        // Clear session storage when modal is closed
        $('#paymentModal').on('hide.bs.modal', function() {
            sessionStorage.clear();
        });

        // Handle modal close confirmation
        $('#paymentModal .close, #paymentModal [data-dismiss="modal"]').on('click', function(e) {
            const confirmed = confirm('Are you sure you want to cancel this payment?');
            if (!confirmed) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mastercard Payment Gateway - Hosted Checkout</title>
    <link rel="stylesheet" href="assets/css/checkout-styles.css">
    <style>
        .hero {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #eb001b, #f79e1b);
            color: white;
            border-radius: 8px;
            margin-bottom: 40px;
        }
        .hero h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        .feature {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }
        .feature h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 28px;
            }
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <div class="logo">
                <div class="logo-circle logo-mc-red">M</div>
                <div class="logo-circle logo-mc-orange">C</div>
            </div>
            <h1>Mastercard Payment Gateway</h1>
            <p>Hosted Checkout Integration</p>
            <p style="font-size: 14px; opacity: 0.9;">Secure, PCI-compliant payment processing for your business</p>
            
            <div class="cta-buttons">
                <a href="checkout.php" class="btn btn-primary btn-block" style="display: inline-block; max-width: 200px;">
                    Start Checkout
                </a>
                <a href="checkout.php?demo=1" class="btn btn-secondary btn-block" style="display: inline-block; max-width: 200px;">
                    Try Demo
                </a>
            </div>
        </div>

        <div class="features">
            <div class="feature">
                <h3>🔒 Secure</h3>
                <p>PCI DSS compliant payment processing with end-to-end encryption</p>
            </div>
            <div class="feature">
                <h3>⚡ Fast</h3>
                <p>Quick integration with simple PHP and JavaScript implementation</p>
            </div>
            <div class="feature">
                <h3>📱 Responsive</h3>
                <p>Works seamlessly on desktop, tablet, and mobile devices</p>
            </div>
            <div class="feature">
                <h3>🎨 Customizable</h3>
                <p>Embedded or redirect options to match your brand</p>
            </div>
            <div class="feature">
                <h3>💳 Multi-Currency</h3>
                <p>Accept payments in multiple currencies worldwide</p>
            </div>
            <div class="feature">
                <h3>📊 Complete</h3>
                <p>Full transaction management with capture, refund, and void</p>
            </div>
        </div>

        <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: var(--shadow); margin-top: 40px;">
            <h2 style="text-align: center; margin-bottom: 30px;">Quick Start Guide</h2>
            
            <ol style="line-height: 2;">
                <li><strong>Configure Credentials:</strong> Copy <code>.env.example</code> to <code>.env</code> and add your API credentials</li>
                <li><strong>Start Server:</strong> Run <code>php -S localhost:8000</code> or use your web server</li>
                <li><strong>Test Payment:</strong> Navigate to <code>checkout.php</code> and try a test transaction</li>
                <li><strong>View Receipt:</strong> After payment, view the receipt with full transaction details</li>
            </ol>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin-top: 20px;">
                <h4>Implementation Options:</h4>
                <ul>
                    <li><strong>Embedded Page:</strong> Payment form embedded directly in your page</li>
                    <li><strong>Payment Page:</strong> Redirect to gateway-hosted payment page</li>
                    <li><strong>Modal/Lightbox:</strong> Payment form in a Bootstrap modal dialog</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="README.md" class="btn btn-secondary">View Documentation</a>
                <a href="examples/modal-example.php" class="btn btn-secondary">Modal Example</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; padding: 20px; color: #6c757d; font-size: 14px;">
            <p>This is a reference implementation for development and testing purposes.</p>
            <p>Always conduct thorough security reviews before deploying to production.</p>
        </div>
    </div>
</body>
</html>

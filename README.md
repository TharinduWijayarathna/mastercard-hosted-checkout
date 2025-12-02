# Mastercard Payment Gateway - Hosted Checkout Integration

A complete PHP and JavaScript implementation of the Mastercard Payment Gateway Hosted Checkout solution.

### What Requires Merchant Credentials

⏳ Actual payment processing  
⏳ Gateway API communication  
⏳ Checkout.js loading from gateway  
⏳ Session creation  
⏳ Receipt generation with real data  
⏳ Subsequent operations (CAPTURE, REFUND, VOID)  

---

## ⚠️ Important: Merchant Account Limitations

The test merchant account `TEST700182200504` has specific limitations:

### Supported Operations
- ✅ **PURCHASE** - Direct payment (use this for testing)
- ✅ **VERIFY** - Card verification

### NOT Supported
- ❌ **AUTHORIZE** - Two-step payment (disabled)
- ❌ **CAPTURE** - Cannot be used without AUTHORIZE
- ❌ **USD, EUR, GBP** - Only LKR is supported

> **Note:** The application is pre-configured for these limitations. PURCHASE is set as the default operation, and LKR is the default currency. Contact your payment service provider to enable additional operations or currencies.

For details, see [MERCHANT_LIMITATIONS.md](file:///Users/tharinduwijayarathna/Desktop/ipg/MERCHANT_LIMITATIONS.md)

## Features

✅ **Initiate Checkout** - Create payment sessions via API  
✅ **Embedded Page** - Payment form embedded in your website  
✅ **Payment Page** - Redirect to separate payment page  
✅ **Subsequent Operations** - CAPTURE, REFUND, VOID support  
✅ **Order Management** - Retrieve and display order details  
✅ **Receipt Generation** - Professional payment receipts  
✅ **Responsive Design** - Mobile-optimized interface  
✅ **Error Handling** - Comprehensive callback management  

## Prerequisites

Before you begin, ensure you have:

1. **PHP 7.4 or higher** with cURL extension enabled
2. **Web server** (Apache, Nginx, or PHP built-in server)
3. **Merchant Account** with Mastercard Payment Gateway
4. **API Credentials** (Merchant ID and API Password)
5. **Gateway Access** configured for Hosted Checkout

## Quick Start

### 1. Clone or Download

```bash
cd /your/web/directory
```

### 2. Configure Credentials

Create a `.env` file from the example:

```bash
cp .env.example .env
```

Edit `.env` and add your credentials:

```ini
GATEWAY_ENVIRONMENT=test
MERCHANT_ID=your_merchant_id
API_PASSWORD=your_api_password
MERCHANT_NAME=Your Store Name
```

### 3. Start Server

**Option A: PHP Built-in Server (for testing)**
```bash
php -S localhost:8000
```

**Option B: Apache/Nginx**
- Point your virtual host to the project directory
- Ensure `mod_rewrite` is enabled (Apache)

### 4. Open in Browser

Navigate to:
```
http://localhost:8000/checkout.php
```

Or add `?demo=1` for pre-filled demo data:
```
http://localhost:8000/checkout.php?demo=1
```

## Project Structure

```
ipg/
├── config.php                      # Configuration loader
├── .env.example                    # Environment template
├── .env                           # Your credentials (create this)
├── checkout.php                    # Main checkout page
├── receipt.php                     # Payment receipt page
├── src/
│   └── MastercardGateway.php      # API client class
├── api/
│   ├── initiate-checkout.php      # Initiate checkout endpoint
│   ├── get-order-result.php       # Get order details endpoint
│   └── subsequent-operations.php  # CAPTURE/REFUND/VOID endpoint
├── assets/
│   ├── css/
│   │   └── checkout-styles.css    # Responsive CSS
│   └── js/
│       └── checkout-handler.js    # JavaScript handler
├── examples/
│   └── modal-example.php          # Modal/Lightbox example
└── logs/
    └── gateway.log                # API request/response logs (auto-created)
```

## Usage

### Basic Payment Flow

1. **Customer fills order form** on `checkout.php`
2. **Clicks payment button** (Embedded or Payment Page)
3. **JavaScript initiates checkout** via API
4. **Payment form displays** (embedded or redirected)
5. **Customer enters card details** and confirms
6. **Gateway processes payment** and redirects to receipt
7. **Receipt page displays** transaction details

### Embedded Page Example

```javascript
const checkoutHandler = new CheckoutHandler({
    apiBaseUrl: 'api',
    gatewayUrl: 'https://test-bankofceylon.mtf.gateway.mastercard.com'
});

// Initiate checkout
const session = await checkoutHandler.initiateCheckout({
    orderId: 'ORDER-123',
    amount: '100.00',
    currency: 'USD',
    description: 'Product Purchase',
    operation: 'PAY'
});

// Show embedded page
checkoutHandler.showEmbeddedPage('#embed-target');
```

### Payment Page Example

```javascript
// Same initiation as above, then:
checkoutHandler.showPaymentPage();
// This will redirect to gateway payment page
```

### Subsequent Operations

#### Capture Payment

```javascript
await checkoutHandler.performOperation(
    'CAPTURE',
    'ORDER-123',
    'TXN-456',
    { amount: '100.00', currency: 'USD' }
);
```

#### Refund Payment

```javascript
await checkoutHandler.performOperation(
    'REFUND',
    'ORDER-123',
    'TXN-789',
    { amount: '50.00', currency: 'USD' }
);
```

#### Void Transaction

```javascript
await checkoutHandler.performOperation(
    'VOID',
    'ORDER-123',
    'TXN-456'
);
```

## Configuration Options

### Environment Variables

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `GATEWAY_ENVIRONMENT` | `test` or `production` | No | `test` |
| `MERCHANT_ID` | Your merchant ID | Yes | - |
| `API_PASSWORD` | Your API password | Yes | - |
| `GATEWAY_URL_TEST` | Test gateway URL | No | Bank of Ceylon test URL |
| `GATEWAY_URL_PROD` | Production gateway URL | No | Bank of Ceylon prod URL |
| `API_VERSION` | API version number | No | `100` |
| `CURRENCY` | Default currency | No | `USD` |
| `MERCHANT_NAME` | Display name | No | `My Store` |

### Transaction Types

- **PURCHASE** - Direct payment (single step) - ✅ **Default and recommended**
- **VERIFY** - Card verification (usually $0 amount)

> **Note:** AUTHORIZE/CAPTURE workflow is not available for this merchant account.

## API Endpoints

### POST /api/initiate-checkout.php

Initiates a checkout session.

**Request:**
```json
{
    "orderId": "ORDER-123",
    "amount": "100.00",
    "currency": "USD",
    "description": "Product Purchase",
    "operation": "AUTHORIZE",
    "customer": {
        "email": "customer@example.com"
    }
}
```

**Response:**
```json
{
    "success": true,
    "sessionId": "SESSION123...",
    "successIndicator": "ABC123...",
    "orderId": "ORDER-123"
}
```

### GET /api/get-order-result.php?orderId=ORDER-123

Retrieves order details.

**Response:**
```json
{
    "success": true,
    "order": {
        "id": "ORDER-123",
        "amount": "100.00",
        "currency": "USD",
        "status": "CAPTURED"
    },
    "transactions": [...],
    "paymentMethod": {...}
}
```

### POST /api/subsequent-operations.php

Performs CAPTURE, REFUND, or VOID.

**Request:**
```json
{
    "operation": "CAPTURE",
    "orderId": "ORDER-123",
    "transactionId": "TXN-456",
    "amount": "100.00",
    "currency": "USD"
}
```

## Testing

### Test Card Numbers

Use these card numbers in the **test environment**:

| Card Number | Type | Result |
|-------------|------|--------|
| 5123456789012346 | Mastercard | Success |
| 4005550000000001 | Visa | Success |
| 2223000048400011 | Mastercard | Success |
| 4005550000000019 | Visa | Declined |

**Note:** Contact your payment service provider for specific test cards.

### Testing Workflow

1. Use test credentials in `.env`
2. Set `GATEWAY_ENVIRONMENT=test`
3. Use test card numbers
4. Check logs in `logs/gateway.log`
5. Verify in Merchant Administration portal

## Security Best Practices

🔒 **Never commit `.env` file** - Add to `.gitignore`  
🔒 **Use HTTPS in production** - Required for PCI compliance  
🔒 **Validate all inputs** - Server-side validation essential  
🔒 **Keep API credentials secure** - Restrict file permissions  
🔒 **Enable logging in test only** - Disable in production  
🔒 **Regular security updates** - Keep PHP and dependencies updated  

## Troubleshooting

### Issue: "Gateway connection error"

**Solution:** Check your internet connection and gateway URL in config.

### Issue: "Authentication failed"

**Solution:** Verify `MERCHANT_ID` and `API_PASSWORD` in `.env` file.

### Issue: "Session not found"

**Solution:** Ensure `sessionId` is correctly passed to Checkout.configure().

### Issue: "Checkout.js not loading"

**Solution:** Check gateway URL and ensure it's reachable. Verify SSL certificate.

### Issue: "CORS errors in console"

**Solution:** API endpoints include CORS headers. Ensure same-origin or configure properly.

## Support

- **Gateway Documentation:** [Mastercard Gateway Docs](https://test-bankofceylon.mtf.gateway.mastercard.com/api/documentation/)
- **API Reference:** Check your gateway's API documentation
- **Merchant Support:** Contact your payment service provider

## License

This implementation is provided as-is for integration with Mastercard Payment Gateway.

## Contributing

Feel free to submit issues, fork the repository, and create pull requests for improvements.

---

**⚠️ Important:** This is a reference implementation. Always conduct thorough testing and security reviews before deploying to production.

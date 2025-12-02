# Mastercard Payment Gateway - Hosted Checkout

Simple PHP and JavaScript implementation for Bank of Ceylon Mastercard Payment Gateway.

## Quick Start

### 1. Configure Credentials

Copy `.env.example` to `.env` and add your credentials:

```bash
cp .env.example .env
```

Edit `.env`:
```ini
MERCHANT_ID=your_merchant_id
API_PASSWORD=your_api_password
MERCHANT_NAME=Your Store Name
```

### 2. Start Server

```bash
php -S localhost:8000
```

### 3. Test

Open in browser:
```
http://localhost:8000/checkout.php?demo=1
```

**Test Card:** `5123456789012346`

---

## Prerequisites

- PHP 7.4+ with cURL extension
- Merchant account with Bank of Ceylon
- API credentials (Merchant ID & Password)

## Features

- **Embedded Page** - Payment form integrated in your website
- **Payment Page** - Redirect to gateway-hosted page
- **LKR Currency** - Sri Lankan Rupee support
- **PURCHASE** - Direct payment processing
- **VERIFY** - Card verification
- **Receipt** - Professional payment receipts
- **Responsive** - Mobile-optimized design

## Important Limitations

This merchant account configuration supports:

**Supported:**
- Currency: LKR only
- Operations: PURCHASE, VERIFY

**Not Supported:**
- AUTHORIZE, CAPTURE operations
- USD, EUR, GBP currencies

## Project Structure

```
ipg/
├── checkout.php              # Main checkout page
├── receipt.php               # Payment receipt
├── config.php                # Configuration
├── .env                      # Your credentials
├── src/
│   └── MastercardGateway.php # API client
├── api/
│   ├── initiate-checkout.php
│   ├── get-order-result.php
│   └── subsequent-operations.php
└── assets/
    ├── css/checkout-styles.css
    └── js/checkout-handler.js
```

## Configuration

| Variable | Required | Default |
|----------|----------|---------|
| `MERCHANT_ID` | Yes | - |
| `API_PASSWORD` | Yes | - |
| `MERCHANT_NAME` | No | My Store |
| `GATEWAY_ENVIRONMENT` | No | test |

## Testing

### Test Cards

| Card Number | Type | Result |
|-------------|------|--------|
| 5123456789012346 | Mastercard | Success |
| 4005550000000001 | Visa | Success |
| 4005550000000019 | Visa | Declined |

### Test Flow

1. Open `http://localhost:8000/checkout.php?demo=1`
2. Click "Pay with Embedded Page" or "Pay with Payment Page"
3. Enter test card: `5123456789012346`
4. Complete payment
5. View receipt

## Security

- `.env` file excluded via `.gitignore`
- HTTPS required for production deployment
- Server-side input validation
- Basic authentication for API requests
- Logging enabled in test mode only

## Troubleshooting

**Connection Error:**
- Verify internet connection
- Check gateway URL configuration

**Authentication Failed:**
- Verify `MERCHANT_ID` and `API_PASSWORD` in `.env` file
- Ensure credentials match merchant account

**Currency Error:**
- Use LKR currency only
- USD/EUR/GBP not supported for this merchant

**Operation Error:**
- Use PURCHASE operation
- AUTHORIZE not enabled for this merchant account

## Documentation

- [Implementation Summary](IMPLEMENTATION_SUMMARY.md)
- [Merchant Limitations](MERCHANT_LIMITATIONS.md)
- [Official Gateway Documentation](https://test-bankofceylon.mtf.gateway.mastercard.com/api/documentation/)

## Support

Contact your payment service provider (Bank of Ceylon) for:
- Enabling additional operations (AUTHORIZE/CAPTURE)
- Adding support for additional currencies
- Production credentials and deployment

---

**Note:** This implementation is configured for Bank of Ceylon test environment. Conduct thorough testing before production deployment.

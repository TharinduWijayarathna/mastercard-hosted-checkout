<?php
/**
 * Get Order Result API Endpoint
 * 
 * Retrieves order details after payment completion
 */

header('Content-Type: application/json');

// Enable CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Load configuration and gateway client
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../src/MastercardGateway.php';
    
    $config = require __DIR__ . '/../config.php';
    $gateway = new MastercardGateway($config);
    
    // Get order ID from query string
    $orderId = $_GET['orderId'] ?? null;
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    // Retrieve order details
    $orderDetails = $gateway->retrieveOrder($orderId);
    
    // Extract relevant information
    $result = [
        'success' => true,
        'order' => [
            'id' => $orderDetails['id'] ?? null,
            'amount' => $orderDetails['amount'] ?? null,
            'currency' => $orderDetails['currency'] ?? null,
            'status' => $orderDetails['status'] ?? null,
            'description' => $orderDetails['description'] ?? null,
            'creationTime' => $orderDetails['creationTime'] ?? null,
            'totalAuthorizedAmount' => $orderDetails['totalAuthorizedAmount'] ?? null,
            'totalCapturedAmount' => $orderDetails['totalCapturedAmount'] ?? null,
            'totalRefundedAmount' => $orderDetails['totalRefundedAmount'] ?? null
        ],
        'transactions' => []
    ];
    
    // Extract transaction details
    if (isset($orderDetails['transaction'])) {
        foreach ($orderDetails['transaction'] as $transaction) {
            $result['transactions'][] = [
                'id' => $transaction['id'] ?? null,
                'type' => $transaction['transaction']['type'] ?? null,
                'amount' => $transaction['transaction']['amount'] ?? null,
                'currency' => $transaction['transaction']['currency'] ?? null,
                'authorizationCode' => $transaction['transaction']['authorizationCode'] ?? null,
                'receipt' => $transaction['transaction']['receipt'] ?? null,
                'result' => $transaction['result'] ?? null,
                'timestamp' => $transaction['timeOfRecord'] ?? null
            ];
        }
    }
    
    // Add source of funds if available
    if (isset($orderDetails['sourceOfFunds'])) {
        $result['paymentMethod'] = [
            'type' => $orderDetails['sourceOfFunds']['type'] ?? null,
            'provided' => $orderDetails['sourceOfFunds']['provided'] ?? []
        ];
        
        // Add card details if available (masked)
        if (isset($orderDetails['sourceOfFunds']['provided']['card'])) {
            $result['paymentMethod']['card'] = [
                'number' => $orderDetails['sourceOfFunds']['provided']['card']['number'] ?? null,
                'scheme' => $orderDetails['sourceOfFunds']['provided']['card']['scheme'] ?? null,
                'brand' => $orderDetails['sourceOfFunds']['provided']['card']['brand'] ?? null,
                'expiry' => $orderDetails['sourceOfFunds']['provided']['card']['expiry'] ?? null
            ];
        }
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

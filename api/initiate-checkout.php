<?php
/**
 * Initiate Checkout API Endpoint
 * 
 * This endpoint receives order details and creates a checkout session
 */

header('Content-Type: application/json');

// Enable CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Get request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Extract order details
    $orderData = [
        'id' => $data['orderId'] ?? 'ORDER-' . time(),
        'amount' => $data['amount'] ?? null,
        'currency' => $data['currency'] ?? $config['currency'],
        'description' => $data['description'] ?? 'Online Purchase'
    ];
    
    // Get operation type
    $operation = $data['operation'] ?? 'AUTHORIZE';
    
    // Build options
    $options = [];
    
    // Get current protocol and host for return URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . dirname(dirname($_SERVER['PHP_SELF']));
    
    // Add return URL
    if (isset($data['returnUrl'])) {
        $options['returnUrl'] = $data['returnUrl'];
    } else {
        $options['returnUrl'] = $baseUrl . '/receipt.php';
    }
    
    // Add customer details if provided
    if (isset($data['customer'])) {
        $options['customer'] = $data['customer'];
    }
    
    // Add billing details if provided
    if (isset($data['billing'])) {
        $options['billing'] = $data['billing'];
    }
    
    // Add shipping details if provided
    if (isset($data['shipping'])) {
        $options['shipping'] = $data['shipping'];
    }
    
    // Initiate checkout
    $response = $gateway->initiateCheckout($orderData, $operation, $options);
    
    // Return session details
    echo json_encode([
        'success' => true,
        'sessionId' => $response['session']['id'] ?? null,
        'successIndicator' => $response['successIndicator'] ?? null,
        'orderId' => $orderData['id']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

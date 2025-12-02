<?php
/**
 * Subsequent Operations API Endpoint
 * 
 * Handles CAPTURE, REFUND, and VOID operations
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
    
    // Validate required fields
    $operation = $data['operation'] ?? null;
    $orderId = $data['orderId'] ?? null;
    $transactionId = $data['transactionId'] ?? null;
    
    if (!$operation) {
        throw new Exception('Operation type is required');
    }
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    if (!$transactionId) {
        throw new Exception('Transaction ID is required');
    }
    
    $response = null;
    
    // Execute operation
    switch (strtoupper($operation)) {
        case 'CAPTURE':
            $captureData = [];
            if (isset($data['amount'])) {
                $captureData['amount'] = $data['amount'];
                $captureData['currency'] = $data['currency'] ?? $config['currency'];
            }
            $response = $gateway->capturePayment($orderId, $transactionId, $captureData);
            break;
            
        case 'REFUND':
            if (!isset($data['amount'])) {
                throw new Exception('Refund amount is required');
            }
            $refundData = [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? $config['currency']
            ];
            $response = $gateway->refundPayment($orderId, $transactionId, $refundData);
            break;
            
        case 'VOID':
            $response = $gateway->voidTransaction($orderId, $transactionId);
            break;
            
        default:
            throw new Exception('Invalid operation type. Supported: CAPTURE, REFUND, VOID');
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'operation' => $operation,
        'orderId' => $orderId,
        'transactionId' => $transactionId,
        'response' => $response
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

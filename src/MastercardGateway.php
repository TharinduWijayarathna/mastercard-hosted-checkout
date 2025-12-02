<?php
/**
 * Mastercard Gateway API Client
 * 
 * Handles all API operations for Mastercard Payment Gateway
 */

class MastercardGateway
{
    private $config;
    private $baseUrl;
    private $merchantId;
    private $apiPassword;
    private $apiVersion;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->merchantId = $config['merchant_id'];
        $this->apiPassword = $config['api_password'];
        $this->apiVersion = $config['api_version'];
        $this->baseUrl = $config['gateway_url'];
    }
    
    /**
     * Initiate Checkout Session
     * 
     * @param array $orderData Order details (amount, currency, id, description)
     * @param string $operation Operation type (AUTHORIZE, PURCHASE, or VERIFY)
     * @param array $options Additional options
     * @return array Response containing session.id and successIndicator
     */
    public function initiateCheckout($orderData, $operation = 'AUTHORIZE', $options = [])
    {
        // Validate required fields
        $this->validateOrderData($orderData);
        
        // Build request body
        $requestBody = [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'interaction' => [
                'operation' => $operation,
                'merchant' => [
                    'name' => $this->config['merchant_name']
                ]
            ],
            'order' => [
                'currency' => $orderData['currency'] ?? $this->config['currency'],
                'amount' => $orderData['amount'],
                'id' => $orderData['id'],
                'description' => $orderData['description'] ?? 'Order ' . $orderData['id']
            ]
        ];
        
        // Add return URL if provided
        if (isset($options['returnUrl'])) {
            $requestBody['interaction']['returnUrl'] = $options['returnUrl'];
        }
        
        // Add timeout URL if provided
        if (isset($options['timeoutUrl'])) {
            $requestBody['interaction']['timeoutUrl'] = $options['timeoutUrl'];
        }
        
        // Add cancel URL if provided
        if (isset($options['cancelUrl'])) {
            $requestBody['interaction']['cancelUrl'] = $options['cancelUrl'];
        }
        
        // Add customer details if provided
        if (isset($options['customer'])) {
            $requestBody['customer'] = $options['customer'];
        }
        
        // Add billing details if provided
        if (isset($options['billing'])) {
            $requestBody['billing'] = $options['billing'];
        }
        
        // Add shipping details if provided
        if (isset($options['shipping'])) {
            $requestBody['shipping'] = $options['shipping'];
        }
        
        // Make API request
        $url = $this->buildUrl('/session');
        $response = $this->makeRequest('POST', $url, $requestBody);
        
        return $response;
    }
    
    /**
     * Retrieve Order Details
     * 
     * @param string $orderId Order ID
     * @return array Order details
     */
    public function retrieveOrder($orderId)
    {
        $url = $this->buildUrl('/order/' . $orderId);
        $response = $this->makeRequest('GET', $url);
        
        return $response;
    }
    
    /**
     * Capture Payment
     * 
     * @param string $orderId Order ID
     * @param string $transactionId Transaction ID to capture
     * @param array $captureData Capture details (amount, currency)
     * @return array Response
     */
    public function capturePayment($orderId, $transactionId, $captureData = [])
    {
        $requestBody = [
            'apiOperation' => 'CAPTURE'
        ];
        
        // Add amount and currency if provided
        if (isset($captureData['amount'])) {
            $requestBody['transaction'] = [
                'amount' => $captureData['amount'],
                'currency' => $captureData['currency'] ?? $this->config['currency']
            ];
        }
        
        $url = $this->buildUrl('/order/' . $orderId . '/transaction/' . $transactionId);
        $response = $this->makeRequest('PUT', $url, $requestBody);
        
        return $response;
    }
    
    /**
     * Refund Payment
     * 
     * @param string $orderId Order ID
     * @param string $transactionId New transaction ID for the refund
     * @param array $refundData Refund details (amount, currency)
     * @return array Response
     */
    public function refundPayment($orderId, $transactionId, $refundData)
    {
        $requestBody = [
            'apiOperation' => 'REFUND',
            'transaction' => [
                'amount' => $refundData['amount'],
                'currency' => $refundData['currency'] ?? $this->config['currency']
            ]
        ];
        
        $url = $this->buildUrl('/order/' . $orderId . '/transaction/' . $transactionId);
        $response = $this->makeRequest('PUT', $url, $requestBody);
        
        return $response;
    }
    
    /**
     * Void Transaction
     * 
     * @param string $orderId Order ID
     * @param string $transactionId Transaction ID to void
     * @return array Response
     */
    public function voidTransaction($orderId, $transactionId)
    {
        $requestBody = [
            'apiOperation' => 'VOID'
        ];
        
        $url = $this->buildUrl('/order/' . $orderId . '/transaction/' . $transactionId);
        $response = $this->makeRequest('PUT', $url, $requestBody);
        
        return $response;
    }
    
    /**
     * Retrieve Transaction Details
     * 
     * @param string $orderId Order ID
     * @param string $transactionId Transaction ID
     * @return array Transaction details
     */
    public function retrieveTransaction($orderId, $transactionId)
    {
        $url = $this->buildUrl('/order/' . $orderId . '/transaction/' . $transactionId);
        $response = $this->makeRequest('GET', $url);
        
        return $response;
    }
    
    /**
     * Validate Order Data
     * 
     * @param array $orderData Order data to validate
     * @throws Exception if validation fails
     */
    private function validateOrderData($orderData)
    {
        if (empty($orderData['amount'])) {
            throw new Exception('Order amount is required');
        }
        
        if (empty($orderData['id'])) {
            throw new Exception('Order ID is required');
        }
        
        // Validate amount format
        if (!is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new Exception('Invalid order amount');
        }
    }
    
    /**
     * Build API URL
     * 
     * @param string $endpoint API endpoint
     * @return string Full URL
     */
    private function buildUrl($endpoint)
    {
        return $this->baseUrl . '/api/rest/version/' . $this->apiVersion . 
               '/merchant/' . $this->merchantId . $endpoint;
    }
    
    /**
     * Make HTTP Request to Gateway
     * 
     * @param string $method HTTP method (GET, POST, PUT)
     * @param string $url Full URL
     * @param array $body Request body (optional)
     * @return array Response data
     */
    private function makeRequest($method, $url, $body = null)
    {
        // Initialize cURL
        $ch = curl_init($url);
        
        // Set common options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        // Set authentication
        $authString = 'merchant.' . $this->merchantId . ':' . $this->apiPassword;
        curl_setopt($ch, CURLOPT_USERPWD, $authString);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        
        // Set headers
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Set method and body
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($body) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }
        
        // Log request if enabled
        if ($this->config['enable_logging']) {
            $this->log('REQUEST', $method . ' ' . $url, $body);
        }
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            $this->log('ERROR', 'cURL Error: ' . $error);
            throw new Exception('Gateway connection error: ' . $error);
        }
        
        // Parse response
        $responseData = json_decode($response, true);
        
        // Log response if enabled
        if ($this->config['enable_logging']) {
            $this->log('RESPONSE', 'HTTP ' . $httpCode, $responseData);
        }
        
        // Handle HTTP errors
        if ($httpCode >= 400) {
            $errorMessage = $responseData['error']['explanation'] ?? 'Unknown error';
            throw new Exception('Gateway error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');
        }
        
        return $responseData;
    }
    
    /**
     * Log message to file
     * 
     * @param string $type Log type (REQUEST, RESPONSE, ERROR)
     * @param string $message Log message
     * @param mixed $data Additional data to log
     */
    private function log($type, $message, $data = null)
    {
        if (!$this->config['enable_logging']) {
            return;
        }
        
        $logDir = dirname($this->config['log_file']);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$type] $message\n";
        
        if ($data) {
            $logMessage .= json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
        
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents($this->config['log_file'], $logMessage, FILE_APPEND);
    }
}

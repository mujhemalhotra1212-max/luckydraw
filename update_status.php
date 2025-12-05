<?php
// Allow CORS and handle preflight requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $purchaseCode = trim($input['purchase_code'] ?? '');
    $paymentStatus = trim($input['payment_status'] ?? 'Pending');
    
    // Validate status
    $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
    if (!in_array($paymentStatus, $allowedStatuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid payment status.'
        ]);
        exit;
    }
    
    if (empty($purchaseCode)) {
        echo json_encode([
            'success' => false,
            'message' => 'Purchase Code is required.'
        ]);
        exit;
    }
    
    // Read bookings from JSON file
    $json_file = '/tmp/bookings.json';
    
    if (!file_exists($json_file)) {
        echo json_encode([
            'success' => false,
            'message' => 'Bookings file not found.'
        ]);
        exit;
    }
    
    $json_data = file_get_contents($json_file);
    $bookings = json_decode($json_data, true);
    
    if (!is_array($bookings)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid bookings data.'
        ]);
        exit;
    }
    
    // Find and update booking
    $found = false;
    foreach ($bookings as &$booking) {
        if (isset($booking['purchase_code']) && strtoupper($booking['purchase_code']) === strtoupper($purchaseCode)) {
            $booking['payment_status'] = $paymentStatus;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo json_encode([
            'success' => false,
            'message' => 'Purchase Code not found.'
        ]);
        exit;
    }
    
    // Save updated bookings
    try {
        file_put_contents($json_file, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment status updated successfully.',
            'payment_status' => $paymentStatus
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update status: ' . $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>

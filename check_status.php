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
    
    if (empty($purchaseCode)) {
        echo json_encode([
            'success' => false,
            'message' => 'Purchase Code is required.'
        ]);
        exit;
    }
    
    // Read bookings from JSON file
    $json_file = 'bookings.json';
    
    if (!file_exists($json_file)) {
        echo json_encode([
            'success' => false,
            'message' => 'Purchase Code अनिवार्य है।'
        ]);
        exit;
    }
    
    $json_data = file_get_contents($json_file);
    $bookings = json_decode($json_data, true);
    
    if (!is_array($bookings)) {
        echo json_encode([
            'success' => false,
            'message' => 'Purchase Code अनिवार्य है।'
        ]);
        exit;
    }
    
    // Find booking by purchase code
    $booking = null;
    foreach ($bookings as $b) {
        if (isset($b['purchase_code']) && strtoupper($b['purchase_code']) === strtoupper($purchaseCode)) {
            $booking = $b;
            break;
        }
    }
    
    if (!$booking) {
        echo json_encode([
            'success' => false,
            'message' => 'Purchase Code अनिवार्य है।'
        ]);
        exit;
    }
    
    // Mask mobile number (show only last 4 digits)
    $mobile = $booking['mobile'] ?? '';
    $maskedMobile = '*****' . substr($mobile, -4);
    
    // Calculate total price (ticket_count * 101)
    $ticketCount = intval($booking['ticket_count'] ?? 1);
    $totalPrice = $ticketCount * 101;
    
    // Return booking details
    echo json_encode([
        'success' => true,
        'data' => [
            'name' => $booking['name'] ?? '',
            'mobile' => $maskedMobile,
            'purchase_code' => $booking['purchase_code'] ?? '',
            'ticket_count' => $ticketCount,
            'total_price' => $totalPrice,
            'payment_status' => $booking['payment_status'] ?? 'Pending',
            'draw_date' => '1 जनवरी 2026', // You can make this dynamic
            'timestamp' => $booking['timestamp'] ?? ''
        ]
    ]);
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>


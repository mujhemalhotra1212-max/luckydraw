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
    
    // Generate unique token (format: PUR-XXXXXXXXXXXX)
    $randomPart = strtoupper(bin2hex(random_bytes(6))); // 12 characters
    $purchaseCode = "PUR-" . $randomPart;
    
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $aadhar = trim($_POST['aadhar'] ?? '');
    $ticket_count = trim($_POST['ticket_count'] ?? '');
    $utr = trim($_POST['utr'] ?? '');
    $upi_id_used = trim($_POST['upi_id_used'] ?? '');
    
    // Handle screenshot file
    $screenshot_name = '';
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $screenshot_name = $_FILES['screenshot']['name'];
        // Optionally save the file
        $upload_dir = '/tmp/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($screenshot_name, PATHINFO_EXTENSION);
        $new_filename = $purchaseCode . '_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['screenshot']['tmp_name'], $upload_dir . $new_filename);
        $screenshot_name = $new_filename;
    }
    
    // Create booking data
    $booking_data = [
        'purchase_code' => $purchaseCode,
        'name' => $name,
        'mobile' => $mobile,
        'aadhar' => $aadhar,
        'ticket_count' => $ticket_count,
        'utr' => $utr,
        'upi_id_used' => $upi_id_used,
        'screenshot' => $screenshot_name,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Read existing bookings from JSON file
    $json_file = '/tmp/bookings.json';
    $bookings = [];
    
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $bookings = json_decode($json_data, true);
        if (!is_array($bookings)) {
            $bookings = [];
        }
    }
    
    // Add new booking
    $bookings[] = $booking_data;
    
    // Save to JSON file
    try {
        file_put_contents($json_file, json_encode($bookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking saved successfully.',
            'purchase_code' => $purchaseCode
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save booking: ' . $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>

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

        'token' => $token,
        'ticket_count' => $ticket_count,
        'timestamp' => date('Y-m-d H:i:s')
    ];
            $json_data = file_get_contents($log_file);
            $bookings = json_decode($json_data, true);
            if (!is_array($bookings)) {
            }
        }
        // नई बुकिंग जोड़ें
        $bookings[] = $new_booking;
       $e) {

        echo json_encode(['success' => false, 'message' => 'डेटा सहेजने में विफल: ' . $e->getMessage()]);
      
    // --- उपयोगकर्ता को ईमेल भेजें ---
    $to = $user_email;
    $from_email = 'mujhemalhotra1212@gmail.com'; // भेजने वाला ईमेल
    $from_name = 'जीत के धमाके';
    $subject = 'आपका जीत के धमाके का लकी टोकन!';

    // --- ईमेल हेडर ---
    $headers = "From: \"$from_name\" <$from_email>\r\n";
    $hnt-Type: text/html; charset=UTF-8\r\n";

    // --- HTML ईमेल संदेश ---
    $message .= "
    <html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
        <h2 style='color: #6a0dad;'>नमस्ते $name,</h2>
        <p>जीत के धमाके लकी ड्रॉ में भाग लेने के लिए धन्यवाद!</p>
        <p>आपका यूनिक लकी टोकन है:</p>
        <p style='font-size: 24px; font-weight: bold; color: #d97706; border: 2px dashed #6a0dad; padding: 10px; text-align: center;'>
            $token
        </p>
        <p>कृपया इस टोकन को भविष्य के लिए संभाल कर रखें। ड्रॉ के परिणाम हमारी वेबसाइट पर घोषित किए जाएंगे।</p>
        <p>शुभकामनाएं!</p>
        <p><strong>- टीम जीत के धमाके</strong></p>
    </body>
    </html>

    // --- ईमेलosage' => 'बुकिंग सफल हुई और ईमेल भेज दिया गया है।',
            'token' => $token
        ]
} els$d
    // Debug: Log the actual request method received
    $actualMethod = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    error_log("contact.php received method: " . $actualMethod);
    
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Method Not Allowed. Received: ' . $actualMethod . ', Expected: POST',
        'received_method' => $actualMethod
    ]);
}

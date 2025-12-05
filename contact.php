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

    $to = 'mujhemalhotra1212@gmail.com';
    $from = 'noreply@luckydraw.com';

    // --- UNIQUE PURCHASE CODE ---
    $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $_POST['name'] ?? 'XX'), 0, 2));
    $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    $purchaseCode = "PUR-" . $namePart . $randomPart;

    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $ticket_count = trim($_POST['ticket_count'] ?? '');
    $utr = trim($_POST['utr'] ?? '');
    $upi_id_used = trim($_POST['upi_id_used'] ?? '');

    $subject = "जीत के धमाके - नई टिकट बुकिंग: " . $name;

    // Boundary
    $boundary = md5(time());

    // --- HEADERS ---
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

    // --- HTML MESSAGE BODY ---
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

    $message .= "
    <html><body>
        <h2>जीत के धमाके - नई बुकिंग</h2>
        <p><strong>नाम:</strong> $name</p>
        <p><strong>WhatsApp नंबर:</strong> $mobile</p>
        <p><strong>Purchase Code:</strong> $purchaseCode</p>
        <p><strong>टोकन संख्या:</strong> $ticket_count</p>
        <p><strong>UTR/Transaction ID:</strong> $utr</p>
        <p><strong>UPI ID:</strong> $upi_id_used</p>
        <p>पेमेंट स्क्रीनशॉट अटैचमेंट में है।</p>
    </body></html>
    ";

    // --- ATTACHMENT (Screenshot) ---
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {

        $file_name = $_FILES['screenshot']['name'];
        $file_type = $_FILES['screenshot']['type'];
        $file_content = chunk_split(base64_encode(file_get_contents($_FILES['screenshot']['tmp_name'])));

        $message .= "--$boundary\r\n";
        $message .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
        $message .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= $file_content . "\r\n";
    }

    $message .= "--$boundary--";

    // --- LOG FILE ---
    $log = "================================\n";
    $log .= "Timestamp: " . date("Y-m-d H:i:s") . "\n";
    $log .= "Name: $name\n";
    $log .= "Mobile: $mobile\n";
    $log .= "Purchase Code: $purchaseCode\n";
    $log .= "Ticket Count: $ticket_count\n";
    $log .= "UTR: $utr\n";
    $log .= "UPI ID: $upi_id_used\n";
    $log .= "Screenshot: " . ($file_name ?? 'No file') . "\n";
    $log .= "================================\n\n";

    file_put_contents("bookings.log", $log, FILE_APPEND);

    // --- SEND EMAIL ---
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode([
            'success' => true,
            'message' => 'Email sent successfully.',
            'purchase_code' => $purchaseCode
        ]);
    } else {
        error_log("Mail failed", 3, "php_errors.log");

        echo json_encode([
            'success' => true,
            'message' => 'Booking logged, email failed.',
            'purchase_code' => $purchaseCode
        ]);
    }

} else {
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
?>

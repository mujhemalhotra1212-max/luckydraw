<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $to = 'mujhemalhotra1212@gmail.com';
    
    // From हेडर ईमेल भेजने के लिए ज़रूरी है ताकि यह स्पैम में न जाए।
    // आपको इस ईमेल को बनाने की ज़रूरत नहीं है, यह सिर्फ एक लेबल है।
    // 'noreply@yourdomain.com' एक सुरक्षित विकल्प है।
    $from = 'noreply@luckydraw.com';

    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $ticket_count = isset($_POST['ticket_count']) ? trim($_POST['ticket_count']) : '';
    $utr = isset($_POST['utr']) ? trim($_POST['utr']) : '';
    $upi_id_used = isset($_POST['upi_id_used']) ? trim($_POST['upi_id_used']) : '';

    $subject = "जीत के धमाके - नई टिकट बुकिंग: " . $name;

    // Boundary for multipart email
    $boundary = md5(time());

    // Headers
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Message Body (HTML)
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= "<html><body>";
    $message .= "<h2>जीत के धमाके - नई बुकिंग</h2>";
    $message .= "<p><strong>नाम:</strong> " . htmlspecialchars($name) . "</p>";
    $message .= "<p><strong>WhatsApp नंबर:</strong> " . htmlspecialchars($mobile) . "</p>";
    $message .= "<p><strong>टोकन संख्या:</strong> " . htmlspecialchars($ticket_count) . "</p>";
    $message .= "<p><strong>UTR/Transaction ID:</strong> " . htmlspecialchars($utr) . "</p>";
    $message .= "<p><strong>UPI ID (अगर दी गई है):</strong> " . htmlspecialchars($upi_id_used) . "</p>";
    $message .= "<p>पेमेंट स्क्रीनशॉट अटैचमेंट में है।</p>";
    $message .= "</body></html>\r\n";

    // Attachment
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['screenshot']['tmp_name'];
        $file_name = $_FILES['screenshot']['name'];
        $file_size = $_FILES['screenshot']['size'];
        $file_type = $_FILES['screenshot']['type'];

        $file_content = file_get_contents($file_tmp_name);
        $encoded_content = chunk_split(base64_encode($file_content));

        $message .= "--$boundary\r\n";
        $message .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
        $message .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $message .= $encoded_content . "\r\n";
    }

    $message .= "--$boundary--";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
    } else {
        // Check for errors
        $error = error_get_last();
        $errorMessage = 'Email could not be sent.';
        if ($error !== null) {
            $errorMessage .= ' Error: ' . $error['message'];
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>
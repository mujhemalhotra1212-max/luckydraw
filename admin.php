<?php
// Simple authentication (you can improve this)
session_start();
$admin_password = 'luckydraw@9090'; // Change this password

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = 'Invalid password';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    ?>
    <!DOCTYPE html>
    <html lang="hi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - जीत के धमाके</title>
        <script src="https://cdn.tailwindcss.com/"></script>
    </head>
    <body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">
        <div class="bg-gray-800 p-8 rounded-xl shadow-xl max-w-md w-full">
            <h1 class="text-3xl font-bold text-center mb-6 text-yellow-400">Admin Login</h1>
            <?php if (isset($error)): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3">
                </div>
                <button type="submit" name="login" 
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg">
                    Login
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Read bookings
$bookings_file = '/tmp/bookings.json';
$bookings = [];
if (file_exists($bookings_file)) {
    $bookings = json_decode(file_get_contents($bookings_file), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - जीत के धमाके</title>
    <script src="https://cdn.tailwindcss.com/"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-bold text-yellow-400">Admin Panel</h1>
            <a href="?logout=1" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg">Logout</a>
        </div>

        <div class="bg-gray-800 rounded-xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Total Bookings: <?= count($bookings) ?></h2>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <?php foreach (array_reverse($bookings) as $index => $booking): ?>
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column: Details -->
                        <div class="space-y-4">
                            <h3 class="text-2xl font-bold text-purple-300 mb-4">
                                Booking #<?= count($bookings) - $index ?>
                            </h3>
                            
                            <div class="space-y-2 text-lg">
                                <div><strong>Purchase Code:</strong> 
                                    <span class="font-mono text-yellow-400"><?= htmlspecialchars($booking['purchase_code'] ?? 'N/A') ?></span>
                                </div>
                                <div><strong>नाम:</strong> <?= htmlspecialchars($booking['name'] ?? 'N/A') ?></div>
                                <div><strong>मोबाइल:</strong> <?= htmlspecialchars($booking['mobile'] ?? 'N/A') ?></div>
                                <div><strong>आधार:</strong> <?= htmlspecialchars($booking['aadhar'] ?? 'N/A') ?></div>
                                <div><strong>टिकट संख्या:</strong> <?= htmlspecialchars($booking['ticket_count'] ?? '1') ?></div>
                                <div><strong>कुल कीमत:</strong> ₹<?= intval($booking['ticket_count'] ?? 1) * 101 ?></div>
                                <div><strong>UTR:</strong> <?= htmlspecialchars($booking['utr'] ?? 'N/A') ?></div>
                                <div><strong>UPI ID:</strong> <?= htmlspecialchars($booking['upi_id_used'] ?? 'N/A') ?></div>
                                <div><strong>Timestamp:</strong> <?= htmlspecialchars($booking['timestamp'] ?? 'N/A') ?></div>
                                
                                <div class="pt-2">
                                    <strong>Payment Status:</strong>
                                    <span id="status-<?= $index ?>" class="ml-2 px-3 py-1 rounded font-bold
                                        <?php 
                                        $status = $booking['payment_status'] ?? 'Pending';
                                        if ($status === 'Approved') echo 'bg-green-600 text-white';
                                        elseif ($status === 'Rejected') echo 'bg-red-600 text-white';
                                        else echo 'bg-orange-500 text-white';
                                        ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Status Update Buttons -->
                            <div class="flex gap-2 pt-4">
                                <button onclick="updateStatus('<?= htmlspecialchars($booking['purchase_code']) ?>', 'Approved', <?= $index ?>)"
                                        class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg font-bold">
                                    Approve
                                </button>
                                <button onclick="updateStatus('<?= htmlspecialchars($booking['purchase_code']) ?>', 'Rejected', <?= $index ?>)"
                                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg font-bold">
                                    Reject
                                </button>
                                <button onclick="updateStatus('<?= htmlspecialchars($booking['purchase_code']) ?>', 'Pending', <?= $index ?>)"
                                        class="bg-orange-500 hover:bg-orange-600 px-4 py-2 rounded-lg font-bold">
                                    Pending
                                </button>
                            </div>
                        </div>

                        <!-- Right Column: Screenshot -->
                        <div>
                            <?php if (!empty($booking['screenshot'])): 
                                $screenshot_path = '/tmp/uploads/' . $booking['screenshot'];
                                if (file_exists($screenshot_path)):
                            ?>
                                <div class="bg-gray-900 p-4 rounded-lg">
                                    <h4 class="text-lg font-bold mb-2">Payment Screenshot:</h4>
                                    <img src="<?= htmlspecialchars($screenshot_path) ?>" 
                                         alt="Screenshot" 
                                         class="w-full h-auto rounded-lg border border-gray-700 max-h-96 object-contain">
                                </div>
                            <?php else: ?>
                                <div class="bg-gray-900 p-4 rounded-lg text-center">
                                    <p class="text-gray-400">Screenshot not found</p>
                                </div>
                            <?php endif; ?>
                            <?php else: ?>
                                <div class="bg-gray-900 p-4 rounded-lg text-center">
                                    <p class="text-gray-400">No screenshot uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        async function updateStatus(purchaseCode, status, index) {
            if (!confirm(`Are you sure you want to set status to "${status}"?`)) {
                return;
            }

            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        purchase_code: purchaseCode,
                        payment_status: status
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update UI
                    const statusElement = document.getElementById('status-' + index);
                    statusElement.textContent = status;
                    statusElement.className = 'ml-2 px-3 py-1 rounded font-bold ' + 
                        (status === 'Approved' ? 'bg-green-600 text-white' :
                         status === 'Rejected' ? 'bg-red-600 text-white' :
                         'bg-orange-500 text-white');
                    
                    alert('Status updated successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error updating status: ' + error.message);
            }
        }
    </script>
</body>
</html>

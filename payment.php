<?php
// Start the session (optional, if you need user session data)
session_start();

// Include the database connection file
include 'includes/dp.php'; // Adjust the path based on your project structure

// Initialize variables for form data and error messages
$passengerName = '';
$ticketName = '';
$ticketPrice = '';
$error_message = '';

// Handle GET request to populate form fields from URL parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Parse URL query string to get parameters
    $queryString = $_SERVER['QUERY_STRING'];
    parse_str($queryString, $params);

    // Populate variables from URL parameters
    $ticketName = isset($params['ticketName']) ? trim($params['ticketName']) : '';
    $ticketPrice = isset($params['ticketPrice']) ? trim(str_replace('$', '', $params['ticketPrice'])) : ''; // Remove '$' from price

    // Debugging: Check the raw and cleaned ticketPrice from URL
    error_log("GET ticketPrice (raw): " . (isset($params['ticketPrice']) ? $params['ticketPrice'] : 'null'));
    error_log("GET ticketPrice (cleaned): " . $ticketPrice . " (Type: " . gettype($ticketPrice) . ")");
}

// Handle form submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passengerName = trim($_POST['name'] ?? '');
    $ticketName = trim($_POST['ticketName'] ?? '');
    $ticketPrice = trim(str_replace('$', '', $_POST['ticketPrice'] ?? ''));

    // Validate required fields
    if (empty($passengerName) || empty($ticketName) || empty($ticketPrice)) {
        $error_message = "Error: All fields are required.";
    } else {
        try {
            // Debugging: Log the ticketPrice before conversion
            error_log("POST ticketPrice (before floatval): " . $ticketPrice . " (Type: " . gettype($ticketPrice) . ")");

            // Convert ticketPrice to a float for database storage, ensuring it’s numeric
            $ticketPrice = floatval(preg_replace('/[^0-9.]/', '', $ticketPrice)); // Remove any non-numeric characters except decimal points

            // Debugging: Log the ticketPrice after conversion
            error_log("POST ticketPrice (after floatval): " . $ticketPrice . " (Type: " . gettype($ticketPrice) . ")");

            // Check if ticketPrice is valid (not 0 or empty)
            if ($ticketPrice <= 0) {
                $error_message = "Error: Invalid price value. Price must be greater than 0.";
                throw new Exception("Invalid price: " . $ticketPrice);
            }

            // Prepare and execute the SQL query to insert payment details
            $stmt = $conn->prepare("INSERT INTO payments (passenger_name, ticket_type, price) VALUES (?, ?, ?)");
            $stmt->execute([$passengerName, $ticketName, $ticketPrice]);

            // Get the last inserted payment ID (optional)
            $paymentId = $conn->lastInsertId();

            // Redirect to the ticket page with the details
            $ticketNumber = 'TK-' . str_pad($paymentId, 9, '0', STR_PAD_LEFT); // Generate ticket number
            $seatNumber = "12A"; // Replace with dynamic seat if available

            // Format ticketPrice as a string with '$' for the URL, ensuring it’s numeric
            $formattedPrice = '$' . number_format($ticketPrice, 2);

            header("Location: ticket.html?name=" . urlencode($passengerName) . 
                   "&ticketName=" . urlencode($ticketName) . 
                   "&price=" . urlencode($formattedPrice) . 
                   "&seat=" . urlencode($seatNumber) . 
                   "&ticket=" . urlencode($ticketNumber));
            exit();

        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - BookMyTickets</title>
    <link rel="stylesheet" href="payment.css">
</head>
<body>
    <div class="payment-container">
        <h2>Payment Details</h2>
        <div class="payment-form">
            <p><strong>Ticket Booking Details:</strong></p>
            <p><strong>Flight/Cabs/Hotels/Train/Buses:</strong> <span id="ticket-name"><?= htmlspecialchars($ticketName); ?></span></p>
            <p><strong>Price:</strong> <span id="ticket-price"><?= htmlspecialchars($ticketPrice ? '$' . number_format(floatval($ticketPrice), 2) : ''); ?></span></p>

            <form id="payment-form" method="POST" action="">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($passengerName); ?>" required>

                <label for="card-number">Card Number</label>
                <input type="text" id="card-number" name="card_number" required>

                <label for="expiry">Expiry Date</label>
                <input type="text" id="expiry" name="expiry" required>

                <label for="cvv">CVV</label>
                <input type="text" id="cvv" name="cvv" required>

                <!-- Hidden fields for ticket details -->
                <input type="hidden" id="ticketName" name="ticketName" value="<?= htmlspecialchars($ticketName); ?>">
                <input type="hidden" id="ticketPrice" name="ticketPrice" value="<?= htmlspecialchars($ticketPrice); ?>">

                <button type="submit">Pay Now</button>
            </form>
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script src="payment.js"></script>
</body>
</html>
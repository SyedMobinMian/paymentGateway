<?php
    // process_payment.php - Creates Razorpay Order, saves pending DB record, triggers checkout UI

    // 1. Include database connection file to access $conn
    require_once 'db.php';

    // 2. Fetch POST input from index.php safely using Null Coalescing Operator (??)
    $fullname = $_POST['fullname'] ?? 'John Doe';
    $email    = $_POST['email'] ?? 'john@example.com';
    $passport = $_POST['passport'] ?? 'A1234567';
    $visaType = $_POST['visa_type'] ?? 'Tourist Visa';
    $amount   = $_POST['amount'] ?? 50;

    // 3. Convert currency amount to smallest subunit (Paise or Cents) to prevent floating point inaccuracies
    $amountInSubunits = $amount * 100; // e.g., 50 * 100 = 5000 paise
    $currency         = "INR";         // ISO 4217 Currency Code

    // 4. Razorpay Test Mode API Keys (Replace with your keys from Razorpay Dashboard)
    $keyId     = "rzp_test_TlKNHhG3l1Fhnv";      // Public key used by frontend script
    $keySecret = "IgP6CwLfunWNmGtHOHuGhpRl";  // Private secret key used for backend API authorization

    // 5. Construct Order parameters array required by Razorpay Orders API
    $orderData = [
        'receipt'         => 'visa_order_' . time(), // Unique internal order reference string
        'amount'          => $amountInSubunits,      // Amount in subunits
        'currency'        => $currency,              // Target currency
        'payment_capture' => 1                       // 1 = Automatically capture payment once authorized
    ];

    // 6. Initialize cURL session targeting Razorpay Orders endpoint
    $ch = curl_init('https://api.razorpay.com/v1/orders');

    // 7. Configure cURL options for POST API request
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');                                // Set HTTP method to POST
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);                 // HTTP Basic Authentication
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));                 // Convert order array to JSON payload
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                // Return API response as string
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);      // Set content header type

    // 8. Execute cURL request and close session
    $response = curl_exec($ch);
    curl_close($ch);

    // 9. Decode JSON response string into an associative PHP array
    $order = json_decode($response, true);

    // 10. Check if Razorpay API returned an error
    if (isset($order['error'])) {
        die("Error creating Razorpay Order: " . htmlspecialchars($order['error']['description']));
    }

    // 11. Extract official Razorpay Order ID (e.g., 'order_L12345678')
    $orderId = $order['id'];

    // 12. Insert order details into database with initial status = 'PENDING'
    $stmt = $conn->prepare("INSERT INTO visa_applications (fullname, email, passport, visa_type, amount, currency, order_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING')");

    // Bind variables to SQL placeholders ("ssssdss" = string, string, string, string, double, string, string)
    $stmt->bind_param("ssssdss", $fullname, $email, $passport, $visaType, $amount, $currency, $orderId);

    // Execute query and check for SQL errors
    if (!$stmt->execute()) {
        die("Error saving order to database: " . $stmt->error);
    }

    // Close SQL statement handle
    $stmt->close();
?>


    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Complete Payment</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { 
                    font-family: 'Inter', sans-serif; 
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfb8 100%); 
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px; 
                }
                .card { 
                    background: white; 
                    width: 100%;
                    max-width: 450px; 
                    padding: 35px 30px; 
                    border-radius: 12px; 
                    box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
                    text-align: center;
                }
                h2 { color: #1e293b; margin-bottom: 20px; }
                .summary-item { 
                    display: flex; 
                    justify-content: space-between; 
                    padding: 12px 0; 
                    border-bottom: 1px solid #f1f5f9; 
                    font-size: 15px; 
                    color: #475569;
                }
                .summary-item strong { color: #0f172a; }
                .total-box { 
                    margin-top: 20px; 
                    margin-bottom: 25px; 
                    padding: 15px; 
                    background: #f8fafc; 
                    border-radius: 6px; 
                    font-size: 18px; 
                    font-weight: 700; 
                    color: #1e293b; 
                }
                button { 
                    background-color: #072654; 
                    color: white; 
                    border: none; 
                    padding: 14px; 
                    font-size: 16px; 
                    font-weight: 600; 
                    border-radius: 6px; 
                    cursor: pointer; 
                    width: 100%; 
                    transition: background 0.2s ease; 
                }
                button:hover { background-color: #0d3b7a; }
            </style>
        </head>
        <body>

            <div class="card">
                <h2>Confirm & Pay</h2>
                
                <!-- Application Details Summary -->
                <div class="summary-item"><span>Applicant:</span> <strong><?php echo htmlspecialchars($fullname); ?></strong></div>
                <div class="summary-item"><span>Passport:</span> <strong><?php echo htmlspecialchars($passport); ?></strong></div>
                <div class="summary-item"><span>Visa Type:</span> <strong><?php echo htmlspecialchars($visaType); ?></strong></div>
                
                <div class="total-box">
                    Total Payable: <?php echo $currency . ' ' . number_format($amount, 2); ?>
                </div>
                
                <!-- Hidden form that submits payment responses to verify.php via JS callback -->
                <form id="razorpay-form" action="verify.php" method="POST">
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id" value="<?php echo htmlspecialchars($orderId); ?>">
                    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                    
                    <button type="button" id="rzp-button1">Pay Now with Razorpay</button>
                </form>
            </div>

            <!-- Load official Razorpay Checkout JavaScript SDK -->
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
                // Configure Razorpay Checkout options object
                var options = {
                    "key": "<?php echo $keyId; ?>",                  // Razorpay Key ID
                    "amount": "<?php echo $amountInSubunits; ?>",    // Amount in subunits
                    "currency": "<?php echo $currency; ?>",          // Currency
                    "name": "Visa Application Portal",               // Business/App name shown in modal header
                    "description": "Tourist Visa Processing Fee",    // Transaction description
                    "order_id": "<?php echo $orderId; ?>",            // Pre-created Razorpay Order ID
                    "handler": function (response){
                        // This handler function runs automatically upon successful card authorization
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_order_id').value   = response.razorpay_order_id;
                        document.getElementById('razorpay_signature').value  = response.razorpay_signature;
                        
                        // Auto-submit hidden form to verify.php
                        document.getElementById('razorpay-form').submit();
                    },
                    "prefill": {
                        "name": "<?php echo htmlspecialchars($fullname); ?>",
                        "email": "<?php echo htmlspecialchars($email); ?>"
                    },
                    "theme": { "color": "#072654" } // Custom modal brand color
                };

                // Instantiate Razorpay Checkout instance
                var rzp1 = new Razorpay(options);

                    // Attach event listener to launch modal on button click
                    document.getElementById('rzp-button1').onclick = function(e){
                        rzp1.open();
                        e.preventDefault();
                    }
            </script>

        </body>
    </html>
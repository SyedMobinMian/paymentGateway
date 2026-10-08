<?php
// verify.php - Authenticates signature and updates database status

// 1. Include database connection file
require_once 'db.php';

// 2. Secret key used for calculating HMAC signature
$keySecret = "YOUR_TEST_KEY_SECRET"; // Must match key secret used in process_payment.php

// 3. Read POST parameters returned from process_payment.php JS callback
$razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
$razorpayOrderId   = $_POST['razorpay_order_id'] ?? '';
$razorpaySignature = $_POST['razorpay_signature'] ?? '';

// 4. Validate that required payment parameters are present
if (empty($razorpayPaymentId) || empty($razorpayOrderId) || empty($razorpaySignature)) {
    die("Error: Missing payment response parameters.");
}

// 5. Re-calculate HMAC-SHA256 signature using Razorpay's cryptographic formula:
//    hash_hmac('sha256', order_id + "|" + payment_id, secret_key)
$generatedSignature = hash_hmac('sha256', $razorpayOrderId . "|" . $razorpayPaymentId, $keySecret);

// 6. Compare generated signature against signature returned by Razorpay
//    hash_equals() prevents timing attacks during string comparison
$isSignatureValid = hash_equals($generatedSignature, $razorpaySignature);

if ($isSignatureValid) {
    // A. SIGNATURE MATCHED: Update database status from 'PENDING' to 'SUCCESS'
    $stmt = $conn->prepare("UPDATE visa_applications SET payment_id = ?, payment_status = 'SUCCESS' WHERE order_id = ?");
    $stmt->bind_param("ss", $razorpayPaymentId, $razorpayOrderId);
    $stmt->execute();
    $stmt->close();
    
    // B. Query database to fetch complete application details for receipt view
    $query = $conn->prepare("SELECT * FROM visa_applications WHERE order_id = ?");
    $query->bind_param("s", $razorpayOrderId);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $query->close();
} else {
    // C. SIGNATURE MISMATCH: Update status to 'FAILED' (Possible data tampering attempt)
    $stmt = $conn->prepare("UPDATE visa_applications SET payment_status = 'FAILED' WHERE order_id = ?");
    $stmt->bind_param("s", $razorpayOrderId);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
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
        .box { 
            background: white; 
            width: 100%;
            max-width: 500px; 
            padding: 40px 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
            text-align: center;
        }
        .icon { 
            width: 64px; 
            height: 64px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 32px; 
            margin: 0 auto 20px auto; 
        }
        .icon.success { background: #dcfce7; color: #16a34a; }
        .icon.error { background: #fee2e2; color: #dc2626; }
        
        h2.success { color: #15803d; margin-bottom: 10px; }
        h2.error { color: #b91c1c; margin-bottom: 10px; }
        p.desc { color: #64748b; font-size: 14px; margin-bottom: 25px; }

        .details { 
            text-align: left; 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            padding: 20px; 
            border-radius: 8px; 
            font-size: 14px; 
        }
        .details-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 8px 0; 
            border-bottom: 1px solid #e2e8f0; 
        }
        .details-row:last-child { border-bottom: none; }
        .details-row span { color: #64748b; }
        .details-row strong { color: #0f172a; word-break: break-all; }
        
        .badge { 
            background: #dcfce7; 
            color: #15803d; 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 12px; 
            font-weight: 700; 
        }
        .btn-home {
            display: inline-block;
            margin-top: 25px;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s ease;
        }
        .btn-home:hover { background-color: #1d4ed8; }
    </style>
</head>
<body>

<div class="box">
    <?php if ($isSignatureValid && isset($result)): ?>
        <!-- Success State View -->
        <div class="icon success">✓</div>
        <h2 class="success">Payment Verified!</h2>
        <p class="desc">Your visa application fee has been authenticated and saved successfully.</p>
        
        <div class="details">
            <div class="details-row"><span>Applicant Name:</span> <strong><?php echo htmlspecialchars($result['fullname']); ?></strong></div>
            <div class="details-row"><span>Email:</span> <strong><?php echo htmlspecialchars($result['email']); ?></strong></div>
            <div class="details-row"><span>Passport:</span> <strong><?php echo htmlspecialchars($result['passport']); ?></strong></div>
            <div class="details-row"><span>Visa Type:</span> <strong><?php echo htmlspecialchars($result['visa_type']); ?></strong></div>
            <div class="details-row"><span>Amount Paid:</span> <strong><?php echo htmlspecialchars($result['currency']) . ' ' . htmlspecialchars($result['amount']); ?></strong></div>
            <div class="details-row"><span>Order ID:</span> <strong><?php echo htmlspecialchars($result['order_id']); ?></strong></div>
            <div class="details-row"><span>Payment ID:</span> <strong><?php echo htmlspecialchars($result['payment_id']); ?></strong></div>
            <div class="details-row"><span>Status:</span> <strong class="badge"><?php echo htmlspecialchars($result['payment_status']); ?></strong></div>
        </div>

        <a href="index.php" class="btn-home">Submit Another Application</a>

    <?php else: ?>
        <!-- Error State View -->
        <div class="icon error">✗</div>
        <h2 class="error">Verification Failed</h2>
        <p class="desc">The payment signature was invalid or tampered with. Status updated to FAILED.</p>
        <a href="index.php" class="btn-home">Try Again</a>
    <?php endif; ?>
</div>

</body>
</html>
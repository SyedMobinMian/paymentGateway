<?php
// success.php
$payment_id = $_GET['payment_id'] ?? 'N/A';
$order_id   = $_GET['order_id'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; background: #f4f7f6; }
        .box { background: white; max-width: 450px; margin: auto; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success-icon { color: #28a745; font-size: 48px; }
    </style>
</head>
<body>

<div class="box">
    <div class="success-icon">✓</div>
    <h2>Visa Application Submitted!</h2>
    <p>Your payment was successfully processed in Test Mode.</p>
    <p><strong>Razorpay Payment ID:</strong><br><?php echo htmlspecialchars($payment_id); ?></p>
    <p><strong>Razorpay Order ID:</strong><br><?php echo htmlspecialchars($order_id); ?></p>
</div>

</body>
</html>
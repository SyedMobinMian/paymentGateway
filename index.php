<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Visa Application Form</title>
    <!-- Google Fonts for improved typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS RESET & BASE STYLES */
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

        /* CARD CONTAINER WITH DROP SHADOW AND SMOOTH TRANSITION */
        .form-container { 
            width: 100%;
            max-width: 480px; 
            background: #ffffff; 
            padding: 35px 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); 
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .form-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        /* FORM HEADER STYLING */
        h2 { text-align: center; color: #1e293b; margin-bottom: 8px; font-weight: 700; }
        .subtitle { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 25px; }

        /* INPUT FIELD GROUPING */
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #334155; }
        
        /* INPUT & SELECT ELEMENTS STYLING WITH FOCUS STATES */
        input[type="text"], input[type="email"], select { 
            width: 100%; 
            padding: 12px 14px; 
            border: 1.5px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 15px;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease; 
        }
        input[type="text"]:focus, input[type="email"]:focus, select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        /* FEE HIGHLIGHT BOX */
        .fee-box { 
            background: #eff6ff; 
            border: 1px solid #bfdbfe; 
            padding: 14px; 
            border-radius: 6px; 
            margin: 22px 0; 
            text-align: center; 
            font-size: 15px;
            font-weight: 600; 
            color: #1e40af; 
        }

        /* SUBMIT BUTTON WITH HOVER & ACTIVE ANIMATIONS */
        button { 
            width: 100%; 
            background-color: #2563eb; 
            color: #ffffff; 
            border: none; 
            padding: 14px; 
            font-size: 16px; 
            font-weight: 600;
            border-radius: 6px; 
            cursor: pointer; 
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        button:hover { background-color: #1d4ed8; }
        button:active { transform: scale(0.99); }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Visa Application</h2>
    <p class="subtitle">Complete your details to proceed to secure payment</p>
    
    <!-- Form submits data to process_payment.php via HTTP POST -->
    <form action="process_payment.php" method="POST">
        <!-- Applicant Full Name Input -->
        <div class="form-group">
            <label for="fullname">Full Name (as in Passport):</label>
            <input type="text" id="fullname" name="fullname" placeholder="John Doe" required>
        </div>

        <!-- Applicant Email Input -->
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" required>
        </div>

        <!-- Passport Number Input -->
        <div class="form-group">
            <label for="passport">Passport Number:</label>
            <input type="text" id="passport" name="passport" placeholder="A12345678" required>
        </div>

        <!-- Visa Category Selection -->
        <div class="form-group">
            <label for="visa_type">Visa Type:</label>
            <select id="visa_type" name="visa_type">
                <option value="Tourist Visa">Tourist Visa - ₹50.00 / $50.00</option>
                <option value="Business Visa">Business Visa - ₹100.00 / $100.00</option>
            </select>
        </div>

        <!-- Display Processing Fee & Pass Amount to Server via Hidden Field -->
        <div class="fee-box">
            Processing Fee: <strong>50.00</strong>
            <!-- Hidden field passes static amount value to process_payment.php -->
            <input type="hidden" name="amount" value="50">
        </div>

        <!-- Submit Button -->
        <button type="submit">Proceed to Secure Payment</button>
    </form>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartAttendance - Check In</title>
    <meta name="description" content="Mark your attendance by entering your computer number">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f0e4 0%, #c8dcc4 50%, #e8f0e4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .checkin-container {
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.5s ease forwards;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header / Logo */
        .checkin-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .checkin-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #006400;
            color: white;
            padding: 12px 24px;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(0, 100, 0, 0.3);
            margin-bottom: 12px;
        }

        .checkin-logo i {
            font-size: 1.5rem;
        }

        .checkin-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #006400;
            margin-bottom: 4px;
        }

        .checkin-header p {
            color: #4a5568;
            font-size: 0.9rem;
        }

        /* Card */
        .checkin-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 100, 0, 0.12);
            border-radius: 24px;
            padding: 32px 28px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }

        .checkin-card .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #006400, #008000);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 16px rgba(0, 100, 0, 0.25);
        }

        .checkin-card .icon-circle i {
            font-size: 2rem;
            color: white;
        }

        .checkin-card h2 {
            text-align: center;
            font-size: 1.2rem;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .checkin-card .subtitle {
            text-align: center;
            color: #718096;
            font-size: 0.85rem;
            margin-bottom: 28px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 16px;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 4px;
            font-family: 'Outfit', monospace;
            font-weight: 600;
            border: 2px solid #d4edda;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
            background: #f7faf7;
            color: #1a1a1a;
        }

        .form-group input:focus {
            border-color: #006400;
            box-shadow: 0 0 0 3px rgba(0, 100, 0, 0.15);
            background: #ffffff;
        }

        .form-group input::placeholder {
            color: #cbd5e0;
            letter-spacing: 2px;
            font-size: 1.1rem;
        }

        .form-hint {
            font-size: 0.75rem;
            color: #718096;
            margin-top: 6px;
            text-align: center;
        }

        /* Submit Button */
        .btn-checkin {
            width: 100%;
            padding: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            color: white;
            background: linear-gradient(135deg, #006400, #008000);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(0, 100, 0, 0.25);
            margin-top: 8px;
        }

        .btn-checkin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 100, 0, 0.35);
        }

        .btn-checkin:active {
            transform: translateY(0);
        }

        .btn-checkin:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-checkin i {
            font-size: 1.3rem;
        }

        /* Result Messages */
        .result-message {
            margin-top: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            text-align: center;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: none;
        }

        .result-message.show {
            display: block;
        }

        .result-message i {
            font-size: 2.5rem;
            margin-bottom: 8px;
            display: block;
        }

        .result-message h3 {
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .result-message p {
            font-size: 0.85rem;
        }

        .result-message.success {
            background: rgba(0, 100, 0, 0.08);
            border: 1px solid rgba(0, 100, 0, 0.2);
        }
        .result-message.success i, .result-message.success h3 { color: #006400; }
        .result-message.success p { color: #4a5568; }

        .result-message.error {
            background: rgba(192, 57, 43, 0.08);
            border: 1px solid rgba(192, 57, 43, 0.2);
        }
        .result-message.error i, .result-message.error h3 { color: #c0392b; }
        .result-message.error p { color: #4a5568; }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Token expired state */
        .expired-overlay {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .expired-overlay.show {
            display: block;
        }

        .expired-overlay i {
            font-size: 3rem;
            color: #c0392b;
            margin-bottom: 12px;
        }

        .expired-overlay h2 {
            color: #c0392b;
            margin-bottom: 8px;
        }

        .expired-overlay p {
            color: #4a5568;
            font-size: 0.9rem;
        }

        /* Footer */
        .checkin-footer {
            text-align: center;
            margin-top: 20px;
            color: #718096;
            font-size: 0.8rem;
        }

        .checkin-footer span {
            color: #006400;
            font-weight: 600;
        }

        /* Pulse animation for the check-in icon */
        @keyframes gentlePulse {
            0% { box-shadow: 0 4px 16px rgba(0, 100, 0, 0.25); }
            50% { box-shadow: 0 4px 24px rgba(0, 100, 0, 0.4); }
            100% { box-shadow: 0 4px 16px rgba(0, 100, 0, 0.25); }
        }

        .icon-circle {
            animation: gentlePulse 2s infinite ease-in-out;
        }
    </style>
</head>
<body>
    <div class="checkin-container">
        <!-- Header -->
        <div class="checkin-header">
            <div class="checkin-logo">
                <i class='bx bx-qr-scan'></i>
                SmartAttendance
            </div>
            <h1>Mark Your Attendance</h1>
            <p>Enter your computer number below to check in</p>
        </div>

        <!-- Card -->
        <div class="checkin-card">
            <!-- Normal form state -->
            <div id="formState">
                <div class="icon-circle">
                    <i class='bx bx-user-check'></i>
                </div>
                <h2>Student Check-In</h2>
                <p class="subtitle">Enter your 10-digit computer number to mark yourself as present</p>

                <form id="checkinForm" onsubmit="return false;">
                    <div class="form-group">
                        <label for="compNumber">Computer Number</label>
                        <input 
                            type="text" 
                            id="compNumber" 
                            placeholder="e.g. 2023555227" 
                            maxlength="10" 
                            inputmode="numeric" 
                            pattern="\d{10}" 
                            autocomplete="off"
                            autofocus
                        >
                        <p class="form-hint">Must be exactly 10 digits</p>
                    </div>

                    <button type="submit" class="btn-checkin" id="submitBtn">
                        <i class='bx bx-check-circle'></i>
                        Mark Present
                    </button>
                </form>

                <div class="result-message" id="resultMessage">
                    <i id="resultIcon"></i>
                    <h3 id="resultTitle"></h3>
                    <p id="resultText"></p>
                </div>
            </div>

            <!-- Token expired state -->
            <div class="expired-overlay" id="expiredState">
                <i class='bx bx-error-circle'></i>
                <h2>QR Code Expired</h2>
                <p>This QR code has expired. Please scan the new QR code displayed on the projector screen.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="checkin-footer">
            Powered by <span>SmartAttendance</span> &bull; University of Zambia
        </div>
    </div>

    <script>
        // Get token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        // CRITICAL CHECK: Detect if this page was loaded via localhost/127.0.0.1
        // This happens when the QR code wasn't updated with the real LAN IP.
        // On a phone, "localhost" resolves to the phone itself, not the server.
        const currentHost = window.location.hostname;
        if (currentHost === 'localhost' || currentHost === '127.0.0.1' || currentHost === '::1') {
            document.getElementById('formState').style.display = 'none';
            document.getElementById('expiredState').classList.add('show');
            document.querySelector('#expiredState i').className = 'bx bx-error-circle';
            document.querySelector('#expiredState h2').textContent = 'Configuration Error';
            document.querySelector('#expiredState p').innerHTML = 
                'The server is currently using "<strong>' + currentHost + '</strong>".<br><br>' +
                'Please ask the lecturer to refresh the dashboard on the PC to update the QR code with the real LAN IP address.<br><br>' +
                '<small style="color:#718096;">The QR code must contain the server\'s network IP (e.g. 192.168.x.x or 10.x.x.x), not localhost.</small>';
        }

        // If no token provided, show expired
        if (!token) {
            document.getElementById('formState').style.display = 'none';
            document.getElementById('expiredState').classList.add('show');
        }

        // Only allow digits in the input
        const compInput = document.getElementById('compNumber');
        compInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        // Handle form submission
        document.getElementById('checkinForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const compNumber = compInput.value.trim();
            const submitBtn = document.getElementById('submitBtn');
            const resultMsg = document.getElementById('resultMessage');

            // Validate
            if (!compNumber) {
                showResult(false, 'Missing Input', 'Please enter your computer number.');
                return;
            }

            if (!/^\d{10}$/.test(compNumber)) {
                showResult(false, 'Invalid Format', 'Computer number must be exactly 10 digits.');
                return;
            }

            // Disable button & show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Checking in...";

            try {
                // Use the same server origin that served this page
                const apiUrl = window.location.origin + '/QR/api/qr_checkin.php';
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        computer_number: compNumber,
                        token: token
                    })
                });

                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = { success: false, message: 'Server error. Please try again.' };
                }

                if (data.success) {
                    showResult(true, 'Success!', data.message);
                    compInput.value = '';
                } else {
                    showResult(false, 'Error', data.message);
                }
            } catch (error) {
                showResult(false, 'Network Error', 'Could not connect to the server. Make sure you are on the same network.');
            }

            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.innerHTML = "<i class='bx bx-check-circle'></i> Mark Present";
        });

        function showResult(isSuccess, title, message) {
            const resultMsg = document.getElementById('resultMessage');
            const resultIcon = document.getElementById('resultIcon');
            const resultTitle = document.getElementById('resultTitle');
            const resultText = document.getElementById('resultText');

            resultIcon.className = isSuccess ? 'bx bx-check-circle' : 'bx bx-error-circle';
            resultTitle.textContent = title;
            resultText.textContent = message;
            resultMsg.className = 'result-message show ' + (isSuccess ? 'success' : 'error');

            // Auto-hide after 4 seconds
            setTimeout(() => {
                resultMsg.classList.remove('show');
            }, 4000);
        }
    </script>
</body>
</html>

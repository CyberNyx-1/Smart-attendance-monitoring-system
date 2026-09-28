<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartAttendanceApp</title>
    <meta name="description" content="Dynamic QR Code Student Tracking System">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css?v=3">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="logo">
                <i class='bx bx-qr-scan'></i>
                <span>SmartAttendance</span>
            </div>
            
            <ul class="nav-links">
                <li class="active" data-view="dashboard">
                    <i class='bx bx-grid-alt'></i>
                    <span>Dashboard</span>
                </li>
                <li data-view="students">
                    <i class='bx bx-user'></i>
                    <span>Students</span>
                </li>
                <li data-view="simulator">
                    <i class='bx bx-mobile-alt'></i>
                    <span>Scan Simulator</span>
                </li>
                <li data-view="manual">
                    <i class='bx bx-keyboard'></i>
                    <span>Manual Check-in</span>
                </li>
                <li data-view="reports">
                    <i class='bx bx-bar-chart-alt-2'></i>
                    <span>Reports</span>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class='bx bx-user-circle'></i>
                    </div>
                    <div class="user-details">
                        <h4>Oliver Kambikambi</h4>
                        <p>I.T Student</p>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div>
                    <h1>Classroom Attendance Monitoring System</h1>
                    <p class="subtitle">Real-time student tracking via dynamic QR codes</p>
                </div>
                <div class="header-actions">
                    <button id="resetBtn" class="btn btn-outline">
                        <i class='bx bx-reset'></i> Reset Attendance
                    </button>
                    <div class="date-display" id="currentDate"></div>
                </div>
            </header>

            <!-- Dashboard View -->
            <div class="view active-view" id="dashboard-view">
                <div class="dashboard-grid">
                    
                    <!-- QR Code Section -->
                    <div class="glass-card qr-section">
                        <div class="card-header">
                            <h2>Dynamic QR Code</h2>
                            <span class="live-badge"><span class="dot"></span> Live</span>
                        </div>
                        <div class="qr-container">
                            <div class="qr-wrapper">
                                <div id="qrcodeCanvas"></div>
                                <div class="qr-overlay"></div>
                            </div>
                            
                            <div class="timer-container">
                                <p>Refreshing in: <strong id="refreshTimer">15</strong>s</p>
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progressFill"></div>
                                </div>
                            </div>
                            <p class="token-text">Current Token: <span id="currentTokenText" class="code-font">Loading...</span></p>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="stats-section">
                        <div class="stats-grid">
                            <div class="glass-card stat-card">
                                <div class="stat-icon"><i class='bx bx-group'></i></div>
                                <div class="stat-info">
                                    <h3>Total Students</h3>
                                    <p id="totalStudents">0</p>
                                </div>
                            </div>
                            <div class="glass-card stat-card success">
                                <div class="stat-icon"><i class='bx bx-check-circle'></i></div>
                                <div class="stat-info">
                                    <h3>Present</h3>
                                    <p id="presentCount">0</p>
                                </div>
                            </div>
                            <div class="glass-card stat-card danger">
                                <div class="stat-icon"><i class='bx bx-x-circle'></i></div>
                                <div class="stat-info">
                                    <h3>Absent</h3>
                                    <p id="absentCount">0</p>
                                </div>
                            </div>
                            <div class="glass-card stat-card info">
                                <div class="stat-icon"><i class='bx bx-pie-chart-alt-2'></i></div>
                                <div class="stat-info">
                                    <h3>Attendance Rate</h3>
                                    <p id="attendanceRate">0%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Scans -->
                        <div class="glass-card recent-scans">
                            <div class="card-header">
                                <h2>Recent Check-ins</h2>
                                <button class="btn-text" id="viewAllBtn">View All</button>
                            </div>
                            <div class="scans-list" id="recentScansList">
                                <div class="empty-state">No check-ins yet.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students View -->
            <div class="view" id="students-view">
                <div class="glass-card full-height">
                    <div class="card-header">
                        <h2 id="studentDirectoryTitle">Student Directory</h2>
                        <div class="search-box">
                            <i class='bx bx-search'></i>
                            <input type="text" id="studentSearch" placeholder="Search students...">
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Computer No.</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Simulator View -->
            <div class="view" id="simulator-view">
                <div class="simulator-container">
                    <div class="glass-card phone-simulator">
                        <div class="phone-header">
                            <div class="notch"></div>
                        </div>
                        <div class="phone-screen">
                            <h3>QR Scanner</h3>
                            <p>Simulate scanning the current QR code.</p>
                            
                            <div class="simulator-form">
                                <div class="input-group">
                                    <label for="simStudentSelect">Select Student</label>
                                    <select id="simStudentSelect">
                                        <!-- Populated dynamically -->
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label for="simTokenInput">QR Token</label>
                                    <input type="text" id="simTokenInput" readonly>
                                    <small>Auto-filled with live token</small>
                                </div>
                                <button id="simulateScanBtn" class="btn btn-primary btn-block">
                                    <i class='bx bx-scan'></i> Scan QR Code
                                </button>
                            </div>
                            
                            <div id="scanResult" class="scan-result hidden">
                                <i class='bx bx-check-circle'></i>
                                <h4>Success!</h4>
                                <p>Attendance marked successfully.</p>
                            </div>
                        </div>
                        <div class="phone-footer"></div>
                    </div>
                </div>
            </div>

            <!-- Manual Check-in View -->
            <div class="view" id="manual-view">
                <div class="glass-card centered-card" style="max-width: 520px; margin: 40px auto;">
                    <div class="card-header" style="display: block; text-align: center;">
                        <h2><i class='bx bx-keyboard' style="vertical-align: middle;"></i> Manual Check-in</h2>
                        <p class="text-muted" style="margin-top: 8px;">Enter your 10-digit computer number if QR scan has failed.</p>
                    </div>
                    <div class="manual-form" style="padding: 30px 20px;">
                        <div class="input-group">
                            <label for="computerNumberInput">Computer Number (10 digits)</label>
                            <input type="text" id="computerNumberInput" placeholder="e.g. 2023555227" maxlength="10" inputmode="numeric" pattern="\d{10}" autocomplete="off" style="font-size: 1.4rem; text-align: center; letter-spacing: 3px; padding: 14px;">
                        </div>
                        <button id="manualCheckinBtn" class="btn btn-primary btn-block" style="margin-top: 24px; padding: 14px; font-size: 1.1rem;">
                            <i class='bx bx-check'></i> Mark Present
                        </button>
                    </div>
                    <div id="manualResult" class="scan-result hidden" style="margin-top: 20px;">
                        <!-- dynamic message -->
                    </div>
                </div>
            </div>

            <!-- Reports View -->
            <div class="view" id="reports-view">
                <div class="glass-card full-height">
                    <div class="card-header">
                        <h2>Overall Attendance Statistics</h2>
                    </div>
                    <div class="reports-content" style="padding: 20px;">
                        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 30px;">
                            <div class="glass-card stat-card" style="background: rgba(0, 100, 0, 0.06);">
                                <div class="stat-icon" style="color: #006400;"><i class='bx bx-user-check'></i></div>
                                <div class="stat-info">
                                    <h3>Total Present Today</h3>
                                    <p id="reportPresent">0</p>
                                </div>
                            </div>
                            <div class="glass-card stat-card" style="background: rgba(192, 57, 43, 0.06);">
                                <div class="stat-icon" style="color: #c0392b;"><i class='bx bx-user-x'></i></div>
                                <div class="stat-info">
                                    <h3>Total Absent Today</h3>
                                    <p id="reportAbsent">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="progress-bar-container">
                            <h3>Today's Attendance Completion</h3>
                            <div class="progress-bar" style="height: 24px; border-radius: 12px; background: #d4edda; margin-top: 10px; overflow: hidden;">
                                <div id="reportProgressBar" style="height: 100%; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); width: 0%; transition: width 1s ease; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.8rem;">0%</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 40px;">
                            <h3>Missing Students List</h3>
                            <p class="text-muted" style="margin-bottom: 15px;">Students who have not checked in yet today.</p>
                            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                                <table class="students-table">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Computer No.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="missingStudentsTableBody">
                                        <!-- Populated dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- QR Code Library (qrcodejs) -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <!-- Custom JS -->
    <script src="app.js?v=4"></script>
</body>
</html>

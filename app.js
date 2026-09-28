// --- Application State ---
let TOTAL_STUDENTS = 0;
const REFRESH_INTERVAL_SEC = 15;

let state = {
    students: [],
    recentScans: [],
    currentToken: '',
    timer: REFRESH_INTERVAL_SEC
};

// --- DOM Elements ---
const views = document.querySelectorAll('.view');
const navLinks = document.querySelectorAll('.nav-links li');
const dateDisplay = document.getElementById('currentDate');

// Dashboard Elements
const totalStudentsEl = document.getElementById('totalStudents');
const presentCountEl = document.getElementById('presentCount');
const absentCountEl = document.getElementById('absentCount');
const attendanceRateEl = document.getElementById('attendanceRate');
const recentScansList = document.getElementById('recentScansList');

// QR Elements
const qrcodeCanvas = document.getElementById('qrcodeCanvas');
const refreshTimerEl = document.getElementById('refreshTimer');
const progressFill = document.getElementById('progressFill');
const currentTokenText = document.getElementById('currentTokenText');
let qrCodeInstance = null;

// Students View Elements
const studentsTableBody = document.getElementById('studentsTableBody');
const studentSearch = document.getElementById('studentSearch');

// Simulator Elements
const simStudentSelect = document.getElementById('simStudentSelect');
const simTokenInput = document.getElementById('simTokenInput');
const simulateScanBtn = document.getElementById('simulateScanBtn');
const scanResult = document.getElementById('scanResult');

// Manual Check-in Elements
const computerNumberInput = document.getElementById('computerNumberInput');
const manualCheckinBtn = document.getElementById('manualCheckinBtn');
const manualResult = document.getElementById('manualResult');

// Reports View Elements
const reportPresentEl = document.getElementById('reportPresent');
const reportAbsentEl = document.getElementById('reportAbsent');
const reportProgressBar = document.getElementById('reportProgressBar');
const missingStudentsTableBody = document.getElementById('missingStudentsTableBody');


// --- Initialization ---
async function init() {
    setupDate();
    setupNavigation();
    setupSearch();

    // Bind Buttons FIRST (before anything that might fail)
    document.getElementById('resetBtn').addEventListener('click', resetAttendance);

    var manualBtn = document.getElementById('manualCheckinBtn');
    if (manualBtn) {
        manualBtn.addEventListener('click', handleManualCheckin);
        console.log('Manual check-in button bound successfully');
    } else {
        console.error('manualCheckinBtn not found in DOM!');
    }

    // Fetch the server's real LAN IP so QR codes work on phones
    await fetchServerIP();

    // Load initial state from backend
    await fetchState();

    // Start QR Code System (wrapped in try-catch so it doesn't break other features)
    try {
        generateNewToken();
        startQRRefreshCycle();
    } catch (err) {
        console.error('QR Code initialization failed:', err);
    }
}

/**
 * Fetch the server's real LAN IP address from get_ip.php.
 * This ensures QR codes contain e.g. http://192.168.1.5/QR/checkin.php
 * instead of http://localhost/QR/checkin.php which won't work on phones.
 */
async function fetchServerIP() {
    try {
        const response = await fetch('api/get_ip.php');
        const data = await response.json();
        if (data.success && data.origin) {
            // Dynamically determine the folder path (e.g., "/QR/" or "/")
            const path = window.location.pathname;
            const lastSlash = path.lastIndexOf('/');
            const folderPath = path.substring(0, lastSlash + 1);

            CHECKIN_BASE_URL = `${data.origin}${folderPath}checkin.php`;
            console.log('QR codes will use server IP:', data.ip);
            console.log('Check-in URL base:', CHECKIN_BASE_URL);

            if (data.ip === '127.0.0.1' || data.ip === 'localhost') {
                console.warn('Server IP detected as localhost. QR codes may not work on mobile devices.');
                showIPWarning('QR codes are using <strong>localhost</strong> — phones will NOT be able to connect. ' +
                    'Connect this PC to <strong>WiFi</strong> or enable <strong>Mobile Hotspot</strong>, then refresh this page.');
            }
        } else {
            console.warn('Could not detect server IP, falling back to window.location.origin');
            showIPWarning('Could not detect server IP. QR codes may not work on phones. ' +
                'Make sure this PC is connected to a network, then refresh.');
        }
    } catch (error) {
        console.warn('Failed to fetch server IP, QR codes will use:', CHECKIN_BASE_URL);
        showIPWarning('Failed to detect server IP. QR codes may not work on phones. ' +
            'Make sure XAMPP Apache is running, then refresh.');
    }
}

/**
 * Show a warning banner at the top of the main content area.
 */
function showIPWarning(message) {
    const banner = document.createElement('div');
    banner.id = 'ipWarningBanner';
    banner.style.cssText = 'background:#c0392b;color:#fff;padding:12px 20px;border-radius:10px;margin-bottom:16px;' +
        'font-size:0.9rem;display:flex;align-items:center;gap:10px;animation:slideUp 0.3s ease;';
    banner.innerHTML = `<i class='bx bx-error-circle' style='font-size:1.4rem;flex-shrink:0;'></i><span>${message}</span>` +
        `<button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;">×</button>`;
    
    const header = document.querySelector('.top-header');
    if (header && header.parentNode) {
        header.parentNode.insertBefore(banner, header.nextSibling);
    }
}

// --- Data Management (API Calls) ---
async function fetchState() {
    try {
        const response = await fetch('api/get_state.php');
        const data = await response.json();

        if (data.students) {
            state.students = data.students;
            state.recentScans = data.recentScans || [];
            TOTAL_STUDENTS = state.students.length;

            updateUI();
            setupSimulator();
        }
    } catch (error) {
        console.error('Error fetching state:', error);
    }
}

async function markAttendance(identifier, type) {
    try {
        const response = await fetch('api/mark_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ identifier: identifier, type: type })
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', text);
            return { success: false, message: 'Server error. Please try again.' };
        }

        if (data.success === true) {
            await fetchState();
            return { success: true, message: data.message };
        } else {
            return { success: false, message: data.message || data.error || 'Failed to mark attendance' };
        }
    } catch (error) {
        console.error('Error marking attendance:', error);
        return { success: false, message: 'Network error. Please try again.' };
    }
}

async function resetAttendance() {
    if (confirm("Are you sure you want to reset today's attendance records?")) {
        try {
            const response = await fetch('api/reset_attendance.php');
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Reset JSON parse error:', text);
                alert('Server error. Please try again.');
                return;
            }

            if (data.success === true) {
                await fetchState();
                alert('Attendance reset successfully.');
            } else {
                alert('Error: ' + (data.message || 'Failed to reset attendance'));
            }
        } catch (error) {
            console.error('Error resetting attendance:', error);
            alert('Network error. Please try again.');
        }
    }
}

// --- Navigation ---
function setupNavigation() {
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Update active state
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            // Switch view
            const viewId = link.getAttribute('data-view');
            views.forEach(view => {
                view.classList.remove('active-view');
                if (view.id === `${viewId}-view`) {
                    view.classList.add('active-view');
                }
            });

            // Handle view specific logic
            if (viewId === 'simulator') {
                scanResult.classList.add('hidden');
                simTokenInput.value = state.currentToken;
            } else if (viewId === 'manual') {
                manualResult.classList.add('hidden');
                computerNumberInput.value = '';
            } else if (viewId === 'reports') {
                updateDashboardStats(); // Ensure reports are up to date
            }
        });
    });
}

function setupDate() {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateDisplay.textContent = new Date().toLocaleDateString('en-US', options);
}

// The QR code URL must use the server's real LAN IP address, NOT "localhost"
// or "127.0.0.1", because Android phones resolve those to THEMSELVES.
// This variable is populated at startup by fetchServerIP().
const initialPath = window.location.pathname;
const lastSlash = initialPath.lastIndexOf('/');
const initialFolder = initialPath.substring(0, lastSlash + 1);
let CHECKIN_BASE_URL = `${window.location.origin}${initialFolder}checkin.php`; // fallback

function generateNewToken() {
    const timestamp = Date.now().toString(36);
    const randomStr = Math.random().toString(36).substring(2, 8).toUpperCase();
    state.currentToken = `${timestamp}-${randomStr}`;

    currentTokenText.innerHTML = `<span>${state.currentToken}</span>`;
    if (simTokenInput) simTokenInput.value = state.currentToken;

    // Build the check-in URL with the token as a query parameter
    const checkinUrl = `${CHECKIN_BASE_URL}?token=${encodeURIComponent(state.currentToken)}`;

    // Sync the token to the server so it can be validated
    syncTokenToServer(state.currentToken);

    if (qrCodeInstance) {
        qrCodeInstance.clear();
        qrCodeInstance.makeCode(checkinUrl);
    } else {
        qrCodeInstance = new QRCode(qrcodeCanvas, {
            text: checkinUrl,
            width: 200,
            height: 200,
            colorDark: "#006400",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }
}

// Sync the current token to the server for validation
async function syncTokenToServer(token) {
    try {
        await fetch('api/set_token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token })
        });
    } catch (error) {
        console.error('Failed to sync token to server:', error);
    }
}

function startQRRefreshCycle() {
    setInterval(() => {
        state.timer--;
        refreshTimerEl.textContent = state.timer;

        const percentage = (state.timer / REFRESH_INTERVAL_SEC) * 100;
        progressFill.style.width = `${percentage}%`;

        if (state.timer <= 0) {
            state.timer = REFRESH_INTERVAL_SEC;
            generateNewToken();
        }
    }, 1000);
}

// --- UI Rendering ---
function updateUI() {
    updateDashboardStats();
    renderStudentsTable(studentSearch.value);
    renderRecentScans();
    renderMissingStudents();
}

function updateDashboardStats() {
    const presentCount = state.students.filter(s => s.present).length;
    const absentCount = TOTAL_STUDENTS - presentCount;
    const rate = TOTAL_STUDENTS > 0 ? ((presentCount / TOTAL_STUDENTS) * 100).toFixed(1) : 0;

    // Dashboard Stats
    if (totalStudentsEl) totalStudentsEl.textContent = TOTAL_STUDENTS;
    if (presentCountEl) presentCountEl.textContent = presentCount;
    if (absentCountEl) absentCountEl.textContent = absentCount;
    if (attendanceRateEl) attendanceRateEl.textContent = `${rate}%`;

    // Reports Stats
    if (reportPresentEl) reportPresentEl.textContent = presentCount;
    if (reportAbsentEl) reportAbsentEl.textContent = absentCount;
    if (reportProgressBar) {
        reportProgressBar.style.width = `${rate}%`;
        reportProgressBar.textContent = `${rate}%`;
    }
}

function renderStudentsTable(filterText = '') {
    if (!studentsTableBody) return;
    studentsTableBody.innerHTML = '';

    const filteredStudents = state.students.filter(s =>
        s.name.toLowerCase().includes(filterText.toLowerCase()) ||
        s.student_id.toLowerCase().includes(filterText.toLowerCase()) ||
        s.computer_number.toLowerCase().includes(filterText.toLowerCase())
    );

    filteredStudents.forEach(student => {
        const tr = document.createElement('tr');

        const statusBadge = student.present
            ? `<span class="status-badge present"><i class='bx bx-check'></i> Present</span>`
            : `<span class="status-badge absent"><i class='bx bx-x'></i> Absent</span>`;

        const timeStr = student.timestamp ? new Date(parseInt(student.timestamp)).toLocaleTimeString() : '-';

        tr.innerHTML = `
            <td class="code-font">${student.student_id}</td>
            <td class="code-font">${student.computer_number}</td>
            <td><strong>${student.name}</strong></td>
            <td>${statusBadge}</td>
            <td class="text-muted">${timeStr}</td>
        `;
        studentsTableBody.appendChild(tr);
    });
}

function renderMissingStudents() {
    if (!missingStudentsTableBody) return;
    missingStudentsTableBody.innerHTML = '';

    const missing = state.students.filter(s => !s.present);

    if (missing.length === 0) {
        missingStudentsTableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">All students are present!</td></tr>';
        return;
    }

    missing.forEach(student => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="code-font">${student.student_id}</td>
            <td><strong>${student.name}</strong></td>
            <td class="code-font">${student.computer_number}</td>
        `;
        missingStudentsTableBody.appendChild(tr);
    });
}

function renderRecentScans() {
    if (!recentScansList) return;

    if (state.recentScans.length === 0) {
        recentScansList.innerHTML = '<div class="empty-state">No check-ins yet.</div>';
        return;
    }

    recentScansList.innerHTML = '';
    // Show top 10 recent
    const displayScans = [...state.recentScans].slice(0, 10);

    displayScans.forEach(scan => {
        const timeStr = new Date(parseInt(scan.timestamp)).toLocaleTimeString();
        const initials = scan.name.split(' ').map(n => n[0]).join('').substring(0, 2);

        const div = document.createElement('div');
        div.className = 'scan-item';
        div.innerHTML = `
            <div class="scan-user">
                <div class="scan-avatar">${initials}</div>
                <div class="scan-details">
                    <h4>${scan.name}</h4>
                    <p>${scan.id}</p>
                </div>
            </div>
            <div class="scan-time">${timeStr}</div>
        `;
        recentScansList.appendChild(div);
    });
}

// --- Simulator & Actions ---
function setupSimulator() {
    if (!simStudentSelect) return;

    const currentVal = simStudentSelect.value;

    simStudentSelect.innerHTML = '<option value="" disabled selected>-- Select a student --</option>';
    state.students.forEach(student => {
        const option = document.createElement('option');
        option.value = student.student_id;
        option.textContent = `${student.name} (${student.student_id}) ${student.present ? '- Already Present' : ''}`;
        simStudentSelect.appendChild(option);
    });

    if (currentVal) simStudentSelect.value = currentVal;
}

if (simulateScanBtn) {
    simulateScanBtn.addEventListener('click', async () => {
        const studentId = simStudentSelect.value;
        const scannedToken = simTokenInput.value;

        if (!studentId) {
            alert('Please select a student');
            return;
        }

        if (scannedToken !== state.currentToken) {
            alert('QR Token Expired! Please scan the new code.');
            return;
        }

        // Disable button during req
        simulateScanBtn.disabled = true;
        simulateScanBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Scanning...";

        const res = await markAttendance(studentId, 'qr');

        simulateScanBtn.disabled = false;
        simulateScanBtn.innerHTML = "<i class='bx bx-scan'></i> Scan QR Code";

        if (res.success) {
            scanResult.innerHTML = `<i class='bx bx-check-circle'></i><h4>Success!</h4><p>${res.message}</p>`;
            scanResult.className = 'scan-result success';
            setTimeout(() => scanResult.classList.add('hidden'), 3000);
        } else {
            scanResult.innerHTML = `<i class='bx bx-error-circle'></i><h4>Error</h4><p>${res.message}</p>`;
            scanResult.className = 'scan-result danger';
            setTimeout(() => scanResult.classList.add('hidden'), 3000);
        }
    });
}

function setupSearch() {
    if (studentSearch) {
        studentSearch.addEventListener('input', (e) => {
            renderStudentsTable(e.target.value);
        });
    }
}

async function handleManualCheckin() {
    const compNumber = computerNumberInput.value.trim();

    // Check if empty
    if (!compNumber) {
        showManualResult(false, 'Please enter a computer number.');
        return;
    }

    // Must be exactly 10 digits
    if (!/^\d{10}$/.test(compNumber)) {
        showManualResult(false, 'Please retry. Computer number must be exactly 10 digits.');
        return;
    }

    // Disable button and show loading
    manualCheckinBtn.disabled = true;
    manualCheckinBtn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Marking...";

    // Send to PHP API
    const res = await markAttendance(compNumber, 'manual');

    // Re-enable button
    manualCheckinBtn.disabled = false;
    manualCheckinBtn.innerHTML = "<i class='bx bx-check'></i> Mark Present";

    // Show result
    showManualResult(res.success, res.message);

    // If successful, clear input and hide message after 2 seconds
    if (res.success) {
        computerNumberInput.value = '';
        setTimeout(function () {
            manualResult.classList.add('hidden');
            computerNumberInput.focus();
        }, 2000);
    }
}

function showManualResult(isSuccess, message) {
    if (isSuccess) {
        manualResult.innerHTML = "<i class='bx bx-check-circle' style='font-size:2rem;color:#2ecc71;'></i><h4 style='color:#2ecc71;'>" + message + "</h4>";
        manualResult.className = 'scan-result success';
    } else {
        manualResult.innerHTML = "<i class='bx bx-error-circle' style='font-size:2rem;color:#e74c3c;'></i><h4 style='color:#e74c3c;'>Error</h4><p>" + message + "</p>";
        manualResult.className = 'scan-result danger';
    }
    manualResult.classList.remove('hidden');
}

// Start app
window.addEventListener('DOMContentLoaded', init);

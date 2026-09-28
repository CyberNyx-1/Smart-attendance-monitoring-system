<?php
/**
 * Returns the server's real LAN IP address.
 * Used by app.js to build QR codes with the correct IP
 * instead of "localhost" / "127.0.0.1" which won't work
 * when scanned by an Android phone on the same network.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Try multiple methods to get the real LAN IP
$ip = null;

// Method 1: Use SERVER_ADDR (works on most XAMPP setups when accessed via IP)
if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' && $_SERVER['SERVER_ADDR'] !== '::1') {
    $ip = $_SERVER['SERVER_ADDR'];
}

// Method 2: If accessed via localhost, SERVER_ADDR will be 127.0.0.1.
// Fall back to detecting the real LAN IP via the OS.
if (!$ip || $ip === '127.0.0.1' || $ip === '::1') {
    // Helper function for PHP < 8.0
    if (!function_exists('str_starts_with')) {
        function str_starts_with($haystack, $needle) {
            return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
        }
    }

    // On Windows (XAMPP), parse ipconfig to find the best IP
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec('ipconfig');
        if ($output) {
            // Split ipconfig output into sections per adapter
            $sections = preg_split('/\r?\n(?=\S)/', $output);
            
            $candidates_with_gateway = [];  // IPs on adapters that have a default gateway
            $candidates_10x = [];           // 10.x.x.x IPs (university/classroom network)
            $candidates_hotspot = [];       // 192.168.137.x (mobile hotspot)
            $candidates_other = [];         // Other private IPs

            foreach ($sections as $section) {
                // Skip VirtualBox adapters
                if (stripos($section, 'VirtualBox') !== false || stripos($section, 'Ethernet 2') !== false) {
                    continue;
                }
                // Skip disconnected adapters
                if (stripos($section, 'Media disconnected') !== false) {
                    continue;
                }
                // Skip Bluetooth adapters
                if (stripos($section, 'Bluetooth') !== false) {
                    continue;
                }

                // Find IPv4 address in this section
                if (preg_match('/IPv4 Address[.\s]*:\s*(\d+\.\d+\.\d+\.\d+)/', $section, $ip_match)) {
                    $found_ip = $ip_match[1];
                    if ($found_ip === '127.0.0.1') continue;
                    
                    // Check if this adapter has a default gateway
                    $has_gateway = preg_match('/Default Gateway[.\s]*:\s*(\d+\.\d+\.\d+\.\d+)/', $section, $gw_match);

                    if (str_starts_with($found_ip, '192.168.56.')) {
                        continue; // Always skip VirtualBox host-only
                    }
                    
                    // Classify the IP
                    if (str_starts_with($found_ip, '10.')) {
                        if ($has_gateway) {
                            array_unshift($candidates_with_gateway, $found_ip);
                        }
                        $candidates_10x[] = $found_ip;
                    } elseif (str_starts_with($found_ip, '192.168.137.')) {
                        $candidates_hotspot[] = $found_ip;
                    } elseif ($has_gateway) {
                        $candidates_with_gateway[] = $found_ip;
                    } else {
                        $candidates_other[] = $found_ip;
                    }
                }
            }

            // Priority order:
            // 1. IPs with a default gateway (active internet-connected adapters)
            // 2. 10.x.x.x IPs (university/classroom network)
            // 3. Mobile hotspot IPs (students may connect to this)
            // 4. Any other private IP
            if (!empty($candidates_with_gateway)) {
                $ip = $candidates_with_gateway[0];
            } elseif (!empty($candidates_10x)) {
                $ip = $candidates_10x[0];
            } elseif (!empty($candidates_hotspot)) {
                $ip = $candidates_hotspot[0];
            } elseif (!empty($candidates_other)) {
                $ip = $candidates_other[0];
            }
        }
    } else {
        // On Linux/Mac, use hostname -I
        $output = trim(shell_exec('hostname -I 2>/dev/null') ?? '');
        $ips = explode(' ', $output);
        if (!empty($ips[0]) && $ips[0] !== '127.0.0.1') {
            $ip = $ips[0];
        }
    }
}

// Method 3: gethostbyname as last resort
if (!$ip) {
    $ip = gethostbyname(gethostname());
}

// Final fallback
if (!$ip || $ip === gethostname()) {
    $ip = '127.0.0.1'; // Can't detect — user will need to configure manually
}

echo json_encode([
    "success" => true,
    "ip" => $ip,
    "origin" => "http://" . $ip
]);
?>

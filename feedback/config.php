<?php
/**
 * GameBridge Platform - Database Configuration
 * 
 * This file contains database connection settings using PDO
 * for the GameBridge feedback system.
 * 
 * XAMPP Default Settings:
 * - Host: localhost
 * - User: root
 * - Password: (empty)
 * - Port: 3306
 */

// ============================================
// DATABASE CONFIGURATION CLASS
// ============================================

class Config {
    // Database credentials
    private static $host = 'localhost';
    private static $dbname = 'gamebridge';
    private static $user = 'root';
    private static $pass = '';
    private static $port = 3306;
    
    // PDO connection instance
    private static $connexion = null;
    
    /**
     * Get PDO database connection (Singleton pattern)
     * 
     * @return PDO Database connection object
     */
    public static function getConnexion() {
        if (self::$connexion === null) {
            try {
                $dsn = "mysql:host=" . self::$host . 
                       ";port=" . self::$port . 
                       ";dbname=" . self::$dbname . 
                       ";charset=utf8mb4";
                
                self::$connexion = new PDO(
                    $dsn, 
                    self::$user, 
                    self::$pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
            } catch (PDOException $e) {
                die("
                    <div style='font-family: Arial; padding: 20px; background: #fee; border: 1px solid #c00; border-radius: 5px; margin: 20px;'>
                        <h2 style='color: #c00;'>Database Connection Error</h2>
                        <p>Unable to connect to the database. Please check your configuration.</p>
                        <p><strong>Troubleshooting Steps:</strong></p>
                        <ul>
                            <li>Ensure XAMPP MySQL is running</li>
                            <li>Verify database name exists: <strong>" . self::$dbname . "</strong></li>
                            <li>Check credentials in config.php</li>
                            <li>Confirm MySQL port: <strong>" . self::$port . "</strong></li>
                            <li>Run the database schema SQL script to create tables</li>
                        </ul>
                        <p style='font-size: 12px; color: #666;'>Error: " . $e->getMessage() . "</p>
                    </div>
                ");
            }
        }
        
        return self::$connexion;
    }
}

// ============================================
// SESSION CONFIGURATION
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    // Session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    
    // Session lifetime (24 hours)
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    session_start();
}

// ============================================
// TIMEZONE CONFIGURATION
// ============================================

date_default_timezone_set('Africa/Tunis');

// ============================================
// ERROR REPORTING
// ============================================

// Development mode - show all errors
define('DEVELOPMENT_MODE', true);

if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error_log.txt');
}

// ============================================
// GLOBAL CONSTANTS
// ============================================

define('BASE_URL', 'http://localhost/gamebridge/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('FEEDBACK_PER_PAGE', 12);

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && 
           !empty($_SESSION['user_id']) && 
           isset($_SESSION['authenticated']);
}

/**
 * Check user role
 */
function check_role($required_role) {
    if (!is_logged_in()) {
        handle_error('Please log in to continue', 'login.php');
    }
    
    if ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'admin') {
        handle_error('Insufficient permissions');
    }
    
    return true;
}

/**
 * Handle errors with redirect
 */
function handle_error($message, $redirect = '../feedback.php') {
    $_SESSION['error'] = $message;
    header("Location: $redirect");
    exit();
}

/**
 * Handle success with redirect
 */
function handle_success($message, $redirect = '../feedback.php') {
    $_SESSION['success'] = $message;
    header("Location: $redirect");
    exit();
}

/**
 * Format time ago string
 */
function time_ago($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    if ($time_difference < 60) {
        return 'Just now';
    } elseif ($time_difference < 3600) {
        $minutes = floor($time_difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time_difference < 86400) {
        $hours = floor($time_difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time_difference < 604800) {
        $days = floor($time_difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time_ago);
    }
}

/**
 * Validate input length
 */
function validate_length($value, $min, $max, $field_name) {
    $length = strlen($value);
    if ($length < $min) {
        handle_error("$field_name is too short (minimum $min characters)");
    }
    if ($length > $max) {
        handle_error("$field_name is too long (maximum $max characters)");
    }
    return true;
}

/**
 * Send JSON response
 */
function json_response($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit();
}

// ============================================
// SECURITY HEADERS
// ============================================

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ============================================
// CONNECTION TEST
// ============================================

if (DEVELOPMENT_MODE && basename($_SERVER['PHP_SELF']) === 'config.php') {
    $db = Config::getConnexion();
    echo "
    <div style='font-family: Arial; padding: 20px; background: #efe; border: 1px solid #0c0; border-radius: 5px; margin: 20px;'>
        <h2 style='color: #0c0;'>✓ Configuration Loaded Successfully</h2>
        <p><strong>Database:</strong> gamebridge</p>
        <p><strong>Host:</strong> localhost:3306</p>
        <p><strong>Connection Type:</strong> PDO</p>
        <p><strong>Timezone:</strong> " . date_default_timezone_get() . "</p>
        <p><strong>Session Active:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "</p>
        <p><strong>CSRF Token:</strong> " . (isset($_SESSION['csrf_token']) ? 'Generated' : 'Not yet generated') . "</p>
    </div>
    ";
}
?>
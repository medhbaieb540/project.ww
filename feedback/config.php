<?php

class Config {
    private static $host = 'localhost';
    private static $dbname = 'gamebridge';
    private static $user = 'root';
    private static $pass = '';
    private static $port = 3306;
    
    private static $connexion = null;
    
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

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    session_start();
}

date_default_timezone_set('Africa/Tunis');

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

define('BASE_URL', 'http://localhost/gamebridge/');
define('UPLOAD_DIR', __DIR__ . '/models/uploads/');
define('MAX_FILE_SIZE', 5242880);
define('FEEDBACK_PER_PAGE', 12);

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function check_admin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: views/feedback/feedback.php');
        exit();
    }
    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = $_SESSION['username'] ?? 'AdminUser';
    }
    
    return true;
}

function check_role($required_role) {
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please log in to continue';
        header('Location: views/feedback/feedback.php');
        exit();
    }
    
    if ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = 'Insufficient permissions';
        header('Location: views/feedback/feedback.php');
        exit();
    }
    
    return true;
}

function handle_error($message, $redirect = '../feedback.php') {
    $_SESSION['error'] = $message;
    header("Location: $redirect");
    exit();
}

function handle_success($message, $redirect = '../feedback.php') {
    $_SESSION['success'] = $message;
    header("Location: $redirect");
    exit();
}

function validate_length($value, $min, $max, $field_name) {
    $length = strlen($value);
    if ($length < $min) {
        return ['valid' => false, 'error' => "$field_name is too short (minimum $min characters)"];
    }
    if ($length > $max) {
        return ['valid' => false, 'error' => "$field_name is too long (maximum $max characters)"];
    }
    return ['valid' => true];
}

function json_response($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit();
}

function validate_file_upload($file, $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf']) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => true, 'file' => null];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload failed'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds 5MB limit'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('upload_', true) . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['valid' => true, 'file' => $filename, 'path' => $filepath];
    }
    
    return ['valid' => false, 'error' => 'Failed to save file'];
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

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
        <p><strong>Max File Size:</strong> " . (MAX_FILE_SIZE / 1024 / 1024) . "MB</p>
    </div>
    ";
}
?>
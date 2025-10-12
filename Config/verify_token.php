<?php
require_once __DIR__ . "/../vendor/autoload.php";
$config = require __DIR__ . "/./appsetting.php";
$secretKey = $config['jwt_secret'];
$algo = $config['jwt_algo'];

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-Authorization, x-authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getBearerToken() {
    $headers = null;
    $token = null;

    // Method 1: Check Authorization header (standard)
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        error_log("Found HTTP_AUTHORIZATION: " . $headers);
    }
    
    // Method 2: Check for Apache headers
    if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        error_log("Apache headers: " . print_r($requestHeaders, true));
        
        // Check both Authorization and authorization (case-insensitive)
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
            error_log("Found Authorization in apache_request_headers");
        } elseif (isset($requestHeaders['authorization'])) {
            $headers = trim($requestHeaders['authorization']);
            error_log("Found authorization in apache_request_headers");
        }
    }

    // Method 3: Check REDIRECT_HTTP_AUTHORIZATION
    if (!$headers && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        error_log("Found REDIRECT_HTTP_AUTHORIZATION: " . $headers);
    }

    // Method 4: Check for custom headers (some environments)
    if (!$headers && isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_X_AUTHORIZATION']);
        error_log("Found HTTP_X_AUTHORIZATION: " . $headers);
    }

    // Extract token from headers
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            $token = $matches[1];
            error_log("Successfully extracted token from Bearer header");
        } elseif (preg_match('/Basic\s(\S+)/', $headers, $matches)) {
            $token = $matches[1];
            error_log("Found Basic auth, using as token");
        } else {
            // If no Bearer prefix, try using the entire header as token
            $token = $headers;
            error_log("Using entire header as token (no Bearer prefix found)");
        }
    }

    // Method 5: Fallback to query parameter (for debugging only - remove in production)
    if (!$token && isset($_GET['token'])) {
        $token = $_GET['token'];
        error_log("Using token from query parameter");
    }

    // Method 6: Fallback to POST data (for debugging only - remove in production)
    if (!$token && isset($_POST['token'])) {
        $token = $_POST['token'];
        error_log("Using token from POST data");
    }

    error_log("Final token: " . ($token ? substr($token, 0, 20) . '...' : 'NULL'));
    return $token;
}

// Enable error logging for debugging
error_log("=== Token Verification Request Started ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request Time: " . date('Y-m-d H:i:s'));

// Get the token
$token = getBearerToken();

// Debug output
$debug_info = [
    'token_received' => $token ? true : false,
    'token_length' => $token ? strlen($token) : 0,
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
];

if (!$token) {
    error_log("ERROR: No token found in request");
    
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Authorization token not found",
        "debug" => $debug_info,
        "suggestions" => [
            "Check if Authorization header is being sent",
            "Verify token exists in localStorage",
            "Check for CORS issues"
        ]
    ]);
    exit;
}

error_log("Token verification starting for token: " . substr($token, 0, 20) . '...');

try {
    // Decode the token
    $decoded = JWT::decode($token, new Key($secretKey, $algo));
    
    error_log("Token successfully decoded");
    error_log("User ID: " . $decoded->sub);
    error_log("User Email: " . $decoded->email);
    error_log("User Role: " . $decoded->role);

    $auth_user = [
        'id' => $decoded->sub,
        'email' => $decoded->email,
        'role' => $decoded->role
    ];

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Token is valid",
        "user" => $auth_user,
        "debug" => $debug_info
    ]);
    error_log("=== Token Verification Successful ===");
    exit;

} catch (Exception $e) {
    error_log("ERROR: Token verification failed: " . $e->getMessage());
    
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token",
        "error" => $e->getMessage(),
        "debug" => $debug_info
    ]);
    exit;
}
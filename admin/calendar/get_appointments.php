<?php
// Disable all error reporting to output (log instead)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start output buffering to catch any unexpected output
ob_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Database configuration - UPDATE THESE VALUES
 $db_host = 'localhost';
 $db_user = 'username';      // Change to your DB username
 $db_pass = 'password';      // Change to your DB password
 $db_name = 'database_name'; // Change to your DB name

// Function to send JSON response and exit
function sendResponse($success, $message, $data = null) {
    // Clear any output that might have been generated
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

try {
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Set character set to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception('Error setting character set: ' . $conn->error);
    }
    
    // Query to get all appointments
    $sql = "SELECT id, start_date, end_date, service_type, customer_name, customer_address, staff_name, description FROM schedule_list ORDER BY start_date";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $appointments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
    
    // Close connection
    $conn->close();
    
    // Send success response
    sendResponse(true, 'Appointments retrieved successfully', $appointments);
    
} catch (Exception $e) {
    // Log the error
    error_log('Database error: ' . $e->getMessage());
    
    // Send error response
    sendResponse(false, $e->getMessage());
}
?>
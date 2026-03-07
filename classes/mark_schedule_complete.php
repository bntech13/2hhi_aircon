<?php
// Turn off all error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection
require_once 'config.php'; // Adjust path as needed

// Set header to return JSON
header('Content-Type: application/json');

// Start output buffering to catch any unwanted output
ob_start();

try {
    // Check if ID is provided
    if(empty($_POST['id'])) {
        ob_end_clean();
        echo json_encode(['status'=>'error', 'msg'=>'Schedule ID is required']);
        exit;
    }

    $id = intval($_POST['id']); // Ensure it's an integer
    
    // Check if status column exists, if not add it
    $check_column = $conn->query("SHOW COLUMNS FROM schedule_list LIKE 'status'");
    if($check_column->num_rows == 0) {
        $alter_query = "ALTER TABLE schedule_list ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
        if(!$conn->query($alter_query)) {
            ob_end_clean();
            echo json_encode(['status'=>'error', 'msg'=>'Failed to add status column: '.$conn->error]);
            exit;
        }
    }
    
    // Update the schedule status to completed
    $update_query = "UPDATE schedule_list SET status = 'completed' WHERE id = $id";
    if($conn->query($update_query)) {
        ob_end_clean();
        echo json_encode(['status'=>'success', 'msg'=>'Schedule marked as completed successfully']);
    } else {
        ob_end_clean();
        echo json_encode(['status'=>'error', 'msg'=>'Update failed: '.$conn->error]);
    }
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['status'=>'error', 'msg'=>'Exception: '.$e->getMessage()]);
}
?>
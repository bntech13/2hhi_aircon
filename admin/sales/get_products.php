<?php
// Include necessary files
require_once '../classes/Connection.php';
require_once '../classes/Master.php';

// Start session if needed
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Get the database connection
$conn = new Connection();
$db = $conn->connect();

// Check if the action parameter is set
if (isset($_POST['f'])) {
    $function = $_POST['f'];
    
    switch ($function) {
        case 'get_all_receiving_products':
            getAllReceivingProducts($db);
            break;
            
        case 'get_receiving_items_by_brand':
            getReceivingItemsByBrand($db);
            break;
            
        case 'get_item_by_aircon_code':
            getItemByAirconCode($db);
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid function']);
            break;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No function specified']);
}

// Close the database connection
$db->close();

/**
 * Get all products from receiving_list
 */
function getAllReceivingProducts($db) {
    try {
        $query = "SELECT * FROM receiving_list ORDER BY brand, serial_no";
        $result = $db->query($query);
        
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'count' => count($data)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Get items by brand from receiving_list
 */
function getReceivingItemsByBrand($db) {
    try {
        if (!isset($_POST['brand'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Brand parameter is required'
            ]);
            return;
        }
        
        $brand = $_POST['brand'];
        
        $stmt = $db->prepare("SELECT * FROM receiving_list WHERE brand = ?");
        $stmt->bind_param("s", $brand);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'count' => count($data)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Get item by aircon code from receiving_list
 */
function getItemByAirconCode($db) {
    try {
        if (!isset($_POST['aircon_code'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Aircon code parameter is required'
            ]);
            return;
        }
        
        $aircon_code = $_POST['aircon_code'];
        
        $stmt = $db->prepare("SELECT * FROM receiving_list WHERE serial_no = ?");
        $stmt->bind_param("s", $aircon_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = null;
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>
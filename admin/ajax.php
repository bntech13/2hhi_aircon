<?php
// Include necessary files
require_once '../classes/Connection.php';
require_once '../classes/Master.php';

// Check if the action parameter is set
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Create a database connection
    $conn = new Connection();
    $db = $conn->connect();
    
    switch($action) {
        case 'get_all_receiving_products':
            // Fetch all products from receiving_list
            $query = "SELECT * FROM receiving_list ORDER BY brand, serial_no";
            $result = $db->query($query);
            
            $data = [];
            if($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
            break;
            
        case 'get_receiving_items_by_brand':
            // Fetch items by brand
            $brand = $_POST['brand'] ?? '';
            
            $stmt = $db->prepare("SELECT * FROM receiving_list WHERE brand = ?");
            $stmt->bind_param("s", $brand);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = [];
            if($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
            break;
            
        case 'get_item_by_aircon_code':
            // Fetch item by aircon code (serial_no)
            $aircon_code = $_POST['aircon_code'] ?? '';
            
            $stmt = $db->prepare("SELECT * FROM receiving_list WHERE serial_no = ?");
            $stmt->bind_param("s", $aircon_code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $data = null;
            if($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
            break;
    }
    
    // Close the database connection
    $db->close();
}
?>
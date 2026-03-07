<?php
require_once('../config.php');
class Master extends DBConnection
{
    private $settings;
    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }
    public function __destruct()
    {
        parent::__destruct();
    }
    function capture_err()
    {
        if (!$this->conn->error) {
            return false;
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
            return json_encode($resp);
            exit;
        }
    }
    
    // Helper function to ensure delete_list table exists
    private function ensure_delete_list_table() {
        // Check if delete_list table exists, if not create it
        $check_table = $this->conn->query("SHOW TABLES LIKE 'delete_list'");
        if ($check_table->num_rows == 0) {
            // Create delete_list table with same structure as purchase_order_list
            $create_table_query = "CREATE TABLE `delete_list` LIKE `purchase_order_list`";
            if (!$this->conn->query($create_table_query)) {
                throw new Exception("Failed to create delete_list table: " . $this->conn->error);
            }
            
            // Add a deleted_at timestamp column to track when the record was moved
            $alter_query = "ALTER TABLE `delete_list` ADD COLUMN `deleted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            if (!$this->conn->query($alter_query)) {
                throw new Exception("Failed to add deleted_at column: " . $this->conn->error);
            }
        }
    }
    
    // Helper function to move a record from purchase_order_list to delete_list
    private function move_to_delete_list($id) {
        // Ensure the delete_list table exists
        $this->ensure_delete_list_table();
        
        // Get the record from purchase_order_list
        $select_query = "SELECT * FROM purchase_order_list WHERE id = ?";
        $stmt = $this->conn->prepare($select_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception("Purchase order not found");
        }
        
        $po_data = $result->fetch_assoc();
        $stmt->close();
        
        // Get all columns from delete_list
        $delete_columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM `delete_list`");
        while ($row = $result->fetch_assoc()) {
            $delete_columns[] = $row['Field'];
        }
        
        // Prepare data for insertion
        $insert_data = [];
        foreach ($po_data as $key => $value) {
            if (in_array($key, $delete_columns) && $key !== 'id') {
                $insert_data[$key] = $value;
            }
        }
        
        // Build the insert query
        $columns = array_keys($insert_data);
        $values = array_values($insert_data);
        
        $column_names = implode("`, `", $columns);
        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        
        $insert_query = "INSERT INTO `delete_list` (`$column_names`) VALUES ($placeholders)";
        
        $insert_stmt = $this->conn->prepare($insert_query);
        if (!$insert_stmt) {
            throw new Exception("Insert prepare failed: " . $this->conn->error);
        }
        
        // Create types string for bind_param
        $types = "";
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types .= "d";
            } else {
                $types .= "s";
            }
        }
        
        $insert_stmt->bind_param($types, ...$values);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to insert into delete_list: " . $insert_stmt->error);
        }
        
        $insert_stmt->close();
        
        // Delete the record from purchase_order_list
        $delete_query = "DELETE FROM purchase_order_list WHERE id = ?";
        $delete_stmt = $this->conn->prepare($delete_query);
        if (!$delete_stmt) {
            throw new Exception("Delete prepare failed: " . $this->conn->error);
        }
        
        $delete_stmt->bind_param("i", $id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception("Failed to delete from purchase_order_list: " . $delete_stmt->error);
        }
        
        $delete_stmt->close();
        
        return true;
    }

    function save_supplier()
    {
        extract($_POST);
        $data = "";
        foreach ($_POST as $k => $v) {
            if (!in_array($k, array('id'))) {
                if (!empty($data)) $data .= ",";
                $v = $this->conn->real_escape_string($v);
                $data .= " `{$k}`='{$v}' ";
            }
        }
        $check = $this->conn->query("SELECT * FROM `supplier_list` where `name` = '{$name}' " . (!empty($id) ? " and id != {$id} " : "") . " ")->num_rows;
        if ($this->capture_err()) return $this->capture_err();
        if ($check > 0) {
            $resp['status'] = 'failed';
            $resp['msg'] = "Supplier Name already exists.";
            return json_encode($resp);
            exit;
        }
        if (empty($id)) {
            $sql = "INSERT INTO `supplier_list` set {$data} ";
        } else {
            $sql = "UPDATE `supplier_list` set {$data} where id = '{$id}' ";
        }
        $save = $this->conn->query($sql);
        if ($save) {
            $resp['status'] = 'success';
            if (empty($id)) {
                $res['msg'] = "New Supplier successfully saved.";
                $id = $this->conn->insert_id;
            } else {
                $res['msg'] = "Supplier successfully updated.";
            }
            $this->settings->set_flashdata('success', $res['msg']);
        } else {
            $resp['status'] = 'failed';
            $resp['err'] = $this->conn->error . "[{$sql}]";
        }
        return json_encode($resp);
    }

    function delete_supplier()
    {
        if (!isset($_POST['id'])) {
            $resp['status'] = 'failed';
            $resp['error'] = 'ID not provided';
            return json_encode($resp);
        }
        $id = intval($_POST['id']);
        $stmt = $this->conn->prepare("DELETE FROM `supplier_list` where id = ?");
        if (!$stmt) {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
            return json_encode($resp);
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Supplier successfully deleted.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $stmt->error;
        }
        $stmt->close();
        return json_encode($resp);
    }

    function save_item()
    {
        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $table_columns = array();
            $result = $this->conn->query("SHOW COLUMNS FROM `item_list`");
            while ($row = $result->fetch_assoc()) {
                $table_columns[] = $row['Field'];
            }
            if ($id > 0) {
                $data = "";
                foreach ($_POST as $k => $v) {
                    if (!in_array($k, array('id')) && !is_array($_POST[$k]) && in_array($k, $table_columns)) {
                        if (!empty($data)) $data .= ", ";
                        $v = $this->conn->real_escape_string($v);
                        $data .= " `{$k}`='{$v}' ";
                    }
                }
                if (!$this->conn->query("UPDATE `item_list` SET {$data} WHERE id='{$id}'")) throw new Exception("Failed to update item: " . $this->conn->error);
            } else {
                $data = "";
                $values = "";
                foreach ($_POST as $k => $v) {
                    if (!in_array($k, array('id')) && !is_array($_POST[$k]) && in_array($k, $table_columns)) {
                        if (!empty($data)) $data .= ", ";
                        if (!empty($values)) $values .= ", ";
                        $v = $this->conn->real_escape_string($v);
                        $data .= " `{$k}`";
                        $values .= "'{$v}'";
                    }
                }
                if (!$this->conn->query("INSERT INTO `item_list` ({$data}) VALUES ({$values})")) throw new Exception("Failed to add item: " . $this->conn->error);
            }
            echo json_encode(['status' => 'success']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
            exit;
        }
    }

    function delete_item()
    {
        if (!isset($_POST['id'])) {
            $resp['status'] = 'failed';
            $resp['error'] = 'ID not provided';
            return json_encode($resp);
        }
        $id = intval($_POST['id']);
        $stmt = $this->conn->prepare("DELETE FROM `item_list` where id = ?");
        if (!$stmt) {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
            return json_encode($resp);
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Item successfully deleted.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $stmt->error;
        }
        $stmt->close();
        return json_encode($resp);
    }
    
    function get_or_create_item()
    {
        header('Content-Type: application/json');
        try {
            $brand = isset($_POST['brand']) ? $this->conn->real_escape_string($_POST['brand']) : '';
            $type = isset($_POST['type']) ? $this->conn->real_escape_string($_POST['type']) : '';
            $hp = isset($_POST['hp']) ? $this->conn->real_escape_string($_POST['hp']) : '';
            $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
            if (empty($brand) || empty($type) || empty($hp)) {
                throw new Exception("All item attributes (Brand, Type, HP) are required.");
            }
            $check_sql = "SELECT id FROM `item_list` WHERE `brand` = '{$brand}' AND `type` = '{$type}' AND `hp` = '{$hp}'";
            $result = $this->conn->query($check_sql);
            if ($result && $result->num_rows > 0) {
                $item = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'item_id' => $item['id']]);
            } else {
                $description = "{$brand} {$type} {$hp}";
                $insert_sql = "INSERT INTO `item_list` (`brand`, `type`, `description`, `hp`, `price`) VALUES ('{$brand}', '{$type}', '{$description}', '{$hp}', '{$price}')";
                if ($this->conn->query($insert_sql)) {
                    $new_id = $this->conn->insert_id;
                    echo json_encode(['status' => 'success', 'item_id' => $new_id]);
                } else {
                    throw new Exception("Failed to create new item in the database: " . $this->conn->error);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

function save_po(){
    // Start the database transaction to ensure data integrity
    $this->conn->begin_transaction();
    
    // Initialize variables that will be used in the response
    $po_id = null;
    $grand_total = 0;
    $item_count = 0;
    
    try {
        // Check if the required columns exist in the database
        $check_columns = $this->conn->query("SHOW COLUMNS FROM `purchase_order_list`");
        $existing_columns = [];
        while($row = $check_columns->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        
        // Extract main PO data from the POST request with default values
        $main_data = [
            'id' => $_POST['id'] ?? null,
            'po' => $_POST['po'] ?? '',
            'delivery_date' => !empty($_POST['delivery_date']) ? $_POST['delivery_date'] : date('Y-m-d'),
            'remarks' => $_POST['remarks'] ?? '',
            'status' => $_POST['status'] ?? 1,
            'sub_total' => $_POST['sub_total'] ?? 0,
            'discount_perc' => $_POST['discount_perc'] ?? 0,
            'discount' => $_POST['discount'] ?? 0,
            'tax_perc' => $_POST['tax_perc'] ?? 0,
            'tax' => $_POST['tax'] ?? 0
        ];
        
        // Calculate the grand total (sub_total - discount + tax)
        $sub_total = floatval($main_data['sub_total']);
        $discount = floatval($main_data['discount']);
        $tax = floatval($main_data['tax']);
        $grand_total = $sub_total - $discount + $tax;
        
        // Only add columns if they exist in the table
        if (in_array('supplier_id', $existing_columns)) {
            $main_data['supplier_id'] = $_POST['supplier_id'] ?? null;
        }
        if (in_array('invoice', $existing_columns)) {
            $main_data['invoice'] = $_POST['invoice'] ?? '';
        }
        if (in_array('dr', $existing_columns)) {
            $main_data['dr'] = $_POST['dr'] ?? '';
        }
        
        $resp = [];
        $po_id = $main_data['id'];
        $po = $main_data['po'];
        $new_po_id = null;
        
        // Get supplier name from supplier_list table
        $supplier_name = '';
        if (!empty($main_data['supplier_id']) && in_array('supplier_id', $existing_columns)) {
            $supplier_qry = $this->conn->query("SELECT name FROM `supplier_list` WHERE id = " . intval($main_data['supplier_id']));
            if ($supplier_qry && $supplier_qry->num_rows > 0) {
                $supplier_row = $supplier_qry->fetch_assoc();
                $supplier_name = $supplier_row['name'];
            }
        }
        
        // If it's an existing PO (update mode), delete the old items first.
        if(!empty($po_id) && !empty($po)){
            $stmt_delete = $this->conn->prepare("DELETE FROM `purchase_order_list` where po = ?");
            if (!$stmt_delete) {
                throw new Exception("Delete Prepare Failed: " . $this->conn->error);
            }
            $stmt_delete->bind_param("s", $po);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
        
        $total_purchase_value = 0;
        $first_po_item_id = null;
        
        // Loop through all submitted items and insert them as fresh records
        $item_count = count($_POST['qty'] ?? []);
        for ($i = 0; $i < $item_count; $i++) {
            // Build item data dynamically based on existing columns
            $item_data = [
                'po' => $po,
                // MODIFIED: Use item-specific delivery date if available, otherwise use main delivery date
                'delivery_date' => isset($_POST['item_date'][$i]) ? $_POST['item_date'][$i] : $main_data['delivery_date'],
                'remarks' => $main_data['remarks'],
                'status' => $main_data['status'],
                'sub_total' => $main_data['sub_total'],
                'discount_perc' => $main_data['discount_perc'],
                'discount' => $main_data['discount'],
                'tax_perc' => $main_data['tax_perc'],
                'tax' => $main_data['tax'],
                'brand' => $_POST['brand'][$i] ?? '',
                'type' => $_POST['type'][$i] ?? '',
                'hp' => $_POST['hp'][$i] ?? '',
                'quantity' => $_POST['qty'][$i] ?? 0,
                'price' => $_POST['price'][$i] ?? 0,
                'total' => $_POST['total'][$i] ?? 0,
            ];
            
            // Only add columns if they exist in the table
            if (in_array('supplier_id', $existing_columns)) {
                $item_data['supplier_id'] = $main_data['supplier_id'];
            }
            if (in_array('invoice', $existing_columns)) {
                $item_data['invoice'] = $main_data['invoice'];
            }
            if (in_array('dr', $existing_columns)) {
                $item_data['dr'] = $main_data['dr'];
            }
            
            // Add supplier name if column exists
            if (in_array('supplier', $existing_columns) && !empty($supplier_name)) {
                $item_data['supplier'] = $supplier_name;
            }
            
            // FIXED: Handle indoor_serial value for purchase_order_list
            if (in_array('indoor_serial', $existing_columns)) {
                // Check if the value exists in POST array
                if (isset($_POST['indoor_serial'][$i])) {
                    // Get the value and sanitize it
                    $indoor_serial_value = trim($_POST['indoor_serial'][$i]);
                    // If the value is empty string, set it to NULL
                    if ($indoor_serial_value === '') {
                        $indoor_serial_value = NULL;
                    }
                    // Add to item_data
                    $item_data['indoor_serial'] = $indoor_serial_value;
                } else {
                    // Set to NULL if not provided
                    $item_data['indoor_serial'] = NULL;
                }
            }
            
            // Add other optional fields only if they exist in the database
            $optional_fields = [
                'indoor' => $_POST['indoor'][$i] ?? '',
                'in_serial_no' => $_POST['in_serial_no'][$i] ?? '',
                'outdoor' => $_POST['outdoor'][$i] ?? '',
                'out_serial_no' => $_POST['out_serial_no'][$i] ?? '',
                'series' => $_POST['series'][$i] ?? '',
                'unit' => 'SETS', // Explicitly set unit to "SETS"
                'outdoor_serial' => $_POST['outdoor_serial'][$i] ?? ''
            ];
            
            foreach ($optional_fields as $field => $value) {
                if (in_array($field, $existing_columns)) {
                    $item_data[$field] = $value;
                }
            }
            
            // Calculate purchase value for this item (for reporting purposes)
            $item_purchase_value = floatval($_POST['qty'][$i] ?? 0) * floatval($_POST['price'][$i] ?? 0);
            $total_purchase_value += $item_purchase_value;
            
            // Build the SQL for insertion dynamically
            $sql = "INSERT INTO `purchase_order_list` SET ";
            $params = [];
            $types = '';
            foreach($item_data as $k => $v){
                $sql .= "`$k` = ?, ";
                $params[] = $v;
                // Use 's' for string, 'i' for integer, 'd' for double, 'b' for blob
                if ($v === NULL) {
                    $types .= 's'; // NULL will be handled as string
                } elseif (is_int($v)) {
                    $types .= 'i';
                } elseif (is_float($v)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $sql = rtrim($sql, ', ');
            
            $stmt_insert = $this->conn->prepare($sql);
            if (!$stmt_insert) {
                throw new Exception("Insert Prepare Failed: " . $this->conn->error);
            }
            $stmt_insert->bind_param($types, ...$params);
            
            if (!$stmt_insert->execute()) {
                throw new Exception("Failed to save item: " . $stmt_insert->error);
            }
            
            // Capture the ID of the inserted item
            $po_item_id = $this->conn->insert_id;
            if($i == 0){
                $new_po_id = $po_item_id;
                $first_po_item_id = $po_item_id;
            }
            
            $stmt_insert->close();
        }
        
        // If all queries were successful, commit the changes to the database
        $this->conn->commit();
        
        // Build the redirect URL to the purchase order list page
        // Try multiple possible paths to ensure we find the correct one
        $base_path = '';
        
        // Check if we're running on localhost
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            // For localhost, construct the path more directly
            $base_path = 'http://' . $_SERVER['HTTP_HOST'] . '/2hhi_aircon';
        } else {
            // For production, use a more generic approach
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $base_path = $protocol . $_SERVER['HTTP_HOST'];
        }
        
        // Try different possible paths to the purchased list page
        $possible_paths = [
            '/admin/?page=purchase_order/purchased_list',
            '/admin/?page=purchase_order/index',
            '/admin/index.php?page=purchase_order/purchased_list',
            '/admin/index.php?page=purchase_order/index',
            '/2hhi_aircon/admin/?page=purchase_order/purchased_list',
            '/2hhi_aircon/admin/?page=purchase_order/index'
        ];
        
        // Set the redirect URL to the most likely path
        $redirect_url = $base_path . $possible_paths[0];
        
        $resp['status'] = 'success';
        $resp['id'] = $new_po_id ?? $po_id; 
        $resp['total'] = $grand_total; // Add grand total to response
        $resp['msg'] = !empty($po_id) ? 
            "Purchase Order successfully updated with {$item_count} items. Total value: $" . number_format($grand_total, 2) : 
            "New Purchase Order successfully saved with {$item_count} items. Total value: $" . number_format($grand_total, 2);
        
        // Add redirect URL to the response
        $resp['redirect'] = $redirect_url;
        $resp['js_redirect'] = "window.location.href = '" . $redirect_url . "';";
        
        // Add alternative redirect URLs for debugging
        $resp['alternative_redirects'] = [];
        foreach ($possible_paths as $path) {
            $resp['alternative_redirects'][] = $base_path . $path;
        }
        
    } catch (Exception $e) {
        // If any error occurred at any step, roll back the entire transaction
        $this->conn->rollback();
        
        $resp['status'] = 'failed';
        $resp['msg'] = "An error occurred: " . $e->getMessage();
        error_log($e->getMessage()); // Log the detailed error for the developer
    }
    
    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($resp);
}

    function get_po_item()
    {
        header('Content-Type: application/json');
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            if ($id <= 0) {
                throw new Exception("Invalid item ID provided.");
            }
            $qry = $this->conn->query("SELECT * FROM `purchase_order_list` WHERE id = {$id}");
            if ($qry && $qry->num_rows > 0) {
                $data = $qry->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $data]);
            } else {
                throw new Exception("Item not found.");
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // MODIFIED: delete_po function to move record to delete_list
    public function delete_po(){
        header('Content-Type: application/json');
        try {
            // Check if ID is provided
            if(empty($_POST['id'])){
                throw new Exception('Purchase Order ID is required');
            }
            
            // Sanitize the ID to prevent SQL injection
            $id = intval($_POST['id']);
            
            // Start transaction for data integrity
            $this->conn->begin_transaction();
            
            // Move the record to delete_list
            $this->move_to_delete_list($id);
            
            // Commit the transaction
            $this->conn->commit();
            
            echo json_encode(array('status'=>'success', 'msg'=>'Purchase Order successfully moved to delete list.'));
            
        } catch (Exception $e) {
            // Roll back the transaction on error
            $this->conn->rollback();
            
            echo json_encode(array('status'=>'failed', 'msg'=>$e->getMessage()));
        }
        exit;
    }

    function update_purchase_order() {
        header('Content-Type: application/json');
        
        try {
            // Check if required parameters are present
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Missing required parameter: ID");
            }
            
            $id = (int)$_POST['id'];
            
            // Validate inputs
            if ($id <= 0) {
                throw new Exception("Invalid ID value");
            }
            
            // Check if the record exists
            $check_query = "SELECT id FROM purchase_order_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Record not found");
            }
            $check_stmt->close();
            
            // Get all columns from the table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `purchase_order_list`");
            if (!$columns_result) {
                throw new Exception("Error getting table columns: " . $this->conn->error);
            }
            
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Log the existing columns for debugging
            error_log("Existing columns: " . implode(', ', $existing_columns));
            
            // Check if delivery_date column exists
            if (!in_array('delivery_date', $existing_columns)) {
                throw new Exception("delivery_date column does not exist in the table");
            }
            
            // Check if aux column exists, if not add it
            if (!in_array('aux', $existing_columns)) {
                $alter_query = "ALTER TABLE `purchase_order_list` ADD COLUMN `aux` VARCHAR(255) DEFAULT NULL";
                if (!$this->conn->query($alter_query)) {
                    throw new Exception("Failed to add aux column: " . $this->conn->error);
                }
                // Add the new column to the existing columns list
                $existing_columns[] = 'aux';
            }
            
            // Process delivery date separately
            $delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : '';
            error_log("Raw delivery_date: " . $delivery_date);
            
            // Format delivery date
            if (!empty($delivery_date)) {
                // Try to parse the date with multiple formats
                $date = null;
                $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'Y/m/d', 'm-d-Y', 'd-m-Y'];
                
                foreach ($formats as $format) {
                    $date = DateTime::createFromFormat($format, $delivery_date);
                    if ($date !== false) {
                        break;
                    }
                }
                
                if ($date === false) {
                    // If all formats fail, try to create from any format
                    try {
                        $date = new DateTime($delivery_date);
                    } catch (Exception $e) {
                        $date = false;
                    }
                }
                
                if ($date !== false) {
                    $formatted_date = $date->format('Y-m-d');
                    error_log("Formatted delivery_date: " . $formatted_date);
                } else {
                    // If still invalid, use today's date
                    $formatted_date = date('Y-m-d');
                    error_log("Invalid date, using today: " . $formatted_date);
                }
            } else {
                // If empty, use today's date
                $formatted_date = date('Y-m-d');
                error_log("Empty date, using today: " . $formatted_date);
            }
            
            // Build the update query with delivery_date explicitly included
            $update_fields = [];
            $params = [];
            $types = '';
            
            // Always include delivery_date
            $update_fields[] = "`delivery_date` = ?";
            $params[] = $formatted_date;
            $types .= 's';
            
            // Process other POST fields
            foreach ($_POST as $key => $value) {
                // Skip the id field and delivery_date (already handled)
                if ($key === 'id' || $key === 'delivery_date') {
                    continue;
                }
                
                // Skip if not a valid column
                if (!in_array($key, $existing_columns)) {
                    continue;
                }
                
                // Handle numeric fields
                if ($key === 'price' || $key === 'sub_total' || $key === 'discount' || $key === 'tax') {
                    $value = (float)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'd';
                }
                // Handle integer fields
                elseif ($key === 'quantity' || $key === 'discount_perc' || $key === 'tax_perc') {
                    $value = (int)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'i';
                }
                // Handle string fields (including aux)
                else {
                    $value = trim($value);
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 's';
                }
            }
            
            // If no valid fields to update, return error
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            // Add the ID parameter for the WHERE clause
            $types .= 'i';
            $params[] = $id;
            
            // Build the complete SQL query
            $sql = "UPDATE `purchase_order_list` SET " . implode(', ', $update_fields) . " WHERE `id` = ?";
            
            // Log the SQL query for debugging
            error_log("SQL Query: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            // Prepare and execute the statement
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param($types, ...$params);
            
            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Database update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
            $response = [
                'status' => 'success', 
                'msg' => 'Purchase order updated successfully',
                'delivery_date' => $formatted_date
            ];
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            // Log the error for debugging
            error_log("Update Purchase Order Error: " . $e->getMessage());
        }
        
        // Always return JSON response
        echo json_encode($response);
        exit;
    }

    // MODIFIED: delete_purchase_order function to move record to delete_list
    function delete_purchase_order()
    {
        header('Content-Type: application/json');
        try {
            // Validate input
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Invalid purchase order ID provided.");
            }
            
            $id = intval($_POST['id']);
            
            if ($id <= 0) {
                throw new Exception("Invalid ID value.");
            }
            
            // Start transaction for data integrity
            $this->conn->begin_transaction();
            
            // Move the record to delete_list
            $this->move_to_delete_list($id);
            
            // Commit the transaction
            $this->conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'msg' => 'Purchase order successfully moved to delete list.'
            ]);
        } catch (Exception $e) {
            // Roll back the transaction on error
            $this->conn->rollback();
            
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }

    // Helper function to ensure deleted column exists
    private function ensure_deleted_column() {
        // Check if 'deleted' column exists, if not add it
        $check_columns = $this->conn->query("SHOW COLUMNS FROM `sales_list` LIKE 'deleted'");
        if ($check_columns->num_rows == 0) {
            $alter_query = "ALTER TABLE `sales_list` ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0";
            if (!$this->conn->query($alter_query)) {
                throw new Exception("Failed to add deleted column: " . $this->conn->error);
            }
        }
        
        // Set all existing records to deleted=0 if they don't have a value
        $update_query = "UPDATE `sales_list` SET `deleted` = 0 WHERE `deleted` IS NULL";
        $this->conn->query($update_query);
    }

function save_sale()
{
    // Set header at the beginning
    header('Content-Type: application/json');
    
    try {
        // Ensure the deleted column exists
        $this->ensure_deleted_column();
        
        // Start transaction for data integrity
        $this->conn->begin_transaction();
        
        // Generate a consistent invoice number
        $invoice_number = $this->generateInvoiceNumber();
        
        // Extract sale data from POST with validation
        $sale_date = isset($_POST['date']) ? $this->conn->real_escape_string($_POST['date']) : date('Y-m-d');
        $client_name = isset($_POST['client']) ? $this->conn->real_escape_string($_POST['client']) : '';
        $remarks = isset($_POST['remarks']) ? $this->conn->real_escape_string($_POST['remarks']) : '';
        
        // Extract discount and tax values with validation
        $discount_perc = isset($_POST['discount_perc']) ? floatval($_POST['discount_perc']) : 0;
        $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
        $tax_perc = isset($_POST['tax_perc']) ? floatval($_POST['tax_perc']) : 0;
        $tax = isset($_POST['tax']) ? floatval($_POST['tax']) : 0;
        
        // Extract transaction number - either from POST or generate new one
        $transaction_number = isset($_POST['transaction_number']) ? 
            $this->conn->real_escape_string($_POST['transaction_number']) : 
            $this->generateTransactionNumber();
        
        // Add a flag to determine if PO items should be deleted
        $finalize_sale = isset($_POST['finalize_sale']) ? 
            boolval($_POST['finalize_sale']) : false;
        
        // Log the transaction number for debugging
        error_log("Transaction number: " . $transaction_number);
        error_log("Finalize sale: " . ($finalize_sale ? 'Yes' : 'No'));
        
        // Initialize all possible POST arrays with empty arrays to avoid warnings
        // REMOVED: receiving_id from the $postArrays array
        $postArrays = [
            'qty' => [],
            'brand' => [],
            'hp' => [],
            'type' => [],
            'item_price' => [],
            'unit' => [],
            'total_amount' => [],
            'item_remarks' => [],
            'series' => [],
            'indoor' => [],
            'indoor_serial' => [],
            'outdoor' => [],
            'outdoor_serial' => [],
            'row_perc' => [],
            'po_item_id' => [] // Add this to track PO item IDs
        ];
        
        // Safely get each POST array if it exists
        foreach (array_keys($postArrays) as $key) {
            if (isset($_POST[$key]) && is_array($_POST[$key])) {
                $postArrays[$key] = $_POST[$key];
            }
        }
        
        // Check if items are provided
        $count = count($postArrays['qty']);
        
        // If no items, throw an error
        if ($count == 0) {
            throw new Exception("No items provided for the sale.");
        }
        
        // Log the number of items being processed
        error_log("save_sale: Processing {$count} items for invoice {$invoice_number}");
        
        // Get the actual columns in the sales_list table
        $columns_result = $this->conn->query("SHOW COLUMNS FROM `sales_list`");
        if (!$columns_result) {
            throw new Exception("Error getting table columns: " . $this->conn->error);
        }
        
        $existing_columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        
        // Log the existing columns for debugging
        error_log("Existing columns: " . implode(', ', $existing_columns));
        
        // Check if the required columns exist, if not add them
        $required_columns = [
            ['name' => 'discount_perc', 'type' => 'DECIMAL(10,2) DEFAULT 0'],
            ['name' => 'discount', 'type' => 'DECIMAL(10,2) DEFAULT 0'],
            ['name' => 'tax_perc', 'type' => 'DECIMAL(10,2) DEFAULT 0'],
            ['name' => 'tax', 'type' => 'DECIMAL(10,2) DEFAULT 0'],
            ['name' => 'invoice_number', 'type' => 'VARCHAR(50) DEFAULT NULL'],
            ['name' => 'transaction_number', 'type' => 'VARCHAR(50) DEFAULT NULL'],
            ['name' => 'deleted', 'type' => 'TINYINT(1) NOT NULL DEFAULT 0'],
            ['name' => 'row_perc', 'type' => 'DECIMAL(10,2) DEFAULT 0'],
            ['name' => 'sale_status', 'type' => 'VARCHAR(20) DEFAULT "pending"'] // Add sale status column
        ];
        
        foreach ($required_columns as $col) {
            if (!in_array($col['name'], $existing_columns)) {
                $alter_query = "ALTER TABLE `sales_list` ADD COLUMN `{$col['name']}` {$col['type']}";
                if (!$this->conn->query($alter_query)) {
                    throw new Exception("Failed to add column {$col['name']}: " . $this->conn->error);
                }
                $existing_columns[] = $col['name']; // Add to existing columns list
                error_log("Added column: {$col['name']}");
            }
        }
        
        // Verify transaction_number column exists
        if (!in_array('transaction_number', $existing_columns)) {
            throw new Exception("transaction_number column does not exist in sales_list table");
        }
        
        // Get the actual columns in the sale_items table
        $sale_items_columns_result = $this->conn->query("SHOW COLUMNS FROM `sale_items`");
        if (!$sale_items_columns_result) {
            throw new Exception("Error getting sale_items table columns: " . $this->conn->error);
        }
        
        $existing_sale_items_columns = [];
        while ($row = $sale_items_columns_result->fetch_assoc()) {
            $existing_sale_items_columns[] = $row['Field'];
        }
        
        // Calculate grand total
        $grand_total = 0;
        for ($i = 0; $i < $count; $i++) {
            $qty = isset($postArrays['qty'][$i]) ? max(1, intval($postArrays['qty'][$i])) : 1;
            $price = isset($postArrays['item_price'][$i]) ? max(0, floatval($postArrays['item_price'][$i])) : 0;
            $grand_total += $qty * $price;
        }
        
        // Generate a unique sale reference ID using timestamp and random component
        $sale_ref_id = 'SALE_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
        
        // Determine the sale status based on the finalize_sale flag
        $sale_status = $finalize_sale ? 'completed' : 'pending';
        
        // Process each item in the sale
        $first_item_id = 0; // To store the ID of the first item saved
        $po_item_ids_to_delete = []; // Store PO item IDs to delete after sale is saved
        
        for ($i = 0; $i < $count; $i++) {
            // Log processing of each item
            error_log("Processing item {$i} for invoice {$invoice_number}");
            
            // Initialize item with default values
            // REMOVED: receiving_id from the item initialization
            $item = [
                'quantity' => 1,
                'brand' => '',
                'hp' => '',
                'type' => '',
                'price' => 0,
                'unit' => 'SETS',
                'total' => 0,
                'remarks' => $remarks, // Use the main remarks for all items
                'series' => '',
                'indoor' => '',
                'indoor_serial' => '',
                'outdoor' => '',
                'outdoor_serial' => '',
                'deleted' => 0, // Ensure new records are not marked as deleted
                // Add discount and tax values
                'discount_perc' => $discount_perc,
                'discount' => $discount,
                'tax_perc' => $tax_perc,
                'tax' => $tax,
                // Initialize row_perc
                'row_perc' => 0,
                // Add sale status
                'sale_status' => $sale_status
            ];
            
            // Safely assign values if they exist in the arrays
            // REMOVED: receiving_id from the value assignment
            if (isset($postArrays['qty'][$i])) $item['quantity'] = max(1, intval($postArrays['qty'][$i]));
            if (isset($postArrays['brand'][$i])) $item['brand'] = $postArrays['brand'][$i];
            if (isset($postArrays['hp'][$i])) $item['hp'] = $postArrays['hp'][$i];
            if (isset($postArrays['type'][$i])) $item['type'] = $postArrays['type'][$i];
            if (isset($postArrays['item_price'][$i])) $item['price'] = max(0, floatval($postArrays['item_price'][$i]));
            if (isset($postArrays['unit'][$i])) $item['unit'] = $postArrays['unit'][$i];
            if (isset($postArrays['total_amount'][$i])) $item['total'] = floatval($postArrays['total_amount'][$i]);
            // Use item-specific remarks if available, otherwise use main remarks
            if (isset($postArrays['item_remarks'][$i])) $item['remarks'] = $postArrays['item_remarks'][$i];
            if (isset($postArrays['series'][$i])) $item['series'] = $postArrays['series'][$i];
            if (isset($postArrays['indoor'][$i])) $item['indoor'] = $postArrays['indoor'][$i];
            if (isset($postArrays['indoor_serial'][$i])) $item['indoor_serial'] = $postArrays['indoor_serial'][$i];
            if (isset($postArrays['outdoor'][$i])) $item['outdoor'] = $postArrays['outdoor'][$i];
            if (isset($postArrays['outdoor_serial'][$i])) $item['outdoor_serial'] = $postArrays['outdoor_serial'][$i];
            // Assign row_perc value
            if (isset($postArrays['row_perc'][$i])) $item['row_perc'] = floatval($postArrays['row_perc'][$i]);
            
            // NEW: Try to find the PO item ID by matching the item details
            $po_item_id = null;
            
            // First check if po_item_id is directly provided
            if (isset($postArrays['po_item_id'][$i]) && !empty($postArrays['po_item_id'][$i])) {
                $po_item_id = intval($postArrays['po_item_id'][$i]);
                error_log("Found direct PO item ID: $po_item_id");
            }
            
            // If no direct ID, try to find the PO item by matching details
            if (empty($po_item_id)) {
                $match_query = "SELECT id FROM `purchase_order_list` WHERE 
                    `brand` = ? AND 
                    `hp` = ? AND 
                    `type` = ? AND 
                    `indoor_serial` = ? AND 
                    `outdoor_serial` = ? AND 
                    (`deleted` = 0 OR `deleted` IS NULL)
                    LIMIT 1";
                
                $match_stmt = $this->conn->prepare($match_query);
                if ($match_stmt) {
                    $match_stmt->bind_param("sssss", 
                        $item['brand'], 
                        $item['hp'], 
                        $item['type'], 
                        $item['indoor_serial'], 
                        $item['outdoor_serial']
                    );
                    $match_stmt->execute();
                    $match_result = $match_stmt->get_result();
                    
                    if ($match_result->num_rows > 0) {
                        $match_row = $match_result->fetch_assoc();
                        $po_item_id = $match_row['id'];
                        error_log("Found PO item by matching details: $po_item_id");
                    }
                    $match_stmt->close();
                }
            }
            
            // MODIFIED: Always add PO item IDs to delete list regardless of finalize_sale flag
            if (!empty($po_item_id) && $po_item_id > 0) {
                $po_item_ids_to_delete[] = $po_item_id;
                error_log("Added PO item ID to delete list: $po_item_id");
            }
            
            // Calculate total if not provided
            if ($item['total'] == 0 && $item['quantity'] > 0 && $item['price'] > 0) {
                $item['total'] = $item['quantity'] * $item['price'];
            }
            
            // Extract item data with defaults and validation
            $quantity = max(1, intval($item['quantity']));
            $brand = $this->conn->real_escape_string($item['brand']);
            $hp = $this->conn->real_escape_string($item['hp']);
            $type = $this->conn->real_escape_string($item['type']);
            $price = max(0, floatval($item['price']));
            $unit = $this->conn->real_escape_string($item['unit']);
            $total = floatval($item['total']);
            $item_remarks = $this->conn->real_escape_string($item['remarks']);
            // REMOVED: receiving_id from the item data extraction
            $series = $this->conn->real_escape_string($item['series']);
            $indoor = $this->conn->real_escape_string($item['indoor']);
            $indoor_serial = $this->conn->real_escape_string($item['indoor_serial']);
            $outdoor = $this->conn->real_escape_string($item['outdoor']);
            $outdoor_serial = $this->conn->real_escape_string($item['outdoor_serial']);
            $row_perc = floatval($item['row_perc']);
            
            // Save item in sales_list table directly (no main record creation/deletion)
            $item_sales_data = [
                'sale_date' => $sale_date,
                'invoice_number' => $invoice_number,
                'client_name' => $client_name,
                'remarks' => $item_remarks, // Use the item remarks
                'total_amount' => $total,
                'quantity' => $quantity,
                'brand' => $brand,
                'hp' => $hp,
                'type' => $type,
                'price' => $price,
                'unit' => $unit,
                'series' => $series,
                'indoor' => $indoor,
                'indoor_serial' => $indoor_serial,
                'outdoor' => $outdoor,
                'outdoor_serial' => $outdoor_serial,
                // Add discount and tax values
                'discount_perc' => $discount_perc,
                'discount' => $discount,
                'tax_perc' => $tax_perc,
                'tax' => $tax,
                // Add transaction number
                'transaction_number' => $transaction_number,
                // Ensure deleted is set to 0 for new records
                'deleted' => 0,
                // Add row_perc value
                'row_perc' => $row_perc,
                // Add sale status
                'sale_status' => $sale_status
            ];
            
            // Add sale_ref_id if the column exists
            if (in_array('sale_ref_id', $existing_columns)) {
                $item_sales_data['sale_ref_id'] = $sale_ref_id;
            }
            
            // Log the data being saved
            error_log("Saving item_sales_data: " . print_r($item_sales_data, true));
            
            // Build the insert query for sales_list
            $line_item_insert_columns = [];
            $line_item_insert_values = [];
            $line_item_bind_types = '';
            $line_item_bind_params = [];
            
            // FIXED: Process each column only once
            foreach ($item_sales_data as $column_name => $value) {
                if (in_array($column_name, $existing_columns)) {
                    $line_item_insert_columns[] = "`$column_name`";
                    $line_item_insert_values[] = '?';
                    if (is_int($value)) {
                        $line_item_bind_types .= 'i';
                    } elseif (is_float($value)) {
                        $line_item_bind_types .= 'd';
                    } else {
                        $line_item_bind_types .= 's';
                    }
                    $line_item_bind_params[] = $value;
                }
            }
            
            $line_item_sql = "INSERT INTO `sales_list` (" . implode(', ', $line_item_insert_columns) . ") VALUES (" . implode(', ', $line_item_insert_values) . ")";
            
            // Log the SQL query for debugging
            error_log("SQL Query: " . $line_item_sql);
            error_log("Bind params: " . print_r($line_item_bind_params, true));
            
            $line_item_stmt = $this->conn->prepare($line_item_sql);
            if (!$line_item_stmt) {
                throw new Exception("Prepare failed for item in sales_list: " . $this->conn->error);
            }
            
            $line_item_stmt->bind_param($line_item_bind_types, ...$line_item_bind_params);
            
            if (!$line_item_stmt->execute()) {
                throw new Exception("Failed to save item in sales_list: " . $line_item_stmt->error);
            }
            
            // Get the ID of the inserted item
            $sales_item_id = $this->conn->insert_id;
            
            // Store the first item ID
            if ($i == 0) {
                $first_item_id = $sales_item_id;
            }
            
            $line_item_stmt->close();
            
            // Verify the transaction_number was saved
            $verify_query = "SELECT transaction_number FROM sales_list WHERE id = ?";
            $verify_stmt = $this->conn->prepare($verify_query);
            $verify_stmt->bind_param("i", $sales_item_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $verify_row = $verify_result->fetch_assoc();
            $verify_stmt->close();
            
            if ($verify_row && $verify_row['transaction_number'] == $transaction_number) {
                error_log("Transaction number verified for item ID $sales_item_id: " . $transaction_number);
            } else {
                error_log("ERROR: Transaction number not saved correctly for item ID $sales_item_id");
                error_log("Expected: " . $transaction_number);
                error_log("Actual: " . ($verify_row ? $verify_row['transaction_number'] : 'NULL'));
            }
            
            // Save item in sale_items table
            $item_insert_columns = [];
            $item_insert_values = [];
            $item_bind_types = '';
            $item_bind_params = [];
            
            // Add sale_id if it exists in the table
            if (in_array('sale_id', $existing_sale_items_columns)) {
                $item_insert_columns[] = "`sale_id`";
                $item_insert_values[] = '?';
                $item_bind_types .= 'i';
                $item_bind_params[] = $sales_item_id;
            }
            
            // Add sale_ref_id if it exists in the table
            if (in_array('sale_ref_id', $existing_sale_items_columns)) {
                $item_insert_columns[] = "`sale_ref_id`";
                $item_insert_values[] = '?';
                $item_bind_types .= 's';
                $item_bind_params[] = $sale_ref_id;
            }
            
            // Add other columns that might exist in sale_items
            $column_mappings = [
                'quantity' => ['value' => $quantity, 'type' => 'i'],
                'brand' => ['value' => $brand, 'type' => 's'],
                'hp' => ['value' => $hp, 'type' => 's'],
                'type' => ['value' => $type, 'type' => 's'],
                'price' => ['value' => $price, 'type' => 'd'],
                'unit' => ['value' => $unit, 'type' => 's'],
                'total' => ['value' => $total, 'type' => 'd'],
                'remarks' => ['value' => $item_remarks, 'type' => 's'],
                // REMOVED: receiving_id from the column mappings
                'series' => ['value' => $series, 'type' => 's'],
                'indoor' => ['value' => $indoor, 'type' => 's'],
                'indoor_serial' => ['value' => $indoor_serial, 'type' => 's'],
                'outdoor' => ['value' => $outdoor, 'type' => 's'],
                'outdoor_serial' => ['value' => $outdoor_serial, 'type' => 's'],
                // Add discount and tax values
                'discount_perc' => ['value' => $discount_perc, 'type' => 'd'],
                'discount' => ['value' => $discount, 'type' => 'd'],
                'tax_perc' => ['value' => $tax_perc, 'type' => 'd'],
                'tax' => ['value' => $tax, 'type' => 'd'],
                // Add invoice and transaction numbers
                'invoice_number' => ['value' => $invoice_number, 'type' => 's'],
                'transaction_number' => ['value' => $transaction_number, 'type' => 's'],
                // Add row_perc value
                'row_perc' => ['value' => $row_perc, 'type' => 'd'],
                // Add sale status
                'sale_status' => ['value' => $sale_status, 'type' => 's']
            ];
            
            foreach ($column_mappings as $column_name => $column_info) {
                if (in_array($column_name, $existing_sale_items_columns)) {
                    $item_insert_columns[] = "`$column_name`";
                    $item_insert_values[] = '?';
                    $item_bind_types .= $column_info['type'];
                    $item_bind_params[] = $column_info['value'];
                }
            }
            
            // Only proceed if we have columns to insert
            if (!empty($item_insert_columns)) {
                $item_sql = "INSERT INTO `sale_items` (" . implode(', ', $item_insert_columns) . ") VALUES (" . implode(', ', $item_insert_values) . ")";
                
                $item_stmt = $this->conn->prepare($item_sql);
                if (!$item_stmt) {
                    throw new Exception("Prepare failed for sale_items: " . $this->conn->error);
                }
                
                $item_stmt->bind_param($item_bind_types, ...$item_bind_params);
                
                if (!$item_stmt->execute()) {
                    throw new Exception("Failed to save sale item: " . $item_stmt->error);
                }
                
                $item_stmt->close();
            }
        }
        
        // MODIFIED: Always delete the PO items regardless of finalize_sale flag
        if (!empty($po_item_ids_to_delete)) {
            error_log("Deleting PO items: " . implode(', ', $po_item_ids_to_delete));
            
            // First, verify these items exist before deleting
            $placeholders = implode(',', array_fill(0, count($po_item_ids_to_delete), '?'));
            $verify_sql = "SELECT id, brand, hp, type FROM `purchase_order_list` WHERE `id` IN ($placeholders)";
            $verify_stmt = $this->conn->prepare($verify_sql);
            
            if ($verify_stmt) {
                $types = str_repeat('i', count($po_item_ids_to_delete));
                $verify_stmt->bind_param($types, ...$po_item_ids_to_delete);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                
                $verified_items = [];
                while ($row = $verify_result->fetch_assoc()) {
                    $verified_items[] = $row;
                    error_log("Verified PO item to delete: ID={$row['id']}, Brand={$row['brand']}, HP={$row['hp']}, Type={$row['type']}");
                }
                $verify_stmt->close();
                
                // Now delete the verified items
                if (!empty($verified_items)) {
                    $delete_sql = "DELETE FROM `purchase_order_list` WHERE `id` IN ($placeholders)";
                    $delete_stmt = $this->conn->prepare($delete_sql);
                    
                    if ($delete_stmt) {
                        $delete_stmt->bind_param($types, ...$po_item_ids_to_delete);
                        
                        if ($delete_stmt->execute()) {
                            $deleted_count = $delete_stmt->affected_rows;
                            error_log("Successfully deleted $deleted_count PO items from purchase_order_list");
                            $delete_stmt->close();
                        } else {
                            error_log("Failed to delete PO items: " . $delete_stmt->error);
                            $delete_stmt->close();
                            throw new Exception("Failed to delete PO items: " . $delete_stmt->error);
                        }
                    } else {
                        throw new Exception("Prepare failed for deleting PO items: " . $this->conn->error);
                    }
                }
            }
        } else {
            error_log("No PO items to delete");
        }
        
        // Commit transaction if all operations succeeded
        $this->conn->commit();
        
        // Prepare the response
        $response = [
            'status' => 'success',
            'msg' => 'Sale saved successfully and PO items removed.',
            'sale_id' => $first_item_id, // Return the ID of the first item
            'sale_ref_id' => $sale_ref_id, // Return the unique sale reference
            'invoice_number' => $invoice_number, // Return the generated invoice number
            'discount_perc' => $discount_perc, // Return the discount percentage
            'discount' => $discount,
            'tax_perc' => $tax_perc, // Return the tax percentage
            'tax' => $tax,
            'transaction_number' => $transaction_number, // Return the transaction number
            'remarks' => $remarks, // Include remarks in the response
            'items_count' => $count, // Include the count of items processed
            'po_items_removed' => count($po_item_ids_to_delete), // Include count of PO items removed
            'sale_status' => $sale_status // Include the sale status
        ];
        
        // Echo the response
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        // Roll back transaction on error
        if (method_exists($this->conn, 'rollback')) {
            $this->conn->rollback();
        }
        
        // Log the error for debugging
        error_log("Error in save_sale: " . $e->getMessage());
        
        // Prepare the error response
        $response = [
            'status' => 'failed',
            'msg' => $e->getMessage()
        ];
        
        // Echo the response
        echo json_encode($response);
        exit;
    }
}

/**
     * Generate a unique invoice number in the format SI-00001, SI-00002, etc.
     * @return string The generated invoice number
     */
    private function generateInvoiceNumber() {
        // Get the last invoice number
        $query = "SELECT invoice_number FROM `sales_list` WHERE invoice_number LIKE 'SI-%' ORDER BY id DESC LIMIT 1";
        $result = $this->conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastInvoice = $row['invoice_number'];
            
            // Extract the numeric part (remove "SI-" prefix)
            $numericPart = substr($lastInvoice, 3);
            
            // Convert to integer and increment
            $nextNumber = intval($numericPart) + 1;
            
            // Format with leading zeros to 5 digits
            return 'SI-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        } else {
            // If no previous invoices found, start with SI-00001
            return 'SI-00001';
        }
    }

    /**
     * Generate a unique transaction number
     * @return string The generated transaction number
     */
    private function generateTransactionNumber() {
        $prefix = '';
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        return $prefix . $date . '-' . $random;
    }

function get_sale_by_invoice() { 
    header('Content-Type: application/json');
    
    try {
        // Check if invoice_number parameter is provided
        if (!isset($_GET['invoice_number']) || empty($_GET['invoice_number'])) {
            throw new Exception("Invoice number is required.");
        }
        
        $invoice_number = $this->conn->real_escape_string($_GET['invoice_number']);
        
        // Get the actual columns in the sales_list table
        $columns_result = $this->conn->query("SHOW COLUMNS FROM `sales_list`");
        if (!$columns_result) {
            throw new Exception("Error getting table columns: " . $this->conn->error);
        }
        
        $existing_columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        
        // Check if 'deleted' column exists, if not add it
        if (!in_array('deleted', $existing_columns)) {
            $alter_query = "ALTER TABLE `sales_list` ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0";
            if (!$this->conn->query($alter_query)) {
                throw new Exception("Failed to add deleted column: " . $this->conn->error);
            }
            $existing_columns[] = 'deleted'; // Add to existing columns list
        }
        
        // Query to get all items for the specified invoice number, excluding deleted records
        $query = "SELECT * FROM `sales_list` WHERE `invoice_number` = ? AND `deleted` = 0";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        $stmt->bind_param("s", $invoice_number);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No records found for invoice number: " . $invoice_number);
        }
        
        $sales_items = [];
        $total_amount = 0;
        $sub_total = 0;
        $discount = 0;
        $tax = 0;
        $main_remarks = '';
        $date = '';
        $date_updated = '';
        $invoice_number_result = '';
        $transaction_number_result = '';
        $sale_date = ''; // Add this to store the sale date
        
        while ($row = $result->fetch_assoc()) {
            // Format price for display
            if (isset($row['price']) && is_numeric($row['price'])) {
                $row['formatted_price'] = number_format(floatval($row['price']), 2);
            } else {
                $row['formatted_price'] = '0.00';
            }
            
            // Format total amount for display
            if (isset($row['total_amount']) && is_numeric($row['total_amount'])) {
                $row['formatted_total'] = number_format(floatval($row['total_amount']), 2);
                $total_amount += floatval($row['total_amount']);
            } else {
                $row['formatted_total'] = '0.00';
            }
            
            // Calculate sub_total, discount, and tax if available
            if (isset($row['sub_total']) && is_numeric($row['sub_total'])) {
                $sub_total = floatval($row['sub_total']);
            }
            
            if (isset($row['discount']) && is_numeric($row['discount'])) {
                $discount = floatval($row['discount']);
            }
            
            if (isset($row['tax']) && is_numeric($row['tax'])) {
                $tax = floatval($row['tax']);
            }
            
            // Get remarks if available
            if (isset($row['remarks'])) {
                $main_remarks = $row['remarks'];
            }
            
            // Get dates if available
            if (isset($row['date']) && !empty($row['date'])) {
                $date = $row['date'];
            }
            
            if (isset($row['date_updated']) && !empty($row['date_updated'])) {
                $date_updated = $row['date_updated'];
            }
            
            // Get invoice number if available
            if (isset($row['invoice_number'])) {
                $invoice_number_result = $row['invoice_number'];
            }
            
            // Get transaction number if available
            if (isset($row['transaction_number'])) {
                $transaction_number_result = $row['transaction_number'];
            }
            
            // Get sale date if available
            if (isset($row['sale_date']) && !empty($row['sale_date'])) {
                $sale_date = $row['sale_date'];
            }
            
            // Ensure all required fields exist for display
            $required_fields = [
                'id', 'sale_date', 'client_name', 'brand', 'type', 'hp', 
                'series', 'indoor', 'indoor_serial', 'outdoor', 'outdoor_serial',
                'quantity', 'unit', 'price', 'total_amount', 'remarks'
            ];
            
            foreach ($required_fields as $field) {
                if (!isset($row[$field])) {
                    $row[$field] = '';
                }
            }
            
            // Generate item description
            $description_parts = [];
            if (!empty($row['brand'])) $description_parts[] = $row['brand'];
            if (!empty($row['hp'])) $description_parts[] = $row['hp'] . ' HP';
            if (!empty($row['type'])) $description_parts[] = $row['type'];
            if (!empty($row['series'])) $description_parts[] = $row['series'];
            $row['description'] = implode(', ', $description_parts);
            
            $sales_items[] = $row;
        }
        
        $stmt->close();
        
        // Format the total amount
        $formatted_total = number_format($total_amount, 2);
        
        // Format dates for display with robust handling
        $formatted_date_created = '';
        if (!empty($date)) {
            try {
                $dateObj = new DateTime($date);
                $formatted_date_created = $dateObj->format('M d, Y');
            } catch (Exception $e) {
                $formatted_date_created = 'Invalid date';
            }
        }
        
        $formatted_date_updated = '';
        if (!empty($date_updated)) {
            try {
                $dateObj = new DateTime($date_updated);
                $formatted_date_updated = $dateObj->format('M d, Y');
            } catch (Exception $e) {
                $formatted_date_updated = 'Invalid date';
            }
        }
        
        // Format sale date for display with robust handling
        $formatted_sale_date = '';
        if (!empty($sale_date)) {
            try {
                $dateObj = new DateTime($sale_date);
                $formatted_sale_date = $dateObj->format('M d, Y');
            } catch (Exception $e) {
                $formatted_sale_date = 'Invalid date';
            }
        }
        
        // Get the main sale details
        $main_details = [
            'invoice_number' => $invoice_number_result,
            'transaction_number' => $transaction_number_result,
            'client_name' => $sales_items[0]['client_name'] ?? '',
            'sale_date' => $sale_date, // Include raw sale date
            'formatted_sale_date' => $formatted_sale_date, // Include formatted sale date
            'date_created' => $formatted_date_created,
            'date_updated' => $formatted_date_updated,
            'total_amount' => $total_amount,
            'formatted_total' => $formatted_total,
            'sub_total' => $sub_total,
            'discount' => $discount,
            'tax' => $tax,
            'remarks' => $main_remarks,
            'item_count' => count($sales_items)
        ];
        
        echo json_encode([
            'status' => 'success',
            'main_details' => $main_details,
            'items' => $sales_items
        ]);
        
    } catch (Exception $e) {
        error_log("Error in get_sale_by_invoice: " . $e->getMessage());
        echo json_encode([
            'status' => 'failed',
            'msg' => $e->getMessage()
        ]);
    }
    exit;
}

    function update_sales() {
        header('Content-Type: application/json');
        
        try {
            // Check if required parameters are present
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Missing required parameter: ID");
            }
            
            $id = (int)$_POST['id'];
            
            // Validate inputs
            if ($id <= 0) {
                throw new Exception("Invalid ID value");
            }
            
            // Check if the record exists
            $check_query = "SELECT id FROM sales_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Record not found");
            }
            $check_stmt->close();
            
            // Get all columns from the table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `sales_list`");
            if (!$columns_result) {
                throw new Exception("Error getting table columns: " . $this->conn->error);
            }
            
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Build the update query dynamically
            $update_fields = [];
            $params = [];
            $types = '';
            
            // Process each POST field
            foreach ($_POST as $key => $value) {
                // Skip the id field and any non-column fields
                if ($key === 'id' || !in_array($key, $existing_columns)) {
                    continue;
                }
                
                // Sanitize the value based on its type
                if ($key === 'price' || $key === 'total_amount') {
                    // Numeric fields
                    $value = (float)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'd';
                } elseif ($key === 'quantity') {
                    // Integer fields
                    $value = (int)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'i';
                } else {
                    // String fields
                    $value = trim($value);
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 's';
                }
            }
            
            // If no valid fields to update, return error
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            // Add the ID parameter for the WHERE clause
            $types .= 'i';
            $params[] = $id;
            
            // Build the complete SQL query
            $sql = "UPDATE `sales_list` SET " . implode(', ', $update_fields) . " WHERE `id` = ?";
            
            // Prepare and execute the statement
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param($types, ...$params);
            
            // Execute the statement
            if ($stmt->execute()) {
                $response = [
                    'status' => 'success', 
                    'msg' => 'Sales record updated successfully'
                ];
            } else {
                throw new Exception("Database update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            // Log the error for debugging
            error_log("Update Sales Error: " . $e->getMessage());
        }
        
        // Always return JSON response
        echo json_encode($response);
        exit;
    }

function save_schedule()
{
    header('Content-Type: application/json');
    try {
        // Start transaction for data integrity
        $this->conn->begin_transaction();
        
        // Helper function to normalize input to array
        $toArray = function($val) {
            if (is_array($val)) return $val;
            if ($val === '' || $val === null) return [];
            return [$val];
        };

        // Extract inputs
        $schedule_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Get raw values - using the helper to handle arrays
        $service_types = $toArray($_POST['service_type'] ?? []);
        $start_dates = $toArray($_POST['start_date'] ?? []);
        $customer_names = $toArray($_POST['customer_name'] ?? []);
        $addresses = $toArray($_POST['address'] ?? []);
        $customer_cps = $toArray($_POST['customer_cp'] ?? []);
        $staff_names = $toArray($_POST['staff_name'] ?? []);
        $remarks_arr = $toArray($_POST['remarks'] ?? []);
        $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
        
        // Extract the new fields to support the logic
        $service_types_2 = $toArray($_POST['service_type_2'] ?? []);
        $end_dates = $toArray($_POST['end_date'] ?? []);
        
        // Validate required fields
        if (empty($service_types)) {
            throw new Exception("Service Type is required.");
        }
        if (empty($start_dates)) {
            throw new Exception("Start Date is required.");
        }

        // Check if the schedule table exists
        $table_check = $this->conn->query("SHOW TABLES LIKE 'schedule_list'");
        if ($table_check->num_rows == 0) {
            throw new Exception("Schedule table does not exist in the database.");
        }
        
        // Get existing columns
        $columns_result = $this->conn->query("SHOW COLUMNS FROM `schedule_list`");
        $existing_columns = [];
        while ($row = $columns_result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        
        // Determine the number of rows needed
        $max_rows = max(
            count($service_types), 
            count($start_dates), 
            count($customer_names), 
            count($staff_names)
        );
        
        if ($max_rows == 0) throw new Exception("No schedule data provided.");
        
        // If updating, delete old row first
        if ($schedule_id > 0) {
            $stmt = $this->conn->prepare("DELETE FROM schedule_list WHERE id = ?");
            $stmt->bind_param("i", $schedule_id);
            $stmt->execute();
            $stmt->close();
        }
        
        $inserted_ids = [];
        
        for ($i = 0; $i < $max_rows; $i++) {
            $row_service_type = $service_types[$i] ?? '';
            $row_start_date = $start_dates[$i] ?? '';
            $row_customer_name = $customer_names[$i] ?? '';
            $row_address = $addresses[$i] ?? '';
            $row_customer_cp = $customer_cps[$i] ?? '';
            $row_staff_name = $staff_names[$i] ?? '';
            $row_remarks = $remarks_arr[$i] ?? '';
            
            // Get potential existing values from inputs
            $row_service_type_2 = $service_types_2[$i] ?? '';
            $row_end_date = $end_dates[$i] ?? '';
            
            // Skip if required fields are empty in this row
            if(empty($row_service_type) || empty($row_start_date)) continue;
            
            // --- LOGIC START: Handle "Install" Service Type ---
            if (strtolower(trim($row_service_type)) == 'install') {
                // Automatically set Service Type 2 to "Cleaning"
                $row_service_type_2 = 'Cleaning';
                
                // Calculate End Date: 6 months from Start Date
                $date = date_create($row_start_date);
                if ($date) {
                    date_modify($date, '+6 months');
                    $row_end_date = date_format($date, 'Y-m-d');
                }
            }
            // --- LOGIC END ---
            
            // Prepare data array for this row
            $schedule_data = [
                'service_type' => $row_service_type,
                'start_date' => $row_start_date,
                'end_date' => $row_end_date, // Use calculated or provided end_date
                'customer_name' => $row_customer_name,
                'address' => $row_address,
                'customer_cp' => $row_customer_cp,
                'staff_name' => $row_staff_name,
                'remarks' => $row_remarks,
                'status' => $status,
                'service_type_2' => $row_service_type_2 // Use calculated or provided service_type_2
            ];
            
            // Filter data based on existing columns
            $final_data = [];
            foreach($schedule_data as $col => $val) {
                if (in_array($col, $existing_columns)) {
                    $final_data[$col] = $val;
                }
            }
            
            // Build Insert Query
            $columns = array_keys($final_data);
            $values = array_values($final_data);
            $placeholders = array_fill(0, count($values), '?');
            
            // Determine types
            $types = '';
            foreach($values as $k => $v) {
                $col_name = $columns[$k];
                if($col_name == 'status') $types .= 'i';
                else $types .= 's';
            }
            
            $sql = "INSERT INTO `schedule_list` (`".implode('`,`', $columns)."`) VALUES (".implode(',', $placeholders).")";
            $stmt = $this->conn->prepare($sql);
            if(!$stmt) throw new Exception("Prepare failed: " . $this->conn->error);
            
            $stmt->bind_param($types, ...$values);
            if(!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            
            $inserted_ids[] = $this->conn->insert_id;
            $stmt->close();
        }
        
        // Commit transaction
        $this->conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'msg' => 'Schedule saved successfully.',
            'id' => $inserted_ids[0] ?? $schedule_id,
            'inserted_count' => count($inserted_ids)
        ]);
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $this->conn->rollback();
        
        // Log the error for debugging
        error_log("Error in save_schedule: " . $e->getMessage());
        
        echo json_encode([
            'status' => 'failed',
            'msg' => $e->getMessage()
        ]);
    }
    exit;
}
    function get_all_schedules()
    {
        header('Content-Type: application/json');
        try {
            // Check if the schedule table exists
            $table_check = $this->conn->query("SHOW TABLES LIKE 'schedule_list'");
            if ($table_check->num_rows == 0) {
                throw new Exception("Schedule table does not exist in the database.");
            }
            
            // Get all columns in the schedule table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `schedule_list`");
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Build the query to get all schedules
            $query = "SELECT * FROM `schedule_list`";
            
            // Add ordering if date_created column exists
            if (in_array('date_created', $existing_columns)) {
                $query .= " ORDER BY `date_created` DESC";
            } elseif (in_array('start_date', $existing_columns)) {
                $query .= " ORDER BY `start_date` DESC";
            }
            
            $qry = $this->conn->query($query);
            
            if (!$qry) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $schedules = [];
            while ($row = $qry->fetch_assoc()) {
                // Format dates for display if they exist
                if (isset($row['start_date']) && !empty($row['start_date'])) {
                    $row['formatted_start_date'] = date('M d, Y', strtotime($row['start_date']));
                }
                
                if (isset($row['end_date']) && !empty($row['end_date'])) {
                    $row['formatted_end_date'] = date('M d, Y', strtotime($row['end_date']));
                }
                
                if (isset($row['date_created']) && !empty($row['date_created'])) {
                    $row['formatted_date_created'] = date('M d, Y h:i A', strtotime($row['date_created']));
                }
                
                // Ensure status is properly formatted
                if (isset($row['status'])) {
                    $row['status_text'] = $row['status'] == 1 ? 'Active' : 'Inactive';
                }
                
                $schedules[] = $row;
            }
            
            if (empty($schedules)) {
                echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No schedules found']);
            } else {
                echo json_encode(['status' => 'success', 'data' => $schedules]);
            }
        } catch (Exception $e) {
            error_log("Error in get_all_schedules: " . $e->getMessage());
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

 function delete_schedule(){
        header('Content-Type: application/json');
        try {
            // Check if ID is provided
            if(empty($_POST['id'])){
                throw new Exception('Schedule ID is required');
            }
            
            // Sanitize the ID to prevent SQL injection
            $id = intval($_POST['id']);
            
            // Start transaction for data integrity
            $this->conn->begin_transaction();
            
            // Check if the schedule exists first
            $check_query = "SELECT id FROM schedule_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Schedule not found");
            }
            $check_stmt->close();
            
            // Prepare the delete statement
            $delete_query = "DELETE FROM schedule_list WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_query);
            if (!$delete_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $delete_stmt->bind_param("i", $id);
            
            // Execute the delete statement
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to delete schedule: " . $delete_stmt->error);
            }
            
            // Check if any rows were actually deleted
            $affected_rows = $delete_stmt->affected_rows;
            $delete_stmt->close();
            
            if ($affected_rows === 0) {
                throw new Exception("No schedule was deleted");
            }
            
            // Commit the transaction
            $this->conn->commit();
            
            echo json_encode(array('status'=>'success', 'msg'=>'Schedule deleted successfully'));
            
        } catch (Exception $e) {
            // Roll back the transaction on error
            $this->conn->rollback();
            
            echo json_encode(array('status'=>'failed', 'msg'=>$e->getMessage()));
        }
        exit;
        }

    function get_completed_tasks()
    {
        header('Content-Type: application/json');
        try {
            // Check if the completed_tasks table exists
            $table_check = $this->conn->query("SHOW TABLES LIKE 'completed_tasks'");
            if ($table_check->num_rows == 0) {
                throw new Exception("Completed tasks table does not exist in the database.");
            }
            
            // Get all columns in the completed_tasks table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `completed_tasks`");
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Build the query to get all completed tasks
            $query = "SELECT * FROM `completed_tasks`";
            
            // Add ordering if date_created column exists
            if (in_array('date_created', $existing_columns)) {
                $query .= " ORDER BY `date_created` DESC";
            } elseif (in_array('start_date', $existing_columns)) {
                $query .= " ORDER BY `start_date` DESC";
            }
            
            $qry = $this->conn->query($query);
            
            if (!$qry) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $tasks = [];
            while ($row = $qry->fetch_assoc()) {
                // Format dates for display if they exist
                if (isset($row['start_date']) && !empty($row['start_date'])) {
                    $row['formatted_start_date'] = date('M d, Y', strtotime($row['start_date']));
                }
                
                if (isset($row['end_date']) && !empty($row['end_date'])) {
                    $row['formatted_end_date'] = date('M d, Y', strtotime($row['end_date']));
                }
                
                if (isset($row['date_created']) && !empty($row['date_created'])) {
                    $row['formatted_date_created'] = date('M d, Y h:i A', strtotime($row['date_created']));
                }
                
                // Ensure status is properly formatted
                if (isset($row['status'])) {
                    $row['status_text'] = $row['status'] == 1 ? 'Active' : 'Inactive';
                }
                
                $tasks[] = $row;
            }
            
            if (empty($tasks)) {
                echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No completed tasks found']);
            } else {
                echo json_encode(['status' => 'success', 'data' => $tasks]);
            }
        } catch (Exception $e) {
            error_log("Error in get_completed_tasks: " . $e->getMessage());
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    function mark_schedule_complete() 
    {
        header('Content-Type: application/json');
        try {
            // Check if ID is provided
            if(empty($_POST['id'])) {
                throw new Exception('Schedule ID is required');
            }
            
            $id = intval($_POST['id']);
            
            // Start transaction for data integrity
            $this->conn->begin_transaction();
            
            // Check if completed_tasks table exists, if not create it
            $check_table = $this->conn->query("SHOW TABLES LIKE 'completed_tasks'");
            if($check_table->num_rows == 0) {
                // Create completed_tasks table with same structure as schedule_list
                $create_table_query = "CREATE TABLE completed_tasks LIKE schedule_list";
                if(!$this->conn->query($create_table_query)) {
                    throw new Exception("Failed to create completed_tasks table: " . $this->conn->error);
                }
            }
            
            // Get all columns from schedule_list
            $schedule_columns = [];
            $result = $this->conn->query("SHOW COLUMNS FROM `schedule_list`");
            while ($row = $result->fetch_assoc()) {
                $schedule_columns[] = $row['Field'];
            }
            
            // Get all columns from completed_tasks
            $completed_columns = [];
            $result = $this->conn->query("SHOW COLUMNS FROM `completed_tasks`");
            while ($row = $result->fetch_assoc()) {
                $completed_columns[] = $row['Field'];
            }
            
            // Add any missing columns from schedule_list to completed_tasks
            foreach ($schedule_columns as $column) {
                if (!in_array($column, $completed_columns)) {
                    // Get column definition from schedule_list
                    $def_result = $this->conn->query("SHOW CREATE TABLE `schedule_list`");
                    $create_table = $def_result->fetch_assoc()['Create Table'];
                    
                    // Extract column definition using regex
                    preg_match("/`$column` ([^,]+),/", $create_table, $matches);
                    if (isset($matches[1])) {
                        $column_def = $matches[1];
                        $alter_query = "ALTER TABLE `completed_tasks` ADD COLUMN `$column` $column_def";
                        if (!$this->conn->query($alter_query)) {
                            throw new Exception("Failed to add column $column: " . $this->conn->error);
                        }
                    }
                }
            }
            
            // Get the schedule record from schedule_list
            $select_query = "SELECT * FROM schedule_list WHERE id = ?";
            $stmt = $this->conn->prepare($select_query);
            if(!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows == 0) {
                throw new Exception("Schedule not found");
            }
            
            $schedule_data = $result->fetch_assoc();
            $stmt->close();
            
            // Prepare data for insertion
            $completed_data = $schedule_data;
            
            // Remove the id column from the insert (it will be auto-generated)
            unset($completed_data['id']);
            
            // Build the insert query
            $columns = array_keys($completed_data);
            $values = array_values($completed_data);
            
            $column_names = implode("`, `", $columns);
            $placeholders = implode(", ", array_fill(0, count($values), "?"));
            
            $insert_query = "INSERT INTO `completed_tasks` (`$column_names`) VALUES ($placeholders)";
            
            $insert_stmt = $this->conn->prepare($insert_query);
            if(!$insert_stmt) {
                throw new Exception("Insert prepare failed: " . $this->conn->error);
            }
            
            // Create types string for bind_param
            $types = "";
            foreach($values as $value) {
                if(is_int($value)) {
                    $types .= "i";
                } elseif(is_float($value)) {
                    $types .= "d";
                } else {
                    $types .= "s";
                }
            }
            
            $insert_stmt->bind_param($types, ...$values);
            
            if(!$insert_stmt->execute()) {
                throw new Exception("Failed to insert into completed_tasks: " . $insert_stmt->error);
            }
            
            $insert_stmt->close();
            
            // Delete the record from schedule_list
            $delete_query = "DELETE FROM schedule_list WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_query);
            if(!$delete_stmt) {
                throw new Exception("Delete prepare failed: " . $this->conn->error);
            }
            
            $delete_stmt->bind_param("i", $id);
            
            if(!$delete_stmt->execute()) {
                throw new Exception("Failed to delete from schedule_list: " . $delete_stmt->error);
            }
            
            $delete_stmt->close();
            
            // Commit the transaction
            $this->conn->commit();
            
            // Build the redirect URL to the completed tasks page
            // Using a more reliable approach to get the base URL
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $base_url = $protocol . $host;
            
            // Get the current directory path
            $current_path = dirname($_SERVER['PHP_SELF']);
            
            // Remove the 'classes' directory from the path
            $path_parts = explode('/', trim($current_path, '/'));
            if (end($path_parts) === 'classes') {
                array_pop($path_parts);
            }
            
            $path = '/' . implode('/', $path_parts);
            
            // Build the complete URL
            $redirect_url = $base_url . $path . '/admin/?page=calendar/completed_task';
            
            echo json_encode([
                'status'=>'success', 
                'msg'=>'Schedule moved to completed tasks successfully',
                'redirect' => $redirect_url,
                'js_redirect' => "window.location.href = '" . $redirect_url . "';"
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            
            echo json_encode(['status'=>'error', 'msg'=>'Exception: '.$e->getMessage()]);
        }
        exit;
    }

    function update_schedule() {
        header('Content-Type: application/json');
        
        try {
            // Check if required parameters are present
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Missing required parameter: ID");
            }
            
            $id = (int)$_POST['id'];
            
            // Validate inputs
            if ($id <= 0) {
                throw new Exception("Invalid ID value");
            }
            
            // Check if the record exists
            $check_query = "SELECT id FROM schedule_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
                        $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Schedule not found");
            }
            $check_stmt->close();
            
            // Get all columns from the table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `schedule_list`");
            if (!$columns_result) {
                throw new Exception("Error getting table columns: " . $this->conn->error);
            }
            
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Build the update query dynamically
            $update_fields = [];
            $params = [];
            $types = '';
            
            // Process each POST field
            foreach ($_POST as $key => $value) {
                // Skip the id field and any non-column fields
                if ($key === 'id' || !in_array($key, $existing_columns)) {
                    continue;
                }
                
                // Sanitize the value based on its type
                if ($key === 'status') {
                    // Integer fields
                    $value = (int)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'i';
                } else {
                    // String fields
                    $value = trim($value);
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 's';
                }
            }
            
            // If no valid fields to update, return error
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            // Add the ID parameter for the WHERE clause
            $types .= 'i';
            $params[] = $id;
            
            // Build the complete SQL query
            $sql = "UPDATE `schedule_list` SET " . implode(', ', $update_fields) . " WHERE `id` = ?";
            
            // Prepare and execute the statement
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param($types, ...$params);
            
            // Execute the statement
            if ($stmt->execute()) {
                $response = [
                    'status' => 'success', 
                    'msg' => 'Schedule updated successfully'
                ];
            } else {
                throw new Exception("Database update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            // Log the error for debugging
            error_log("Update Schedule Error: " . $e->getMessage());
        }
        
        // Always return JSON response
        echo json_encode($response);
        exit;
    }

    function get_received_items_count() {
        header('Content-Type: application/json');
        try {
            // Check if the purchase_order_list table exists
            $table_check = $this->conn->query("SHOW TABLES LIKE 'purchase_order_list'");
            if ($table_check->num_rows == 0) {
                echo json_encode(['status' => 'success', 'count' => 0]);
                exit;
            }
            
            // Query to count items
            $query = "SELECT COUNT(*) as count FROM `purchase_order_list` WHERE (`deleted` = 0 OR `deleted` IS NULL)";
            $result = $this->conn->query($query);
            
            if ($result) {
                $row = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'count' => $row['count']]);
            } else {
                echo json_encode(['status' => 'failed', 'msg' => $this->conn->error]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    function update_po_item() {
        header('Content-Type: application/json');
        try {
            // Check if required parameters are present
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Missing required parameter: ID");
            }
            
            $id = (int)$_POST['id'];
            
            // Validate inputs
            if ($id <= 0) {
                throw new Exception("Invalid ID value");
            }
            
            // Check if the record exists
            $check_query = "SELECT id FROM purchase_order_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("PO item not found");
            }
            $check_stmt->close();
            
            // Get all columns from the table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `purchase_order_list`");
            if (!$columns_result) {
                throw new Exception("Error getting table columns: " . $this->conn->error);
            }
            
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Build the update query dynamically
            $update_fields = [];
            $params = [];
            $types = '';
            
            // Process each POST field
            foreach ($_POST as $key => $value) {
                // Skip the id field and any non-column fields
                if ($key === 'id' || !in_array($key, $existing_columns)) {
                    continue;
                }
                
                // Sanitize the value based on its type
                if ($key === 'price' || $key === 'total' || $key === 'sub_total' || $key === 'discount' || $key === 'tax') {
                    // Numeric fields
                    $value = (float)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'd';
                } elseif ($key === 'quantity' || $key === 'discount_perc' || $key === 'tax_perc') {
                    // Integer fields
                    $value = (int)$value;
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 'i';
                } else {
                    // String fields
                    $value = trim($value);
                    $update_fields[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= 's';
                }
            }
            
            // If no valid fields to update, return error
            if (empty($update_fields)) {
                throw new Exception("No valid fields to update");
            }
            
            // Add the ID parameter for the WHERE clause
            $types .= 'i';
            $params[] = $id;
            
            // Build the complete SQL query
            $sql = "UPDATE `purchase_order_list` SET " . implode(', ', $update_fields) . " WHERE `id` = ?";
            
            // Prepare and execute the statement
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $this->conn->error);
            }
            
            // Bind parameters
            $stmt->bind_param($types, ...$params);
            
            // Execute the statement
            if ($stmt->execute()) {
                $response = [
                    'status' => 'success', 
                    'msg' => 'PO item updated successfully'
                ];
            } else {
                throw new Exception("Database update failed: " . $stmt->error);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            // Log the error for debugging
            error_log("Update PO Item Error: " . $e->getMessage());
        }
        
        // Always return JSON response
        echo json_encode($response);
        exit;
    }

    function get_item_by_aircon_code() {
        header('Content-Type: application/json');
        try {
            // Check if aircon_code parameter is provided
            if (!isset($_GET['aircon_code']) || empty($_GET['aircon_code'])) {
                throw new Exception("Aircon code is required.");
            }
            
            $aircon_code = $this->conn->real_escape_string($_GET['aircon_code']);
            
            // Check if the item_list table exists
            $table_check = $this->conn->query("SHOW TABLES LIKE 'item_list'");
            if ($table_check->num_rows == 0) {
                throw new Exception("Item list table does not exist in the database.");
            }
            
            // Query to get item by aircon code
            $query = "SELECT * FROM `item_list` WHERE `description` LIKE ? OR `brand` LIKE ? OR `type` LIKE ? OR `hp` LIKE ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $search_term = "%$aircon_code%";
            $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'success', 'data' => null]);
            } else {
                $item = $result->fetch_assoc();
                echo json_encode(['status' => 'success', 'data' => $item]);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Error in get_item_by_aircon_code: " . $e->getMessage());
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    function remove_sale_item() {
        header('Content-Type: application/json');
        try {
            // Check if required parameters are present
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Missing required parameter: ID");
            }
            
            $id = (int)$_POST['id'];
            
            // Validate inputs
            if ($id <= 0) {
                throw new Exception("Invalid ID value");
            }
            
            // Check if the record exists
            $check_query = "SELECT id FROM sales_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Sale item not found");
            }
            $check_stmt->close();
            
            // Instead of deleting, mark as deleted
            $update_query = "UPDATE `sales_list` SET `deleted` = 1 WHERE `id` = ?";
            $update_stmt = $this->conn->prepare($update_query);
            if (!$update_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $update_stmt->bind_param("i", $id);
            
            if ($update_stmt->execute()) {
                $response = [
                    'status' => 'success', 
                    'msg' => 'Sale item removed successfully'
                ];
            } else {
                throw new Exception("Failed to remove sale item: " . $update_stmt->error);
            }
            
            $update_stmt->close();
            
        } catch (Exception $e) {
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            // Log the error for debugging
            error_log("Remove Sale Item Error: " . $e->getMessage());
        }
        
        // Always return JSON response
        echo json_encode($response);
        exit;
    }

    function get_sale() {
        header('Content-Type: application/json');
        try {
            // Check if ID is provided
            if (!isset($_GET['id'])) {
                throw new Exception("Missing ID");
            }
            
            $id = intval($_GET['id']);
            
            // Check if 'deleted' column exists, if not add it
            $check_columns = $this->conn->query("SHOW COLUMNS FROM `sales_list` LIKE 'deleted'");
            if ($check_columns->num_rows == 0) {
                $alter_query = "ALTER TABLE `sales_list` ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0";
                if (!$this->conn->query($alter_query)) {
                    throw new Exception("Failed to add deleted column: " . $this->conn->error);
                }
            }
            
            // Query to get the sale record, excluding deleted records
            $query = "SELECT * FROM `sales_list` WHERE `id` = ? AND `deleted` = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Sales record not found or has been deleted");
            }
            
            $sale = $result->fetch_assoc();
            $stmt->close();
            
            // Format price for display
            if (isset($sale['price']) && is_numeric($sale['price'])) {
                $sale['formatted_price'] = number_format(floatval($sale['price']), 2);
            } else {
                $sale['formatted_price'] = '0.00';
            }
            
            // Format total amount for display
            if (isset($sale['total_amount']) && is_numeric($sale['total_amount'])) {
                $sale['formatted_total'] = number_format(floatval($sale['total_amount']), 2);
            } else {
                $sale['formatted_total'] = '0.00';
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $sale
            ]);
            
        } catch (Exception $e) {
            error_log("Error in get_sale: " . $e->getMessage());
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }

    function get_sale_items() {
        header('Content-Type: application/json');
        try {
            // Check if sale_id is provided
            if (!isset($_GET['sale_id'])) {
                throw new Exception("Missing sale ID");
            }
            
            $sale_id = intval($_GET['sale_id']);
            
            // Check if 'deleted' column exists in sales_list, if not add it
            $check_columns = $this->conn->query("SHOW COLUMNS FROM `sales_list` LIKE 'deleted'");
            if ($check_columns->num_rows == 0) {
                $alter_query = "ALTER TABLE `sales_list` ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0";
                if (!$this->conn->query($alter_query)) {
                    throw new Exception("Failed to add deleted column: " . $this->conn->error);
                }
            }
            
            // Query to get sale items, excluding deleted records
            $query = "SELECT * FROM `sales_list` WHERE `id` = ? AND `deleted` = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $sale_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Sale items not found or sale has been deleted");
            }
            
            $sale_items = [];
            while ($row = $result->fetch_assoc()) {
                // Format price for display
                if (isset($row['price']) && is_numeric($row['price'])) {
                    $row['formatted_price'] = number_format(floatval($row['price']), 2);
                } else {
                    $row['formatted_price'] = '0.00';
                }
                
                // Format total amount for display
                if (isset($row['total_amount']) && is_numeric($row['total_amount'])) {
                    $row['formatted_total'] = number_format(floatval($row['total_amount']), 2);
                } else {
                    $row['formatted_total'] = '0.00';
                }
                
                $sale_items[] = $row;
            }
            
            $stmt->close();
            
            echo json_encode([
                'status' => 'success',
                'data' => $sale_items
            ]);
            
        } catch (Exception $e) {
            error_log("Error in get_sale_items: " . $e->getMessage());
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }

    function get_next_sales_order() {
        header('Content-Type: application/json');
        try {
            // Get the last sales order number
            $query = "SELECT invoice_number FROM `sales_list` WHERE invoice_number LIKE 'SO-%' ORDER BY id DESC LIMIT 1";
            $result = $this->conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $lastOrder = $row['invoice_number'];
                
                // Extract the numeric part (remove "SO-" prefix)
                $numericPart = substr($lastOrder, 3);
                
                // Convert to integer and increment
                $nextNumber = intval($numericPart) + 1;
                
                // Format with leading zeros to 5 digits
                $nextOrder = 'SO-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            } else {
                // If no previous orders found, start with SO-00001
                $nextOrder = 'SO-00001';
            }
            
            echo json_encode(['status' => 'success', 'next_order' => $nextOrder]);
            
        } catch (Exception $e) {
            error_log("Error in get_next_sales_order: " . $e->getMessage());
            echo json_encode(['status' => 'failed', 'msg' => $e->getMessage()]);
        }
        exit;
    }

    function bulk_update_sales() {
        header('Content-Type: application/json');
        try {
            // Check if sales data is provided
            if (!isset($_POST['sales']) || !is_array($_POST['sales'])) {
                throw new Exception("Sales data is required");
            }
            
            $sales = $_POST['sales'];
            $updated_count = 0;
            $errors = [];
            
            // Start transaction for data integrity
            $this->conn->begin_transaction();
            
            foreach ($sales as $sale) {
                try {
                    $id = isset($sale['id']) ? intval($sale['id']) : 0;
                    
                    if ($id <= 0) {
                        $errors[] = "Invalid sale ID: " . $id;
                        continue;
                    }
                    
                    // Get all columns from the table
                    $columns_result = $this->conn->query("SHOW COLUMNS FROM `sales_list`");
                    $existing_columns = [];
                    while ($row = $columns_result->fetch_assoc()) {
                        $existing_columns[] = $row['Field'];
                    }
                    
                    // Build the update query dynamically
                    $update_fields = [];
                    $params = [];
                    $types = '';
                    
                    // Process each field in the sale data
                    foreach ($sale as $key => $value) {
                        // Skip the id field and any non-column fields
                        if ($key === 'id' || !in_array($key, $existing_columns)) {
                            continue;
                        }
                        
                        // Sanitize the value based on its type
                        if ($key === 'price' || $key === 'total_amount') {
                            // Numeric fields
                            $value = (float)$value;
                            $update_fields[] = "`$key` = ?";
                            $params[] = $value;
                            $types .= 'd';
                        } elseif ($key === 'quantity') {
                            // Integer fields
                            $value = (int)$value;
                            $update_fields[] = "`$key` = ?";
                            $params[] = $value;
                            $types .= 'i';
                        } else {
                            // String fields
                            $value = trim($value);
                            $update_fields[] = "`$key` = ?";
                            $params[] = $value;
                            $types .= 's';
                        }
                    }
                    
                    // If no valid fields to update, skip
                    if (empty($update_fields)) {
                        continue;
                    }
                    
                    // Add the ID parameter for the WHERE clause
                    $types .= 'i';
                    $params[] = $id;
                    
                    // Build the complete SQL query
                    $sql = "UPDATE `sales_list` SET " . implode(', ', $update_fields) . " WHERE `id` = ?";
                    
                    // Prepare and execute the statement
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Prepare statement failed: " . $this->conn->error);
                    }
                    
                    // Bind parameters
                    $stmt->bind_param($types, ...$params);
                    
                    // Execute the statement
                    if ($stmt->execute()) {
                        $updated_count++;
                    } else {
                        $errors[] = "Failed to update sale ID $id: " . $stmt->error;
                    }
                    
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $errors[] = "Error updating sale ID " . (isset($sale['id']) ? $sale['id'] : 'unknown') . ": " . $e->getMessage();
                }
            }
            
            // Commit transaction if no critical errors
            if (empty($errors)) {
                $this->conn->commit();
                
                $response = [
                    'status' => 'success', 
                    'msg' => "$updated_count sales records updated successfully"
                ];
            } else {
                $this->conn->rollback();
                
                $response = [
                    'status' => 'error',
                    'msg' => "Errors occurred during bulk update",
                    'errors' => $errors
                ];
            }
            
        } catch (Exception $e) {
            // Roll back transaction on error
            $this->conn->rollback();
            
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage()
            ];
            // Log the error for debugging
            error_log("Bulk Update Sales Error: " . $e->getMessage());
        }
        
        // Always return JSON response
        echo json_encode($response);
        exit;
    }

    // NEW FUNCTION: Get all receiving stocks from purchase_order_list
    function get_all_purchase_order_stocks() {
        // Set content type header
        header('Content-Type: application/json');
        
        try {
            // Get brand filter if provided
            $brand = isset($_GET['brand']) ? $this->conn->real_escape_string($_GET['brand']) : '';
            
            // Build the query to get all stocks from purchase_order_list
            $query = "SELECT * FROM `purchase_order_list` WHERE (deleted = 0 OR deleted IS NULL)";
            
            // Add brand filter if specified
            if (!empty($brand)) {
                $query .= " AND brand = '" . $brand . "'";
            }
            
            // Order by brand, hp, and then by other fields for consistent display
            $query .= " ORDER BY brand ASC, hp ASC, type ASC";
            
            $result = $this->conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $stocks = [];
            while ($row = $result->fetch_assoc()) {
                // Format price for display
                if (isset($row['price']) && is_numeric($row['price'])) {
                    $row['formatted_price'] = number_format(floatval($row['price']), 2);
                } else {
                    $row['formatted_price'] = '0.00';
                }
                
                // Format total for display
                if (isset($row['total']) && is_numeric($row['total'])) {
                    $row['formatted_total'] = number_format(floatval($row['total']), 2);
                } else {
                    $row['formatted_total'] = '0.00';
                }
                
                // Ensure all required fields exist
                $required_fields = [
                    'id', 'brand', 'type', 'hp', 'quantity', 'price', 'total',
                    'indoor', 'indoor_serial', 'outdoor', 'outdoor_serial'
                ];
                
                foreach ($required_fields as $field) {
                    if (!isset($row[$field])) {
                        $row[$field] = '';
                    }
                }
                
                $stocks[] = $row;
            }
            
            // Clean output buffer to prevent any stray output
            if (ob_get_level()) ob_clean();
            
            // Return JSON response
            echo json_encode([
                'status' => 'success',
                'data' => $stocks
            ]);
            exit;
            
        } catch (Exception $e) {
            // Clean output buffer
            if (ob_get_level()) ob_clean();
            
            // Return error as JSON
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // NEW FUNCTION: Get all deleted items from delete_list
    function get_all_deleted_items() {
        // Set content type header
        header('Content-Type: application/json');
        
        try {
            // Ensure the delete_list table exists
            $this->ensure_delete_list_table();
            
            // Get brand filter if provided
            $brand = isset($_GET['brand']) ? $this->conn->real_escape_string($_GET['brand']) : '';
            
            // Build the query to get all deleted items from delete_list
            $query = "SELECT * FROM `delete_list`";
            
            // Add brand filter if specified
            if (!empty($brand)) {
                $query .= " WHERE brand = '" . $brand . "'";
            }
            
            // Order by deleted_at, then by brand, hp, and then by other fields for consistent display
            $query .= " ORDER BY deleted_at DESC, brand ASC, hp ASC, type ASC";
            
            $result = $this->conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $deleted_items = [];
            while ($row = $result->fetch_assoc()) {
                // Format price for display
                if (isset($row['price']) && is_numeric($row['price'])) {
                    $row['formatted_price'] = number_format(floatval($row['price']), 2);
                } else {
                    $row['formatted_price'] = '0.00';
                }
                
                // Format total for display
                if (isset($row['total']) && is_numeric($row['total'])) {
                    $row['formatted_total'] = number_format(floatval($row['total']), 2);
                } else {
                    $row['formatted_total'] = '0.00';
                }
                
                // Format deleted_at for display
                if (isset($row['deleted_at']) && !empty($row['deleted_at'])) {
                    $row['formatted_deleted_at'] = date('M d, Y h:i A', strtotime($row['deleted_at']));
                }
                
                // Ensure all required fields exist
                $required_fields = [
                    'id', 'brand', 'type', 'hp', 'quantity', 'price', 'total',
                    'indoor', 'indoor_serial', 'outdoor', 'outdoor_serial', 'deleted_at'
                ];
                
                foreach ($required_fields as $field) {
                    if (!isset($row[$field])) {
                        $row[$field] = '';
                    }
                }
                
                $deleted_items[] = $row;
            }
            
            // Clean output buffer to prevent any stray output
            if (ob_get_level()) ob_clean();
            
            // Return JSON response
            echo json_encode([
                'status' => 'success',
                'data' => $deleted_items
            ]);
            exit;
            
        } catch (Exception $e) {
            // Clean output buffer
            if (ob_get_level()) ob_clean();
            
            // Return error as JSON
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // NEW FUNCTION: Restore deleted item from delete_list back to purchase_order_list
    function restore_deleted_item() {
        // Set content type header
        header('Content-Type: application/json');
        
        try {
            // Check if ID is provided
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Deleted item ID is required.");
            }
            
            $id = intval($_POST['id']);
            
            if ($id <= 0) {
                throw new Exception("Invalid ID value.");
            }
            
            // Start transaction for data integrity
            $this->conn->begin_transaction();
            
            // Ensure the delete_list table exists
            $this->ensure_delete_list_table();
            
            // Get the record from delete_list
            $select_query = "SELECT * FROM delete_list WHERE id = ?";
            $stmt = $this->conn->prepare($select_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                throw new Exception("Deleted item not found");
            }
            
            $deleted_data = $result->fetch_assoc();
            $stmt->close();
            
            // Get all columns from purchase_order_list
            $po_columns = [];
            $result = $this->conn->query("SHOW COLUMNS FROM `purchase_order_list`");
            while ($row = $result->fetch_assoc()) {
                $po_columns[] = $row['Field'];
            }
            
            // Prepare data for insertion
            $insert_data = [];
            foreach ($deleted_data as $key => $value) {
                if (in_array($key, $po_columns) && $key !== 'id' && $key !== 'deleted_at') {
                    $insert_data[$key] = $value;
                }
            }
            
            // Build the insert query
            $columns = array_keys($insert_data);
            $values = array_values($insert_data);
            
            $column_names = implode("`, `", $columns);
            $placeholders = implode(", ", array_fill(0, count($values), "?"));
            
            $insert_query = "INSERT INTO `purchase_order_list` (`$column_names`) VALUES ($placeholders)";
            
            $insert_stmt = $this->conn->prepare($insert_query);
            if (!$insert_stmt) {
                throw new Exception("Insert prepare failed: " . $this->conn->error);
            }
            
            // Create types string for bind_param
            $types = "";
            foreach ($values as $value) {
                if (is_int($value)) {
                    $types .= "i";
                } elseif (is_float($value)) {
                    $types .= "d";
                } else {
                    $types .= "s";
                }
            }
            
            $insert_stmt->bind_param($types, ...$values);
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to restore to purchase_order_list: " . $insert_stmt->error);
            }
            
            $insert_stmt->close();
            
            // Delete the record from delete_list
            $delete_query = "DELETE FROM delete_list WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_query);
            if (!$delete_stmt) {
                throw new Exception("Delete prepare failed: " . $this->conn->error);
            }
            
            $delete_stmt->bind_param("i", $id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to delete from delete_list: " . $delete_stmt->error);
            }
            
            $delete_stmt->close();
            
            // Commit the transaction
            $this->conn->commit();
            
            echo json_encode([
                'status' => 'success',
                'msg' => 'Item successfully restored to purchase order list.'
            ]);
            
        } catch (Exception $e) {
            // Roll back the transaction on error
            $this->conn->rollback();
            
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    // NEW FUNCTION: Permanently delete item from delete_list
    function permanently_delete_item() {
        // Set content type header
        header('Content-Type: application/json');
        
        try {
            // Check if ID is provided
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                throw new Exception("Deleted item ID is required.");
            }
            
            $id = intval($_POST['id']);
            
            if ($id <= 0) {
                throw new Exception("Invalid ID value.");
            }
            
            // Ensure the delete_list table exists
            $this->ensure_delete_list_table();
            
            // Check if the record exists first
            $check_query = "SELECT id FROM delete_list WHERE id = ?";
            $check_stmt = $this->conn->prepare($check_query);
            if (!$check_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Deleted item not found");
            }
            $check_stmt->close();
            
            // Prepare the delete statement
            $delete_query = "DELETE FROM delete_list WHERE id = ?";
            $delete_stmt = $this->conn->prepare($delete_query);
            if (!$delete_stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $delete_stmt->bind_param("i", $id);
            
            // Execute the delete statement
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to permanently delete item: " . $delete_stmt->error);
            }
            
            // Check if any rows were actually deleted
            $affected_rows = $delete_stmt->affected_rows;
            $delete_stmt->close();
            
            if ($affected_rows === 0) {
                throw new Exception("No item was deleted");
            }
            
            echo json_encode([
                'status' => 'success',
                'msg' => 'Item permanently deleted from the system.'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    function get_available_products() {
        header('Content-Type: application/json');
        try {
            // First check if the purchase_order_list table exists
            $table_check = $this->conn->query("SHOW TABLES LIKE 'purchase_order_list'");
            if ($table_check->num_rows == 0) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [],
                    'source' => 'purchase_order_list',
                    'message' => 'purchase_order_list table does not exist. No products available.'
                ]);
                exit;
            }
            
            // Get all columns from purchase_order_list table
            $columns_result = $this->conn->query("SHOW COLUMNS FROM `purchase_order_list`");
            if (!$columns_result) {
                throw new Exception("Error getting table columns: " . $this->conn->error);
            }
            
            $existing_columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
            
            // Check if deleted column exists, if not add it
            if (!in_array('deleted', $existing_columns)) {
                $alter_query = "ALTER TABLE `purchase_order_list` ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0";
                if (!$this->conn->query($alter_query)) {
                    throw new Exception("Failed to add deleted column: " . $this->conn->error);
                }
                $existing_columns[] = 'deleted';
            }
            
            // Build query to get available products (not deleted and with quantity > 0)
            $query = "SELECT * FROM `purchase_order_list` WHERE (`deleted` = 0 OR `deleted` IS NULL)";
            
            // Only include items with available quantity if quantity column exists
            if (in_array('quantity', $existing_columns)) {
                $query .= " AND `quantity` > 0";
            }
            
            // Add ordering
            $query .= " ORDER BY brand ASC, type ASC, hp ASC";
            
            $result = $this->conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $products = [];
            while ($row = $result->fetch_assoc()) {
                // Format price for display
                if (isset($row['price']) && is_numeric($row['price'])) {
                    $row['formatted_price'] = number_format(floatval($row['price']), 2);
                } else {
                    $row['formatted_price'] = '0.00';
                }
                
                // Ensure all required fields exist
                $required_fields = [
                    'id', 'brand', 'type', 'hp', 'quantity', 'price', 'unit',
                    'indoor', 'indoor_serial', 'outdoor', 'outdoor_serial'
                ];
                
                foreach ($required_fields as $field) {
                    if (!isset($row[$field])) {
                        $row[$field] = '';
                    }
                }
                
                // Generate a description field for display
                $description_parts = [];
                if (!empty($row['brand'])) $description_parts[] = $row['brand'];
                if (!empty($row['hp'])) $description_parts[] = $row['hp'] . ' HP';
                if (!empty($row['type'])) $description_parts[] = $row['type'];
                $row['description'] = implode(' ', $description_parts);
                
                // IMPORTANT: Include the PO item ID for tracking
                $row['po_item_id'] = $row['id'];
                
                $products[] = $row;
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $products,
                'source' => 'purchase_order_list',
                'message' => 'Displaying available products from purchase_order_list only'
            ]);
            
        } catch (Exception $e) {
            error_log("Error in get_available_products: " . $e->getMessage());
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }

    function verify_po_items_deleted() {
        header('Content-Type: application/json');
        
        try {
            // Get all items from purchase_order_list
            $query = "SELECT id, brand, hp, type, indoor_serial, outdoor_serial FROM `purchase_order_list` WHERE (`deleted` = 0 OR `deleted` IS NULL)";
            $result = $this->conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $this->conn->error);
            }
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            echo json_encode([
                'status' => 'success',
                'count' => count($items),
                'items' => $items
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'failed',
                'msg' => $e->getMessage()
            ]);
        }
        exit;
    }
    public function delete_task() {
        header('Content-Type: application/json');
        try {
            // Check if ID is provided
            if (empty($_POST['id'])) {
                throw new Exception('Task ID is required');
            }
            
            // Sanitize the ID
            $id = intval($_POST['id']);
            
            // Start transaction
            $this->conn->begin_transaction();
            
            // Check if the completed_tasks table exists
            $check_table = $this->conn->query("SHOW TABLES LIKE 'completed_tasks'");
            if ($check_table->num_rows == 0) {
                throw new Exception("Completed tasks table does not exist.");
            }
            
            // Delete the record from completed_tasks
            $delete_query = "DELETE FROM `completed_tasks` WHERE id = ?";
            $stmt = $this->conn->prepare($delete_query);
            
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $resp['status'] = 'success';
                $resp['msg'] = "Completed task successfully deleted.";
            } else {
                throw new Exception("Failed to delete task: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Commit transaction
            $this->conn->commit();
            
            echo json_encode($resp);
            
        } catch (Exception $e) {
            // Rollback on error (checking if connection exists to avoid secondary errors)
            if (isset($this->conn) && $this->conn->connect_error === false) {
                $this->conn->rollback();
            }
            echo json_encode(array('status' => 'failed', 'msg' => $e->getMessage()));
        }
        exit;
    }
}

// Fix the action handling section
 $Master = new Master();

// Fix the action handling section
 $action = isset($_GET['f']) ? strtolower($_GET['f']) : '';

switch ($action) {
    case 'save_supplier':
        echo $Master->save_supplier();
        break;
    case 'delete_supplier':
        echo $Master->delete_supplier();
        break;
    case 'save_item':
        echo $Master->save_item();
        break;
    case 'delete_item':
        echo $Master->delete_item();
        break;
    case 'get_or_create_item':
        echo $Master->get_or_create_item();
        break;
    case 'save_po':
        echo $Master->save_po();
        break;
    case 'delete_po':
        echo $Master->delete_po();
        break;
    case 'get_received_items_count':
        echo $Master->get_received_items_count();
        break;
    case 'get_po_item':
        echo $Master->get_po_item();
        break;
    case 'update_purchase_order':
        echo $Master->update_purchase_order();
        break;
    case 'delete_purchase_order':
        echo $Master->delete_purchase_order();
        break;
    case 'save_sale':
        echo $Master->save_sale();
        break;
    case 'remove_sale_item':
        echo $Master->remove_sale_item();
        break;
    case 'get_sales_list':
        echo $Master->get_sales_list();
        break;
    case 'get_sale':
        echo $Master->get_sale();
        break;
    case 'update_sales':
        echo $Master->update_sales();
        break;
    case 'delete_sales':
        echo $Master->delete_sales();
        break;
    case 'get_sale_items':
        echo $Master->get_sale_items();
        break;
    case 'get_next_sales_order':
        echo $Master->get_next_sales_order();
        break;
    case 'save_schedule':
        echo $Master->save_schedule();
        break;
    case 'update_schedule':
        echo $Master->update_schedule();
        break;
    case 'get_all_schedules':
        echo $Master->get_all_schedules();
        break;
    case 'bulk_update_sales':
        echo $Master->bulk_update_sales();
        break;
    case 'mark_schedule_complete':
        echo $Master->mark_schedule_complete();
        break;
    case 'get_completed_tasks':
        echo $Master->get_completed_tasks();
        break;
    case 'delete_schedule':
        echo $Master->delete_schedule();
        break;
    case 'update_po_item':
        echo $Master->update_po_item();
        break;
    case 'delete_task':
        echo $Master->delete_task();
        break;
    case 'get_item_by_aircon_code':
        echo $Master->get_item_by_aircon_code();
        break;
    case 'get_all_purchase_order_stocks':
        echo $Master->get_all_purchase_order_stocks();
        break;
    case 'get_all_deleted_items':
        echo $Master->get_all_deleted_items();
        break;
    case 'restore_deleted_item':
        echo $Master->restore_deleted_item();
        break;
    case 'permanently_delete_item':
        echo $Master->permanently_delete_item();
        break;
    case 'get_available_products':
        echo $Master->get_available_products();
        break;
    case 'verify_po_items_deleted':
        echo $Master->verify_po_items_deleted();
        break;
    default:
        // Handle unknown action
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'msg' => 'Unknown action']);
        break;
}
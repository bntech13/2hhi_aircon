<?php
// Include database connection
require_once 'includes/database.php';

// Function to get brands from a table
function getBrandsFromTable($conn, $table_name) {
    $brands = [];
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($table_check->num_rows == 0) {
        return $brands;
    }
    
    // Get table structure
    $result = $conn->query("DESCRIBE $table_name");
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Find brand column
    $brand_column = null;
    $possible_brand_columns = ['brand', 'name', 'item_name', 'product_name', 'title', 'item'];
    foreach($possible_brand_columns as $col) {
        if(in_array($col, $columns)) {
            $brand_column = $col;
            break;
        }
    }
    
    // If we found brand column, get unique brands
    if($brand_column) {
        $qry = $conn->query("SELECT DISTINCT `$brand_column` FROM `$table_name` WHERE `$brand_column` != '' AND `$brand_column` IS NOT NULL");
        while($row = $qry->fetch_assoc()) {
            $brands[] = $row[$brand_column];
        }
    }
    
    return $brands;
}

// Function to get total quantity for a brand from a specific table
function getBrandQuantity($conn, $table_name, $brand) {
    $total = 0;
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($table_check->num_rows == 0) {
        return 0;
    }
    
    // Get table structure
    $result = $conn->query("DESCRIBE $table_name");
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Find brand column
    $brand_column = null;
    $possible_brand_columns = ['brand', 'name', 'item_name', 'product_name', 'title', 'item'];
    foreach($possible_brand_columns as $col) {
        if(in_array($col, $columns)) {
            $brand_column = $col;
            break;
        }
    }
    
    // Find quantity column
    $qty_column = null;
    $possible_qty_columns = ['quantity', 'qty', 'amount', 'total_quantity'];
    foreach($possible_qty_columns as $col) {
        if(in_array($col, $columns)) {
            $qty_column = $col;
            break;
        }
    }
    
    // If we found both columns, calculate total
    if($brand_column && $qty_column) {
        // Sanitize inputs to prevent SQL injection
        $brand = $conn->real_escape_string($brand);
        $qry = $conn->query("SELECT SUM(`$qty_column`) as total FROM `$table_name` WHERE `$brand_column` = '$brand'");
        if($qry) {
            $row = $qry->fetch_assoc();
            $total = $row['total'] ?? 0;
        }
    }
    
    return $total;
}

// Function to get available stock for a brand
function getAvailableStock($conn, $brand) {
    $available = 0;
    
    // Check if receiving_list exists
    $table_check = $conn->query("SHOW TABLES LIKE 'receiving_list'");
    if ($table_check->num_rows == 0) {
        return 0;
    }
    
    // Get table structure
    $result = $conn->query("DESCRIBE receiving_list");
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Find brand column
    $brand_column = null;
    $possible_brand_columns = ['brand', 'name', 'item_name', 'product_name', 'title', 'item'];
    foreach($possible_brand_columns as $col) {
        if(in_array($col, $columns)) {
            $brand_column = $col;
            break;
        }
    }
    
    // Find quantity column
    $qty_column = null;
    $possible_qty_columns = ['quantity', 'qty', 'amount', 'total_quantity'];
    foreach($possible_qty_columns as $col) {
        if(in_array($col, $columns)) {
            $qty_column = $col;
            break;
        }
    }
    
    // If we found both columns, calculate available stock
    if($brand_column && $qty_column) {
        // Sanitize input to prevent SQL injection
        $brand = $conn->real_escape_string($brand);
        
        // Get total received quantities
        $qry = $conn->query("SELECT SUM(`$qty_column`) as received FROM `receiving_list` WHERE `$brand_column` = '$brand'");
        if($qry) {
            $row = $qry->fetch_assoc();
            $total_received = $row['received'] ?? 0;
        } else {
            $total_received = 0;
        }
        
        // Get total sold quantities from sales_list
        $sales_table_check = $conn->query("SHOW TABLES LIKE 'sales_list'");
        if ($sales_table_check->num_rows > 0) {
            $sales_result = $conn->query("DESCRIBE sales_list");
            $sales_columns = [];
            while($row = $sales_result->fetch_assoc()) {
                $sales_columns[] = $row['Field'];
            }
            
            $sales_brand_column = null;
            foreach($possible_brand_columns as $col) {
                if(in_array($col, $sales_columns)) {
                    $sales_brand_column = $col;
                    break;
                }
            }
            
            $sales_qty_column = null;
            foreach($possible_qty_columns as $col) {
                if(in_array($col, $sales_columns)) {
                    $sales_qty_column = $col;
                    break;
                }
            }
            
            if($sales_brand_column && $sales_qty_column) {
                $sales_qry = $conn->query("SELECT SUM(`$sales_qty_column`) as sold FROM `sales_list` WHERE `$sales_brand_column` = '$brand'");
                if($sales_qry) {
                    $sales_row = $sales_qry->fetch_assoc();
                    $total_sold = $sales_row['sold'] ?? 0;
                } else {
                    $total_sold = 0;
                }
            } else {
                $total_sold = 0;
            }
        } else {
            $total_sold = 0;
        }
        
        // Calculate available stock
        $available = $total_received - $total_sold;
    }
    
    return max(0, $available); // Ensure we don't return negative values
}

// Get the selected brand from POST request
 $selectedBrand = $_POST['brand'] ?? '';

if (!empty($selectedBrand)) {
    // Calculate inventory data for the selected brand
    $total_purchased = getBrandQuantity($conn, 'purchase_order_list', $selectedBrand) + 
                       getBrandQuantity($conn, 'receiving_list', $selectedBrand);
    $total_sold = getBrandQuantity($conn, 'sales_list', $selectedBrand);
    $available = getAvailableStock($conn, $selectedBrand);
    
    // Prepare response data
    $response = [
        'success' => true,
        'data' => [
            'hp' => number_format($total_purchased),
            'indoor' => number_format($total_sold),
            'outdoor' => number_format($available),
            'price' => '$' . number_format(($total_purchased > 0) ? ($total_purchased * 10) : 0, 2),
            'qty' => number_format($available)
        ]
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'No brand selected'
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
<?php
require_once('./config.php');

echo "<h1>Database Structure Check</h1>";

// Check if stock_list table exists
$table_check = $conn->query("SHOW TABLES LIKE 'stock_list'");
if ($table_check->num_rows > 0) {
    echo "<h2>stock_list table exists</h2>";
    
    // Get columns in stock_list
    $columns_query = $conn->query("SHOW COLUMNS FROM `stock_list`");
    echo "<h3>Columns in stock_list:</h3>";
    echo "<ul>";
    while($col = $columns_query->fetch_assoc()) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>stock_list table does NOT exist</h2>";
}

// Check if sale_items table exists
$table_check = $conn->query("SHOW TABLES LIKE 'sale_items'");
if ($table_check->num_rows > 0) {
    echo "<h2>sale_items table exists</h2>";
    
    // Get columns in sale_items
    $columns_query = $conn->query("SHOW COLUMNS FROM `sale_items`");
    echo "<h3>Columns in sale_items:</h3>";
    echo "<ul>";
    while($col = $columns_query->fetch_assoc()) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>sale_items table does NOT exist</h2>";
}

// Check if sales_list table exists
$table_check = $conn->query("SHOW TABLES LIKE 'sales_list'");
if ($table_check->num_rows > 0) {
    echo "<h2>sales_list table exists</h2>";
    
    // Get columns in sales_list
    $columns_query = $conn->query("SHOW COLUMNS FROM `sales_list`");
    echo "<h3>Columns in sales_list:</h3>";
    echo "<ul>";
    while($col = $columns_query->fetch_assoc()) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>sales_list table does NOT exist</h2>";
}

// Check if item_list table exists
$table_check = $conn->query("SHOW TABLES LIKE 'item_list'");
if ($table_check->num_rows > 0) {
    echo "<h2>item_list table exists</h2>";
    
    // Get columns in item_list
    $columns_query = $conn->query("SHOW COLUMNS FROM `item_list`");
    echo "<h3>Columns in item_list:</h3>";
    echo "<ul>";
    while($col = $columns_query->fetch_assoc()) {
        echo "<li>" . $col['Field'] . " - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h2>item_list table does NOT exist</h2>";
}

$conn->close();
?>
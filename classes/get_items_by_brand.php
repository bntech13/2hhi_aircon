<?php
require_once('../config.php'); // Adjust path as needed
// Assuming $conn is already established in config.php

header('Content-Type: application/json');

if(isset($_GET['brand']) && !empty($_GET['brand'])) {
    $brand = $conn->real_escape_string($_GET['brand']);

    // Join item_list with stock_list to get available quantity
    // Summing quantities from stock_list where status = 0 (in-stock)
    $query = $conn->query("
        SELECT 
            il.id, 
            il.aircon_code, 
            il.brand, 
            il.type, 
            il.hp, 
            il.indoor_outdoor, 
            il.price,
            COALESCE(SUM(CASE WHEN sl.status = 0 THEN sl.quantity ELSE 0 END), 0) as available_stock_qty
        FROM `item_list` il
        LEFT JOIN `stock_list` sl ON il.id = sl.item_id
        WHERE il.brand = '{$brand}'
        GROUP BY il.id, il.aircon_code, il.brand, il.type, il.hp, il.indoor_outdoor, il.price
        HAVING available_stock_qty > 0
        ORDER BY il.aircon_code ASC
    ");

    $items = array();
    while($row = $query->fetch_assoc()){
        $items[] = $row;
    }
    echo json_encode($items);
} else {
    echo json_encode([]); // Return empty array if no brand is provided
}

$conn->close();
?>
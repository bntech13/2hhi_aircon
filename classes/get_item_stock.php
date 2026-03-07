<?php
require_once('../config.php');

header('Content-Type: application/json');

if (!isset($_GET['aircon_code']) || empty($_GET['aircon_code'])) {
    echo json_encode(['status' => 'failed', 'msg' => 'Aircon Code not specified.']);
    exit;
}

$aircon_code = $conn->real_escape_string($_GET['aircon_code']);

// Query to get item_id and available stock quantity for a given aircon code
$query = "
    SELECT
        il.id as item_id,
        SUM(sl.quantity) as available_stock_qty
    FROM item_list il
    INNER JOIN stock_list sl ON il.id = sl.item_id
    WHERE il.aircon_code = '$aircon_code' AND sl.quantity > 0
    GROUP BY il.id
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'item_id' => $row['item_id'],
        'available_stock_qty' => (int)$row['available_stock_qty']
    ]);
} else {
    echo json_encode(['status' => 'failed', 'msg' => 'Item not found or out of stock.']);
}
?>
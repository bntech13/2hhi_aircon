<?php 
// Updated query - removed non-existent columns and used proper column names
$qry = $conn->query("SELECT p.*, s.name as supplier, 
                           CASE WHEN r.id IS NOT NULL THEN 1 ELSE 0 END as received_status,
                           r.date_created as received_date
                    FROM `purchase_order_list` p 
                    INNER JOIN supplier_list s ON p.supplier_id = s.id 
                    LEFT JOIN receiving_list r ON p.id = r.form_id AND r.from_order = 1
                    ORDER BY p.`date_created` DESC");
if (!$qry) {
    echo "<tr><td colspan='14' class='text-center text-danger'>Error loading data: " . $conn->error . "</td></tr>";
} else {
    while($row = $qry->fetch_assoc()):
        $row['items'] = $conn->query("SELECT count(item_id) as `items` FROM `po_items` where po_id = '{$row['id']}' ")->fetch_assoc()['items'];
        
        // Get all aircon codes for this purchase order
        $all_items = $conn->query("SELECT pi.serial_no, i.brand, i.type, i.hp, pi.indoor_outdoor, pi.price as item_price, pi.quantity as item_quantity 
                                    FROM `po_items` pi 
                                    inner join item_list i on pi.item_id = i.id 
                                    where pi.po_id = '{$row['id']}'");
        
        $aircon_codes = array();
        $brand_name = '';
        $description = '';
        $item_price = 0;
        $item_quantity = 0;
        $total_amount = 0;
        $total_quantity = 0;
        
        if ($all_items && $all_items->num_rows > 0) {
            $first_item = true;
            while($item = $all_items->fetch_assoc()) {
                // Collect all aircon codes
                $aircon_codes[] = $item['serial_no'];
                
                // Calculate totals
                $total_amount += $item['item_price'] * $item['item_quantity'];
                $total_quantity += $item['item_quantity'];
                
                // Build description from first item
                if ($first_item) {
                    $brand_name = $item['brand'];
                    $indoor_outdoor = !empty($item['indoor_outdoor']) ? $item['indoor_outdoor'] . ', ' : '';
                    $description = $indoor_outdoor . $item['type'] . ' Type, ' . $item['hp'];
                    $item_price = $item['item_price'];
                    $item_quantity = $item['item_quantity'];
                    $first_item = false;
                }
            }
            
            // If there are multiple items, add a note
            if ($row['items'] > 1) {
                $description .= " +" . ($row['items'] - 1) . " more";
            }
        }
        
        // Combine all aircon codes with commas
        $aircon_code = implode(', ', $aircon_codes);
        
        // Calculate average price per unit
        $average_price = ($total_quantity > 0) ? ($total_amount / $total_quantity) : 0;
        
        // Determine payment status
        $payment_status = '';
        if (isset($row['payment_status'])) {
            // Convert payment status to readable format
            switch($row['payment_status']) {
                case 0:
                    $payment_status = '<span class="badge badge-warning">Pending</span>';
                    break;
                case 1:
                    $payment_status = '<span class="badge badge-success">Paid</span>';
                    break;
                case 2:
                    $payment_status = '<span class="badge badge-danger">Overdue</span>';
                    break;
                default:
                    $payment_status = '<span class="badge badge-secondary">Unknown</span>';
            }
        } else {
            $payment_status = '<span class="badge badge-secondary">Not Set</span>';
        }
        
        // Check if this purchase order has been received
        $is_received = ($row['received_status'] == 1);
    ?>
        <tr>
            <td class="text-left align-middle"><?php $date = strtotime($row['date_created']); echo date('Y M', $date) . '.' . ' ' . date('j', $date);?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($brand_name) ?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($aircon_code) ?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($row['invoice'] ?? '') ?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($row['dr'] ?? '') ?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($row['po'] ?? '') ?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($row['supplier']) ?></td>
            <td class="text-left align-middle"><?php echo htmlspecialchars($description) ?></td>
            <td class="text-left align-middle"><?php echo $total_quantity ?></td>
            <td class="text-left align-middle"><?php echo number_format($item_price, 2) ?></td>
            <td class="text-left align-middle"><?php echo number_format($total_amount, 2) ?></td>
            <td class="text-left align-middle"><?php echo $payment_status ?></td>
            <td class="text-left align-middle">
                <div class="remarks-display">
                    <?php echo isset($row['remarks']) ? htmlspecialchars($row['remarks']) : ''; ?>
                </div>
            </td>
            <td class="text-left align-middle">
                <?php if (!$is_received): ?>
                    <button type="button" class="btn btn-sm btn-success mark-as-received" data-id="<?php echo $row['id'] ?>" aria-label="Mark purchase order <?php echo $row['id'] ?> as received">
                        <i class="fas fa-check-circle" aria-hidden="true"></i> Mark as Received
                    </button>
                <?php else: ?>
                    <span class="badge badge-success" role="status" aria-label="Purchase order received">
                        <i class="fas fa-check-circle"></i> Received
                        <?php if ($row['received_date']): ?>
                            <br><small><?php echo date('M j, Y', strtotime($row['received_date'])); ?></small>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; 
} ?>
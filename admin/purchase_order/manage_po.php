<?php
// Secure ID validation and data fetching
 $po_data = [];
 $is_edit = false;

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    
    if ($id === false || $id === null) {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error: A valid Purchase Order ID is required.</div>';
        echo '<script>setTimeout(function(){ window.location.href = "?page=purchase_order"; }, 3000);</script>';
        return;
    }
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT p.*, COALESCE(s.name, p.supplier_name) as supplier_name FROM purchase_order_list p LEFT JOIN supplier_list s ON p.supplier_id = s.id WHERE p.id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $po_data = $result->fetch_assoc();
            $is_edit = true;
            
            // Extract variables for backward compatibility
            foreach ($po_data as $k => $v) {
                $$k = $v;
            }
        } else {
            echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>Error: Purchase Order with the specified ID was not found.</div>';
            echo '<script>setTimeout(function(){ window.location.href = "?page=purchase_order"; }, 3000);</script>';
            return;
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger"><i class="fas fa-database mr-2"></i>Database error occurred.</div>';
        return;
    }
}
?>
<style>
    select[readonly].select2-hidden-accessible+.select2-container { pointer-events: none; touch-action: none; background: #eee; box-shadow: none; }
    select[readonly].select2-hidden-accessible+.select2-container .select2-selection { background: #eee; box-shadow: none; }
    .form-label { margin-bottom: 0.3rem; font-weight: 500; font-size: 0.875rem; }
    .form-control, .custom-select { padding: 0.375rem 0.75rem; height: calc(1.5em + 0.75rem + 2px); font-size: 0.875rem; }
    .form-group { margin-bottom: 0.5rem; }
    .item-input-section { background-color: #f8f9fa; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    .icon-label { display: flex; align-items: center; }
    .icon-label i { margin-right: 0.5rem; color: #007bff; }
    
    /* Refresh button styling */
    #refresh-btn {
        padding: 8px 15px;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    #refresh-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    #refresh-btn i {
        margin-right: 6px;
    }

    /* Add spinning animation for refresh icon */
    .refreshing i {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Make table list text bold */
    #list td {
        font-weight: bold;
    }
    
    /* Custom toast styling */
    .custom-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        z-index: 9999;
        min-width: 250px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .custom-toast.success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .custom-toast.warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .custom-toast.error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .custom-toast .close-toast {
        margin-left: 15px;
        cursor: pointer;
        font-size: 1.2rem;
        opacity: 0.7;
    }
    
    .custom-toast .close-toast:hover {
        opacity: 1;
    }
    
    /* Text transform to uppercase for input fields */
    .uppercase-input {
        text-transform: uppercase;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
        <h6 style="color: #0066cc; margin: 0;">
            <i class="fas fa-plus-circle"></i> <b>ADD PURCHASED</b>
        </h6>
    </div>
    
    <button id="refresh-btn" class="btn btn-outline-primary btn-flat" style="display: flex; align-items: center;">
        <i class="fas fa-sync-alt"></i> Refresh
    </button>
</div>

<div class="card-body">
    <form action="" id="po-form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <!-- Add hidden status field with default value -->
        <input type="hidden" name="status" value="<?php echo isset($status) ? htmlspecialchars($status) : 'pending'; ?>">
        <div class="container-fluid">
            <!-- Header section -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i> Purchase Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <label for="invoice" class="form-label icon-label">
                                <i class="fas fa-file-invoice"></i> Invoice No
                            </label>
                            <input type="text" name="invoice" id="invoice" class="form-control uppercase-input" value="<?php echo isset($invoice) ? htmlspecialchars($invoice) : '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="dr" class="form-label icon-label">
                                <i class="fas fa-truck"></i> DR No.
                            </label>
                            <input type="text" name="dr" id="dr" class="form-control uppercase-input" value="<?php echo isset($dr) ? htmlspecialchars($dr) : '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="po" class="form-label icon-label">
                                <i class="fas fa-clipboard-list"></i> PO No.
                            </label>
                            <input type="text" name="po" id="po" class="form-control uppercase-input" value="<?php echo isset($po) ? htmlspecialchars($po) : '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="supplier_id" class="form-label icon-label">
                                <i class="fas fa-building"></i> Supplier <span class="text-danger">*</span>
                            </label>
                            <select name="supplier_id" id="supplier_id" class="custom-select select2" required>
                                <option value="" disabled selected>Select Supplier</option>
                                <?php 
                                $supplier_qry = $conn->query("SELECT * FROM `supplier_list` ORDER BY `name` ASC"); 
                                while ($row = $supplier_qry->fetch_assoc()) : 
                                ?>
                                <option value="<?php echo $row['id'] ?>" <?php echo isset($supplier_id) && $supplier_id == $row['id'] ? "selected" : "" ?>>
                                    <?php echo htmlspecialchars($row['name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="delivery_date" class="form-label icon-label">
                                <i class="fas fa-calendar-alt"></i> Delivery Date
                            </label>
                                <input type="date" name="date" id="delivery_date" class="form-control form-control-sm" value="<?php echo isset($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Item input section -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-plus-circle mr-2"></i> Add Items</h5>
                </div>
                <div class="card-body item-input-section">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="indoor" class="form-label icon-label">
                                    <i class="fas fa-home"></i> Indoor
                                </label>
                                <input type="text" class="form-control uppercase-input" id="indoor" placeholder="Enter indoor">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="indoor_serial" class="form-label icon-label">
                                    <i class="fas fa-barcode"></i> Indoor Serial
                                </label>
                                <input type="text" class="form-control uppercase-input" id="indoor_serial" placeholder="Enter indoor serial">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="outdoor" class="form-label icon-label">
                                    <i class="fas fa-tree"></i> Outdoor
                                </label>
                                <input type="text" class="form-control uppercase-input" id="outdoor" placeholder="Enter outdoor">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="outdoor_serial" class="form-label icon-label">
                                    <i class="fas fa-barcode"></i> Outdoor Serial
                                </label>
                                <input type="text" class="form-control uppercase-input" id="outdoor_serial" placeholder="Enter outdoor serial">
                            </div>
                        </div>
                        <!-- Removed series dropdown -->
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="series" class="form-label icon-label">
                                    <i class="fas fa-list-ol"></i> Series
                                </label>
                                <input type="text" class="form-control uppercase-input" id="series" placeholder="Enter series">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type" class="form-label icon-label">
                                    <i class="fas fa-cogs"></i> Type
                                </label>
                                <select id="type" class="custom-select select2">
                                    <option value="" disabled selected>Select Type</option>
                                    <?php 
                                    $types_qry = $conn->query("SELECT DISTINCT `type` FROM `item_list` WHERE `type` IS NOT NULL AND `type` != '' ORDER BY `type` ASC"); 
                                    $all_types = array_unique(array_merge(["Window", "Floor Mounted", "Wall Mounted", "Ceiling", "Cassette"], array_column($types_qry->fetch_all(MYSQLI_ASSOC), 'type'))); 
                                    sort($all_types); 
                                    foreach ($all_types as $type_option) : 
                                    ?>
                                    <option value="<?php echo htmlspecialchars($type_option) ?>"><?php echo htmlspecialchars($type_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
<!-- Change the brand select element to remove the required attribute -->
<div class="col-md-2">
    <div class="form-group">
        <label for="brand" class="form-label icon-label">
            <i class="fas fa-tag"></i> Brand
        </label>
        <select id="brand" class="custom-select select2">
            <option value="" disabled selected>Select Brand</option>
            <?php 
            $brands_qry = $conn->query("SELECT DISTINCT `brand` FROM `item_list` WHERE `brand` IS NOT NULL AND `brand` != '' ORDER BY `brand` ASC"); 
            $all_brands = array_unique(array_merge(["AUFIT", "AUX", "DAIKIN", "GREE", "HK",  "LG", "MATRIX", "MIDEA", "TCL"], array_column($brands_qry->fetch_all(MYSQLI_ASSOC), 'brand'))); 
            sort($all_brands); 
            foreach ($all_brands as $brand_option) : 
            ?>
            <option value="<?php echo htmlspecialchars($brand_option) ?>"><?php echo htmlspecialchars($brand_option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
                        <!-- Added HP textbox after brand -->
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="hp" class="form-label icon-label">
                                    <i class="fas fa-tachometer-alt"></i> HP
                                </label>
                                <input type="text" class="form-control uppercase-input" id="hp" placeholder="HP">
                            </div>
                        </div>
                        <!-- Removed quantity input field -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="item_price" class="form-label icon-label">
                                    <i class="fas fa-money-bill-wave"></i> Price (SRP)
                                </label>
                                <input type="number" step="0.01" class="form-control" id="item_price" placeholder="0.00" min="0">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" class="btn btn-flat btn-primary" id="add_to_list">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Items Table -->
            <div class="card mb-3">
                <div class="card-header bg-navy text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i> Purchase Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0" id="list">
                            <thead>
                                <tr class="text-light bg-navy">
                                    <th class="text-center py-1 px-2" style="width: 5%;"></th>
                                    <!-- Moved Qty column to be second -->
                                    <th class="text-center py-1 px-2" style="width: 8%;"><i class="fas fa-sort-numeric-up mr-1"></i> Qty</th>
                                    <th class="text-center py-1 px-2" style="width: 8%;"><i class="fas fa-home mr-1"></i> Indoor</th>
                                    <th class="text-center py-1 px-2" style="width: 10%;"><i class="fas fa-barcode mr-1"></i> Indoor Serial</th>
                                    <th class="text-center py-1 px-2" style="width: 8%;"><i class="fas fa-tree mr-1"></i> Outdoor</th>
                                    <th class="text-center py-1 px-2" style="width: 10%;"><i class="fas fa-barcode mr-1"></i> Outdoor Serial</th>
                                    <th class="text-center py-1 px-2" style="width: 8%;"><i class="fas fa-ruler mr-1"></i> Unit</th>
                                    <th class="py-1 px-2" style="width: 21%;"><i class="fas fa-info-circle mr-1"></i> Description</th>
                                    <th class="text-right py-1 px-2" style="width: 14%;"><i class="fas fa-money-bill-wave mr-1"></i> Price</th>
                                    <th class="text-right py-1 px-2" style="width: 15%;"><i class="fas fa-calculator mr-1"></i> Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($is_edit && isset($po_data)) : ?>
                                    <?php
                                    // Step 1: Identify the unique PO number from the record we fetched using the ID.
                                    $po_identifier_to_find = $po_data['po'] ?? '';
                                    
                                    $items_for_this_po = [];
                                    // Step 2: If we have a valid PO number, fetch ALL items belonging to it.
                                    if (!empty($po_identifier_to_find)) {
                                        // This is the critical query that solves the problem.
                                        // It ONLY gets items where the `po` column matches our specific PO number.
                                        $items_stmt = $conn->prepare("SELECT * FROM purchase_order_list WHERE po = ?");
                                        if ($items_stmt) {
                                            $items_stmt->bind_param("s", $po_identifier_to_find);
                                            $items_stmt->execute();
                                            $items_result = $items_stmt->get_result();
                                            $items_for_this_po = $items_result->fetch_all(MYSQLI_ASSOC);
                                            $items_stmt->close();
                                        }
                                    }
                                    // Step 3: A safety fallback. If for any reason the query above fails or returns nothing
                                    // (e.g., for a PO with a single item that has no PO number yet),
                                    // we ensure that at least the item fetched by ID is shown.
                                    if (empty($items_for_this_po)) {
                                        $items_for_this_po[] = $po_data;
                                    }
                                    // Step 4: Loop through the correctly filtered list and display the rows.
                                    foreach ($items_for_this_po as $row) :
                                        $brand = !empty($row['brand']) ? htmlspecialchars($row['brand']) : 'N/A';
                                        $hp = !empty($row['hp']) ? htmlspecialchars($row['hp']) : 'N/A';
                                        $type = !empty($row['type']) ? htmlspecialchars($row['type']) : 'N/A';
                                        $series = !empty($row['series']) ? htmlspecialchars($row['series']) : 'N/A';
                                        // Updated description format: 'brand' ('hp' HP) 'type' type, 'series' series
                                        $item_description = $brand . (!empty($hp) && $hp !== 'N/A' ? ' (' . $hp . ' HP)' : '') . (!empty($type) && $type !== 'N/A' ? ' ' . $type . ' type' : '') . (!empty($series) && $series !== 'N/A' ? ', ' . $series . ' series' : '');
                                    ?>
                                        <tr>
                                            <td class="py-1 px-2 text-center">
                                                <button class="btn btn-outline-danger btn-sm rem_row" type="button">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                            <!-- Moved Qty cell to be second -->
                                            <td class="py-1 px-2 text-center qty">
                                                <!-- For existing items, show the actual quantity from database -->
                                                <span class="visible"><?php echo number_format(floatval($row['quantity'] ?? 0)); ?></span>
                                                <input type="hidden" name="item_id[]" value="<?php echo htmlspecialchars($row['item_id'] ?? '1'); ?>">
                                                <input type="hidden" name="qty[]" value="<?php echo floatval($row['quantity'] ?? 0); ?>">
                                                <input type="hidden" name="price[]" value="<?php echo floatval($row['price'] ?? 0); ?>">
                                                <input type="hidden" name="total[]" value="<?php echo floatval($row['total'] ?? 0); ?>">
                                                <input type="hidden" name="indoor_outdoor[]" value="<?php echo htmlspecialchars($row['indoor_outdoor'] ?? ''); ?>">
                                                <input type="hidden" name="brand[]" value="<?php echo $brand; ?>">
                                                <input type="hidden" name="type[]" value="<?php echo htmlspecialchars($row['type'] ?? ''); ?>">
                                                <input type="hidden" name="hp[]" value="<?php echo $hp; ?>">
                                                <input type="hidden" name="indoor_serial[]" value="<?php echo !empty($row['indoor_serial']) ? htmlspecialchars($row['indoor_serial']) : 'N/A'; ?>">
                                                <input type="hidden" name="outdoor_serial[]" value="<?php echo !empty($row['outdoor_serial']) ? htmlspecialchars($row['outdoor_serial']) : 'N/A'; ?>">
                                                <input type="hidden" name="series[]" value="<?php echo htmlspecialchars($row['series'] ?? ''); ?>">
                                                <input type="hidden" name="indoor[]" value="<?php echo !empty($row['indoor']) ? htmlspecialchars($row['indoor']) : 'N/A'; ?>">
                                                <input type="hidden" name="outdoor[]" value="<?php echo !empty($row['outdoor']) ? htmlspecialchars($row['outdoor']) : 'N/A'; ?>">
                                                <!-- Add hidden input for delivery date -->
                                                <input type="hidden" name="item_date[]" value="<?php echo isset($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d'); ?>">
                                            </td>
                                            <td class="py-1 px-2 text-center indoor"><?php echo !empty($row['indoor']) ? htmlspecialchars($row['indoor']) : 'N/A'; ?></td>
                                            <td class="py-1 px-2 text-center indoor_serial"><?php echo !empty($row['indoor_serial']) ? htmlspecialchars($row['indoor_serial']) : 'N/A'; ?></td>
                                            <td class="py-1 px-2 text-center outdoor"><?php echo !empty($row['outdoor']) ? htmlspecialchars($row['outdoor']) : 'N/A'; ?></td>
                                            <td class="py-1 px-2 text-center outdoor_serial"><?php echo !empty($row['outdoor_serial']) ? htmlspecialchars($row['outdoor_serial']) : 'N/A'; ?></td>
                                            <!-- Changed to display "SETS" instead of HP value -->
                                            <td class="py-1 px-2 text-center unit">SETS</td>
                                            <td class="py-1 px-2 item"><?php echo $item_description; ?></td>
                                            <td class="py-1 px-2 text-right price"><?php echo number_format(floatval($row['price'] ?? 0), 2); ?></td>
                                            <td class="py-1 px-2 text-right total"><?php echo number_format(floatval($row['total'] ?? 0), 2); ?></td>
                                        </tr>
                                    <?php 
                                    endforeach; 
                                    ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th class="text-right py-2 px-2" colspan="9"><strong><i class="fas fa-calculator mr-1"></i> Sub Total</strong></th>
                                    <th class="text-right py-2 px-2 sub-total font-weight-bold">0.00</th>
                                </tr>
                                <tr>
                                    <th class="text-right py-2 px-2" colspan="9">
                                        <strong><i class="fas fa-percentage mr-1"></i> Discount</strong> <input style="width:60px !important" name="discount_perc" type="number" min="0" max="100" value="<?php echo isset($discount_perc) ? $discount_perc : 0 ?>" class="form-control-sm">%
                                    </th>
                                    <th class="text-right py-2 px-2 discount font-weight-bold text-danger">0.00</th>
                                </tr>
                                <tr>
                                    <th class="text-right py-2 px-2" colspan="9">
                                        <strong><i class="fas fa-receipt mr-1"></i> Tax</strong> <input style="width:60px !important" name="tax_perc" type="number" min="0" max="100" value="<?php echo isset($tax_perc) ? $tax_perc : 0 ?>" class="form-control-sm">%
                                    </th>
                                    <th class="text-right py-2 px-2 tax font-weight-bold text-success">0.00</th>
                                </tr>
                                <tr class="bg-primary text-white">
                                    <th class="text-right py-2 px-2" colspan="9"><strong><i class="fas fa-coins mr-1"></i> GRAND TOTAL</strong></th>
                                    <th class="text-right py-2 px-2 grand-total font-weight-bold" style="font-size: 1.1em;">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                        <input type="hidden" name="sub_total" value="0">
                        <input type="hidden" name="discount" value="0">
                        <input type="hidden" name="tax" value="0">
                        <input type="hidden" name="amount" value="0">
                    </div>
                </div>
            </div>
            <!-- Additional Information -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-cog mr-2"></i> Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="remarks" class="text-info control-label icon-label">
                                    <i class="fas fa-comment-alt"></i> Remarks
                                </label>
                                <textarea name="remarks" id="remarks" rows="4" class="form-control uppercase-input" placeholder="Enter any remarks or notes..."><?php echo isset($remarks) ? htmlspecialchars($remarks) : '' ?></textarea>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="card-footer py-2 text-center">
    <button class="btn btn-flat btn-success btn-lg" type="submit" form="po-form">
        <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Update Purchase Order' : 'Save Purchase Order' ?>
    </button>
    <a class="btn btn-flat btn-secondary btn-lg" href="<?php echo base_url . '/admin?page=purchase_order' ?>">
        <i class="fas fa-times mr-2"></i> Cancel
    </a>
</div>
</div>
<!-- Clone template for new items -->
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button">
                <i class="fa fa-times"></i>
            </button>
        </td>
        <!-- Moved Qty cell to be second -->
        <td class="py-1 px-2 text-center qty">
            <!-- For new items, always display 1 -->
            <span class="visible">1</span>
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="qty[]" value="1">
            <input type="hidden" name="price[]">
            <input type="hidden" name="total[]">
            <input type="hidden" name="indoor_outdoor[]">
            <input type="hidden" name="brand[]">
            <input type="hidden" name="type[]">
            <input type="hidden" name="hp[]">
            <input type="hidden" name="indoor_serial[]">
            <input type="hidden" name="outdoor_serial[]">
            <input type="hidden" name="series[]">
            <input type="hidden" name="indoor[]">
            <input type="hidden" name="outdoor[]">
            <!-- Add hidden input for delivery date in clone template -->
            <input type="hidden" name="item_date[]" value="<?php echo date('Y-m-d'); ?>">
        </td>
        <td class="py-1 px-2 text-center indoor"></td>
        <td class="py-1 px-2 text-center indoor_serial"></td>
        <td class="py-1 px-2 text-center outdoor"></td>
        <td class="py-1 px-2 text-center outdoor_serial"></td>
        <!-- Changed to display "SETS" instead of HP value -->
        <td class="py-1 px-2 text-center unit">SETS</td>
        <td class="py-1 px-2 item"></td>
        <td class="py-1 px-2 text-right price"></td>
        <td class="py-1 px-2 text-right total"></td>
    </tr>
</table>
<script>
    // Define the base URL variable
    var _base_url_ = '<?php echo base_url; ?>';
    
    // Define alert_toast function
    function alert_toast(message, type) {
        // Remove any existing toasts
        $('.custom-toast').remove();
        
        // Create toast element
        var toast = $('<div class="custom-toast ' + type + '">');
        toast.html(message);
        toast.append('<span class="close-toast">&times;</span>');
        
        // Add to body
        $('body').append(toast);
        
        // Close on click
        toast.find('.close-toast').on('click', function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    function rem(_this) { 
        _this.closest('tr').remove(); 
        calc(); 
    }
    
    function calc() { 
        var sub_total = 0; 
        $('table#list tbody tr').each(function() { 
            sub_total += parseFloat($(this).find('[name="total[]"]').val()) || 0; 
        }); 
        
        var discount_perc = parseFloat($('[name="discount_perc"]').val()) || 0; 
        var tax_perc = parseFloat($('[name="tax_perc"]').val()) || 0; 
        var discount = sub_total * (discount_perc / 100); 
        var tax = (sub_total - discount) * (tax_perc / 100); 
        var grand_total = sub_total - discount + tax; 
        
        $('[name="sub_total"]').val(sub_total.toFixed(2)); 
        $('[name="discount"]').val(discount.toFixed(2)); 
        $('[name="tax"]').val(tax.toFixed(2)); 
        $('[name="amount"]').val(grand_total.toFixed(2)); 
        
        $('table#list tfoot .sub-total').text(sub_total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })); 
        $('table#list tfoot .discount').text(discount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })); 
        $('table#list tfoot .tax').text(tax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })); 
        $('table#list tfoot .grand-total').text(grand_total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })); 
    }
    
    // Function to show success message
    function showSuccessMessage(message) {
        // Remove any existing success message
        $('.success-message').remove();
        
        // Create and show the success message
        const successDiv = $('<div class="success-message">' + message + '</div>');
        $('body').append(successDiv);
        successDiv.fadeIn();
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            successDiv.fadeOut(() => {
                successDiv.remove();
            });
        }, 3000);
    }
    
    $(function() {
        $('.select2').select2({ placeholder: "Select Here", width: 'resolve' });
        
        // Refresh button click event
        $('#refresh-btn').on('click', function() {
            // Add spinning animation to the refresh icon
            $(this).addClass('refreshing');
            
            // If we're in edit mode, reload the page to get fresh data
            if ('<?php echo $is_edit; ?>' == '1') {
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                // Reset form fields
                $('#po-form')[0].reset();
                
                // Clear any added items in the table
                $('#list tbody').empty();
                
                // Reset calculations
                calc();
                
                // Reset select2 dropdowns
                $('#supplier_id, #type, #brand').val(null).trigger('change');
                
                // Remove spinning animation after a short delay
                setTimeout(() => {
                    $(this).removeClass('refreshing');
                }, 800);
                
                // Show success message
                showSuccessMessage('Form refreshed successfully');
            }
        });
        
        // Update item dates when delivery date changes
        $('#delivery_date').on('change', function() {
            var newDate = $(this).val();
            // Update all existing item rows
            $('input[name="item_date[]"]').val(newDate);
            // Also update the clone template
            $('#clone_list input[name="item_date[]"]').val(newDate);
        });
        
// Update the add_to_list click handler to validate brand selection
 $('#add_to_list').click(function() {
    var indoor = $('#indoor').val().trim();
    var indoor_serial = $('#indoor_serial').val().trim();
    var outdoor = $('#outdoor').val().trim();
    var outdoor_serial = $('#outdoor_serial').val().trim();
    var series = $('#series').val().trim();
    var type = $('#type').val();
    var brand = $('#brand').val();
    var hp = $('#hp').val().trim();
    var price = parseFloat($('#item_price').val()) || 0;
    
    // Validate that brand is selected
    if (!brand || brand === '') {
        alert_toast('<i class="fas fa-exclamation-circle mr-2"></i>Please select a brand before adding to the list.', 'warning');
        $('#brand').select2('open');
        return false;
    }
    
    // Set blank fields to "N/A"
    indoor = indoor || 'N/A';
    indoor_serial = indoor_serial || 'N/A';
    outdoor = outdoor || 'N/A';
    outdoor_serial = outdoor_serial || 'N/A';
    series = series || 'N/A';
    type = type || 'N/A';
    hp = hp || 'N/A';
    
    // Convert all text fields to uppercase
    indoor = indoor.toUpperCase();
    indoor_serial = indoor_serial.toUpperCase();
    outdoor = outdoor.toUpperCase();
    outdoor_serial = outdoor_serial.toUpperCase();
    series = series.toUpperCase();
    type = type.toUpperCase();
    brand = brand.toUpperCase();
    hp = hp.toUpperCase();
    
    $(this).prop('disabled', true);
    
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=get_or_create_item", 
        method: 'POST', 
        data: { brand: brand, type: type, hp: hp, price: price }, 
        dataType: 'json',
        success: function(resp) {
            if (resp.status === 'success' && resp.item_id) {
                var tr = $('#clone_list tr').clone();
                tr.find('[name="item_id[]"]').val(resp.item_id);
                // Set quantity to 1 for all new items
                tr.find('[name="qty[]"]').val(1);
                tr.find('[name="price[]"]').val(price);
                tr.find('[name="total[]"]').val(1 * price); // Quantity is always 1
                tr.find('[name="indoor_outdoor[]"]').val(''); // Clear indoor_outdoor since we're not using it
                tr.find('[name="brand[]"]').val(brand);
                tr.find('[name="type[]"]').val(type);
                tr.find('[name="hp[]"]').val(hp);
                tr.find('[name="indoor_serial[]"]').val(indoor_serial);
                tr.find('[name="outdoor_serial[]"]').val(outdoor_serial);
                // Store series value
                tr.find('[name="series[]"]').val(series);
                tr.find('[name="indoor[]"]').val(indoor);
                tr.find('[name="outdoor[]"]').val(outdoor);
                // Set the delivery date from the current delivery date input
                tr.find('[name="item_date[]"]').val($('#delivery_date').val());
                
                tr.find('.qty .visible').text(1);
                tr.find('.indoor').text(indoor);
                tr.find('.indoor_serial').text(indoor_serial);
                tr.find('.outdoor').text(outdoor);
                tr.find('.outdoor_serial').text(outdoor_serial);
                // Changed to display "SETS" instead of HP value
                tr.find('.unit').text("SETS");
                // Updated description format: 'brand' ('hp' HP) 'type' type, 'series' series
                tr.find('.item').text(`${brand} (${hp} HP) ${type} type, ${series} series`);
                tr.find('.price').text(price.toLocaleString('en-US', { minimumFractionDigits: 2 }));
                tr.find('.total').text((1 * price).toLocaleString('en-US', { minimumFractionDigits: 2 })); // Quantity is always 1
                
                $('table#list tbody').append(tr);
                tr.find('.rem_row').click(function() { rem($(this)); });
                calc();
                
                // Reset form
                $('#indoor, #outdoor, #indoor_serial, #outdoor_serial, #series, #hp, #item_price').val('');
                $('#type, #brand').val('').trigger('change');
                $('#indoor').focus();
            } else { 
                alert_toast('<i class="fas fa-exclamation-circle mr-2"></i>' + (resp.msg || 'An unknown error occurred.'), 'error'); 
            }
        },
        error: function(err) { 
            console.error(err); 
            alert_toast('<i class="fas fa-exclamation-circle mr-2"></i>An error occurred while fetching item data.', 'error'); 
        },
        complete: function() { 
            $('#add_to_list').prop('disabled', false); 
        }
    });
});
        
        $('#po-form').submit(function(e) {
            e.preventDefault();
            
            // Validate supplier selection
            var supplier_id = $('#supplier_id').val();
            if (!supplier_id) {
                alert_toast('<i class="fas fa-exclamation-circle mr-2"></i>Please select a supplier.', 'warning');
                return false;
            }
            
            // Set blank text fields to "N/A" before submitting
            $(this).find('input[type="text"], textarea').each(function() {
                if ($(this).val().trim() === '') {
                    $(this).val('N/A');
                }
            });
            
            if ($('table#list tbody tr').length <= 0) { 
                alert_toast('<i class="fas fa-exclamation-circle mr-2"></i>Please add at least one item to the list.', 'warning'); 
                return false; 
            }
            
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_po", 
                data: new FormData($(this)[0]), 
                cache: false, 
                contentType: false, 
                processData: false, 
                method: 'POST', 
                dataType: 'json',
                success: function(resp) {
                    if (resp.status == 'success') { 
                        location.href = _base_url_ + "admin/?page=purchase_order/index&id=" + resp.id; 
                    } else if (resp.msg) { 
                        var el = $('<div>').addClass("alert alert-danger err-msg").html('<i class="fas fa-exclamation-circle mr-2"></i>' + resp.msg); 
                        $('#po-form').prepend(el); 
                        el.show('slow'); 
                        $('html,body').animate({ scrollTop: 0 }, 'fast'); 
                    } else { 
                        alert_toast("<i class='fas fa-exclamation-circle mr-2'></i>An error occurred", 'error'); 
                        console.log(resp); 
                    }
                    end_loader();
                },
                error: function(err) { 
                    console.log(err); 
                    alert_toast("<i class='fas fa-exclamation-circle mr-2'></i>An error occurred", 'error'); 
                    end_loader(); 
                }
            });
        });
        
        if ('<?php echo isset($id) && $id > 0 ?>' == '1') { 
            calc(); 
            $('#supplier_id').attr('readonly', 'readonly'); 
            $('table#list tbody .rem_row').click(function() { rem($(this)); }); 
        }
        
        $('[name="discount_perc"], [name="tax_perc"]').on('input', calc);
    });
</script>
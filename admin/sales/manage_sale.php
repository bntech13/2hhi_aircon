<?php
// Generate invoice number for new sales
if (!isset($id)) {
    // Check if the sales_list table exists
    $tableExists = false;
    $result = $conn->query("SHOW TABLES LIKE 'sales_list'");
    if ($result && $result->num_rows > 0) {
        $tableExists = true;
    }
    
    if ($tableExists) {
        // Get the highest invoice number from the database
        // Handle both formats: with and without "SI-" prefix
        $qry = $conn->query("SELECT 
            MAX(CAST(
                CASE 
                    WHEN invoice_number LIKE 'SI-%' THEN SUBSTRING(invoice_number, 4)
                    WHEN invoice_number LIKE 'INV-%' THEN SUBSTRING(invoice_number, 5)
                    ELSE invoice_number
                END AS UNSIGNED
            )) as max_order 
        FROM `sales_list`");
        
        if ($qry) {
            $row = $qry->fetch_assoc();
            $next_order = ($row['max_order'] ?? 0) + 1; // Default to 1 if no records exist
        } else {
            // If query fails, start from 1
            $next_order = 1;
        }
    } else {
        // Table doesn't exist, start from 1
        $next_order = 1;
    }
    
    // Format the invoice number with "SI-" prefix and 5 leading zeros
    $invoice_number = "SI-" . str_pad($next_order, 5, '0', STR_PAD_LEFT);
    
    // Format the transaction number with "TRX-" prefix and 7 leading zeros using the same numeric value
    $transaction_number = "" . str_pad($next_order, 7, '0', STR_PAD_LEFT);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sale</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <style>
        /* All existing CSS remains unchanged */
        
        /* New styles */
        .brand-items-container {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .brand-items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .brand-items-table th {
            background-color: #021324ff;
            color: white;
            padding: 12px 10px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 5;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        
        .brand-items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }
        
        .brand-items-table tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        
        .brand-items-table tr.selected {
            background-color: #e3f2fd;
            border-left: 3px solid #2196F3;
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top-color: #0066cc;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Modal styling */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(3, 5, 62, 1);
        }
        
        .modal-header {
            background-color: #0066cc;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        /* Button styling */
        .btn-flat {
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .btn-flat:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Table cell alignment */
        .brand-items-table td:nth-child(1) {
            font-weight: 600;
            color: #021324ff;
        }
        
        .brand-items-table td:nth-child(2) {
            font-family: monospace;
            font-weight: 500;
            color: #333;
        }
        
        .brand-items-table td:nth-child(6) {
            text-align: right;
            font-weight: 600;
            color: #2e7d32;
        }
        
        .brand-items-table td:nth-child(3) {
            text-transform: capitalize;
            font-weight: 500;
        }
        
        .brand-items-table td:nth-child(4) {
            text-transform: capitalize;
            font-weight: 500;
        }
        
        .brand-items-table td:nth-child(5) {
            text-transform: capitalize;
            font-weight: 500;
            color: #0066cc;
        }
        
        /* Badge styling for indoor/outdoor */
        .indoor-badge {
            background-color: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .outdoor-badge {
            background-color: #FF9800;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .brand-items-container {
                max-height: 250px;
            }
            
            .brand-items-table th,
            .brand-items-table td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }
        }
        
        /* Products modal table */
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        .products-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .products-table tr:hover {
            background-color: #e3f2fd; /* Light blue highlight on hover */
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        /* Updated: Make selected rows bold */
        .products-table tr.selected {
            background-color: #e3f2fd;
            border-left: 3px solid #2196F3;
            font-weight: bold; /* Added this line */
        }
        
        /* Checkbox styling */
        .product-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        
        /* Search bar styling */
        .search-container {
            margin-bottom: 15px;
        }
        
        .search-input-wrapper {
            position: relative;
            width: 100%;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 40px 10px 10px; /* Add right padding for the icon */
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #0066cc;
            cursor: pointer;
            z-index: 2;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 5px rgba(0, 102, 204, 0.3);
        }
        
        /* Error message styling */
        .error-message {
            color: #dc3545;
            font-weight: 500;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        /* Success message styling */
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 9999;
            display: none;
            animation: fadeInOut 3s ease-in-out;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        
        /* Form field styling */
        .form-field-row {
            margin-bottom: 15px;
        }
        
        .form-field-row .form-group {
            margin-bottom: 0;
        }
        
        /* Search section styling */
        .search-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .search-section h6 {
            color: #0066cc;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        /* Highlight matching text */
        .highlight {
            background-color: #ffeb3b;
            font-weight: bold;
        }
        
        /* Auto-open modal styling */
        .auto-modal {
            display: block;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        /* Added for product availability status */
        .unavailable-product {
            opacity: 0.5;
            background-color: #f5f5f5;
        }
        
        .unavailable-badge {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 8px;
        }
        
        /* Custom table styling for sale items */
        #list th {
            background-color: #021324ff;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        #list td {
            vertical-align: middle;
        }
        
        #list tfoot th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        /* Input styling in table */
        #list input[type="number"] {
            width: 50px;
            text-align: center;
        }
        
        /* Responsive table */
        @media (max-width: 768px) {
            #list {
                font-size: 0.85rem;
            }
            
            #list th, #list td {
                padding: 0.5rem;
            }
        }
        
        /* Total calculation styling */
        .total-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .grand-total-row {
            background-color: #e9ecef;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        /* Updated styling for black text in total rows */
        #list tfoot .total-row th,
        #list tfoot .total-row td,
        #list tfoot .grand-total-row th,
        #list tfoot .grand-total-row td {
            color: black;
        }
        
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
        
        /* Discount input styling */
        .discount-input {
            width: 50px;
            text-align: center;
            padding: 4px;
            font-size: 0.9rem;
        }
        
        /* Percentage input styling */
        .percentage-input {
            width: 60px;
            text-align: center;
            padding: 4px;
            font-size: 0.9rem;
        }
        
        /* Per-item discount styling */
        .per-item-discount-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .per-item-discount-input {
            width: 40px;
            text-align: center;
            padding: 2px;
            font-size: 0.8rem;
        }
        
        .per-item-discount-label {
            margin-left: 2px;
            font-size: 0.8rem;
            color: #666;
        }
        
        /* Updated: Make product values bold in main table */
        #list td.item {
            font-weight: bold;
        }
        
        #list td.indoor,
        #list td.indoor_serial,
        #list td.outdoor,
        #list td.outdoor_serial {
            font-weight: bold;
        }
        
        #list td.price,
        #list td.total {
            font-weight: bold;
        }
        
        /* Updated: Make qty, unit, and discount values bold */
        #list td.qty {
            font-weight: bold;
        }
        
        #list td.unit {
            font-weight: bold;
        }
        
        #list td.discount {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
            <h6 style="color: #0066cc; margin: 0;">
                <i class="fas fa-plus-circle"></i> <b>MANAGE SALE</b>
            </h6>
        </div>
        
        <button id="refresh-btn" class="btn btn-outline-primary btn-flat" style="display: flex; align-items: center;">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
    
    <div class="card-body">
        <form action="" id="sale-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="invoice_number" class="control-label text-info">Sales Invoice No.</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                </div>
                                <input type="text" name="invoice_number" readonly class="form-control form-control-sm bg-gray" value="<?php echo isset($invoice_number) ? $invoice_number : '' ?>" placeholder="Auto-generated">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="transaction_number" class="control-label text-info">Transaction No.</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                                </div>
                                <input type="text" name="transaction_number" readonly class="form-control form-control-sm bg-gray" value="<?php echo isset($transaction_number) ? $transaction_number : '' ?>" placeholder="Auto-generated">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="client" class="control-label text-info">Customer Name</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="client" class="form-control form-control-sm" value="<?php echo isset($client) ? $client : '' ?>" placeholder="Enter customer name">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date" class="control-label text-info">Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="date" class="form-control form-control-sm" value="<?php echo isset($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                
                <!-- Search Section -->
                <div class="search-section">
                    <h6><i class="fas fa-search"></i> Search Products</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="search-input-wrapper">
                                <input type="text" id="product-search" class="search-input" placeholder="Search by brand, indoor, indoor_serial, outdoor, outdoor_serial...">
                                <i class="fas fa-search search-icon" id="search-icon-btn"></i>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <button type="button" class="btn btn-flat btn-info rounded-pill w-100" id="display_products">
                                <i class="fas fa-list"></i> Display All Products
                            </button>
                        </div>
                    </div>
                </div>
                
                <hr>
                <table class="table table-striped table-bordered" id="list">
                    <colgroup>
                        <col width="5%">
                        <col width="5%"> <!-- Qty column -->
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="20%">
                        <col width="10%">
                        <col width="10%"> <!-- Discount column -->
                        <col width="10%">
                    </colgroup>
                    <thead>
                        <tr class="text-light bg-navy">
                            <th class="text-center py-1 px-2"></th>
                            <th class="text-center py-1 px-2">Qty</th>
                            <th class="text-center py-1 px-2">Indoor</th>
                            <th class="text-center py-1 px-2">Indoor Serial</th>
                            <th class="text-center py-1 px-2">Outdoor</th>
                            <th class="text-center py-1 px-2">Outdoor Serial</th>
                            <th class="text-center py-1 px-2">Unit</th>
                            <th class="text-center py-1 px-2">Description</th>
                            <th class="text-center py-1 px-2">Price</th>
                            <th class="text-center py-1 px-2">Discount (%)</th>
                            <th class="text-center py-1 px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
<?php 
 $total = 0;
if(isset($id)):
 $qry = $conn->query("SELECT s.*,i.brand,i.type,i.hp,s.indoor_outdoor FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
while($row = $qry->fetch_assoc()):
    // Calculate total as quantity * price
    $row_total = $row['quantity'] * $row['price'];
    $total += $row_total;
    
    // Format description in uppercase: DAIKIN (HP) , TYPE TYPE, SERIES SERIES
    $description = strtoupper($row['brand']) . ' (' . $row['hp'] . ' HP) , ' . strtoupper($row['type']) . ' TYPE';
    if (!empty($row['series'])) {
        $description .= ', ' . strtoupper($row['series']) . ' SERIES';
    }
?>
<tr>
    <td class="py-1 px-2 text-center">
        <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
    </td>
    <td class="py-1 px-2 text-center qty">
        <?php echo $row['quantity'] ?>
    </td>
    <td class="py-1 px-2 text-center indoor">
        <?php echo $row['indoor'] ?? '' ?>
    </td>
    <td class="py-1 px-2 text-center indoor_serial">
        <?php echo $row['indoor_serial'] ?? '' ?>
    </td>
    <td class="py-1 px-2 text-center outdoor">
        <?php echo $row['outdoor'] ?? '' ?>
    </td>
    <td class="py-1 px-2 text-center outdoor_serial">
        <?php echo $row['outdoor_serial'] ?? '' ?>
    </td>
    <td class="py-1 px-2 text-center unit">
        <?php echo $row['unit'] ?>
    </td>
    <td class="py-1 px-2 item">
        <?php echo $description ?>
    </td>
    <td class="py-1 px-2 text-right price">
        <?php echo number_format($row['price'], 2) ?>
    </td>
    <td class="py-1 px-2 text-center discount">
        <div class="per-item-discount-container">
            <input type="number" name="row_perc[]" class="form-control form-control-sm per-item-discount-input" min="0" max="100" step="1" value="<?php echo isset($row['row_perc']) ? $row['row_perc'] : 0 ?>">
            <span class="per-item-discount-label">%</span>
        </div>
    </td>
    <td class="py-1 px-2 text-right total">
        <?php echo number_format($row_total, 2) ?>
    </td>
</tr>
<?php endwhile; endif; ?>
                    </tbody>
<!-- Update the table footer in the HTML -->
<tfoot>
    <!-- Added spacing row above Sub Total -->
    <tr>
        <td colspan="11" style="height: 20px;"></td>
    </tr>
    
    <tr class="total-row">
        <th class="text-right py-1 px-2" colspan="10">Sub Total</th>
        <th class="text-right py-1 px-2 sub-total"><?php echo number_format($total, 2) ?></th>
    </tr>
    <tr class="total-row">
        <th class="text-right py-1 px-2" colspan="10">Less: VAT</th>
        <th class="text-right py-1 px-2 vat-amount"><?php echo isset($total) ? number_format($total - ($total / 1.12), 2) : '0.00' ?></th>
        <input type="hidden" name="vat" value="<?php echo isset($vat) ? $vat : 0 ?>">
    </tr>
    <tr class="total-row">
        <th class="text-right py-1 px-2" colspan="10">Amount: Net of VAT</th>
        <th class="text-right py-1 px-2 net-vat"><?php echo isset($total) ? number_format($total / 1.12, 2) : '0.00' ?></th>
    </tr>

        <tr>
        <td colspan="11" style="height: 20px;"></td>
    </tr>

    <tr class="total-row">
        <th class="text-right py-1 px-2" colspan="10">Less: Discount </th>
        <th class="text-right py-1 px-2 discount-amount">0.00</th>
    </tr>
    <tr class="total-row">
        <th class="text-right py-1 px-2" colspan="10">Less: Withholding Tax <input style="width:60px !important" name="tax_perc" class='percentage-input' type="number" min="0" max="100" step="1" value="<?php echo isset($tax_perc) ? $tax_perc : 0 ?>">%</th>
        <th class="text-right py-1 px-2 tax"><?php echo isset($tax) ? number_format($tax, 2) : 0 ?></th>
        <input type="hidden" name="tax" value="<?php echo isset($tax) ? $tax : 0 ?>">
    </tr>
    <tr class="grand-total-row">
        <th class="text-right py-1 px-2" colspan="10">GRAND Total:
            <input type="hidden" name="amount" value="<?php echo isset($amount) ? $amount : 0 ?>">
        </th>
        <th class="text-right py-1 px-2 grand-total"><?php echo isset($amount) ? number_format($amount, 2) : 0 ?></th>
    </tr>
</tfoot>

</table>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks" class="control-label text-info">Remarks</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                                </div>
                                <textarea name="remarks" id="remarks" rows="3" class="form-control" placeholder="Enter any remarks or notes..."><?php echo isset($remarks) ? $remarks : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer py-1">
    <div class="d-flex justify-content-center">
        <button class="btn btn-flat btn-primary mr-2" type="submit" form="sale-form">
            <i class="fas fa-save"> </i> &nbsp; Save
        </button>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin/?page=sales' ?>">
            <i class="fas fa-times"></i> &nbsp; Cancel
        </a>
    </div>
</div>

<!-- Products Modal -->
<div class="modal fade" id="productsModal" tabindex="-1" role="dialog" aria-labelledby="productsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productsModalLabel">Available Products</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="products-error-container"></div>
                <div class="table-responsive">
                    <table class="table products-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell"></th>
                                <th>Brand</th>
                                <th>Type</th>
                                <th>HP</th>
                                <th>Series</th>
                                <th>Indoor</th>
                                <th>Indoor Serial</th>
                                <th>Outdoor</th>
                                <th>Outdoor Serial</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="products_tbody">
                            <!-- Products will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="add-selected-products">
                    <i class="fas fa-plus"></i> Add Selected Products
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
<div id="success-message" class="success-message">
    <i class="fas fa-check-circle"></i> Product added successfully!
</div>

<!-- Updated Template Row with all necessary fields -->
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center qty">
            <input type="hidden" name="qty[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-center indoor">
            <input type="hidden" name="indoor[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-center indoor_serial">
            <input type="hidden" name="indoor_serial[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-center outdoor">
            <input type="hidden" name="outdoor[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-center outdoor_serial">
            <input type="hidden" name="outdoor_serial[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-center unit">
            <input type="hidden" name="unit[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 item">
            <input type="hidden" name="brand[]">
            <input type="hidden" name="type[]">
            <input type="hidden" name="hp[]">
            <input type="hidden" name="series[]">
            <input type="hidden" name="item_price[]">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-right price">
            <span class="visible"></span>
        </td>
        <td class="py-1 px-2 text-center discount">
            <input type="hidden" name="row_perc[]">
            <div class="per-item-discount-container">
                <input type="number" class="form-control form-control-sm per-item-discount-input" min="0" max="100" step="1" value="0">
                <span class="per-item-discount-label">%</span>
            </div>
        </td>
        <td class="py-1 px-2 text-right total">
            <input type="hidden" name="total_amount[]">
            <span class="visible"></span>
        </td>
    </tr>
</table>

<script>
    // Define the base URL if it's not already defined
    if (typeof _base_url_ === 'undefined') {
        // Try to determine the base URL from the current location
        var pathArray = window.location.pathname.split('/');
        var newPathname = "";
        for (var i = 0; i < pathArray.length - 1; i++) {
            newPathname += pathArray[i];
            newPathname += "/";
        }
        _base_url_ = window.location.origin + newPathname;
        console.log("Base URL automatically set to:", _base_url_);
    }

    // Variable to store all products for filtering
    let allProducts = null;
    
    // Variable to track if modal is already open
    let isModalOpen = false;
    
    // Set to track added products
    let addedProducts = new Set();

    // Fallback function for when products can't be loaded
    function getReceivingStocksFallback() {
        var tbody = $('#products_tbody');
        var errorContainer = $('#products-error-container');
        
        tbody.empty();
        errorContainer.empty();
        
        errorContainer.html('<div class="error-message">Unable to load products. Please try again later or contact support.</div>');
        console.error("The get_all_purchase_order_stocks function might not exist or there's a server error.");
    }

    // Function to highlight matching text
    function highlightText(text, searchTerm) {
        if (!searchTerm) return text;
        
        var regex = new RegExp('(' + searchTerm + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    // Function to fetch and display products based on search term
    function fetchAndDisplayProducts(searchTerm, showAll = false) {
        // Show the modal if not already open
        if (!isModalOpen) {
            $('#productsModal').modal('show');
            isModalOpen = true;
        }
        
        // Clear any previous errors
        $('#products-error-container').empty();
        
        // Show loading indicator
        var tbody = $('#products_tbody');
        tbody.empty();
        tbody.append('<tr><td colspan="11" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        // If we already have the products data, filter and display
        if (allProducts) {
            filterAndDisplayProducts(allProducts, searchTerm, showAll);
            return;
        }
        
        // Check if the function exists before making the AJAX call
        if (typeof _base_url_ === 'undefined') {
            console.error("_base_url_ is not defined");
            getReceivingStocksFallback();
            return;
        }
        
        // Log the request URL for debugging
        var requestUrl = _base_url_ + "classes/Master.php?f=get_all_purchase_order_stocks";
        console.log("Request URL:", requestUrl);
        
        // Fetch products data from receiving_list
        $.ajax({
            url: requestUrl,
            method: "GET",
            dataType: "json",
            beforeSend: function(xhr) {
                // Set content type to ensure we get JSON
                xhr.setRequestHeader('Accept', 'application/json');
            },
            success: function(resp) {
                console.log("Server response:", resp);
                
                // Store the products data for future filtering
                if (resp.status === 'success' && resp.data && resp.data.length > 0) {
                    allProducts = resp.data;
                }
                
                // Filter and display the products
                filterAndDisplayProducts(resp.status === 'success' ? resp.data : [], searchTerm, showAll);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log("Status:", xhr.status);
                console.log("Response Text:", xhr.responseText);
                console.log("Response Headers:", xhr.getAllResponseHeaders());
                
                tbody.empty();
                
                // Check if the error is because the method doesn't exist
                if (xhr.responseText.indexOf("Call to undefined method Master::get_all_purchase_order_stocks()") !== -1) {
                    $('#products-error-container').html('<div class="error-message">The get_all_purchase_order_stocks method is not implemented yet. Please contact your system administrator.</div>');
                } else if (xhr.status === 200 && xhr.responseText.trim() === '') {
                    // Empty response
                    $('#products-error-container').html('<div class="error-message">The server returned an empty response. Please check the server logs.</div>');
                } else if (xhr.status === 200) {
                    // We got a 200 OK but it's not valid JSON
                    try {
                        // Try to parse as HTML to extract error message
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(xhr.responseText, 'text/html');
                        var errorTitle = doc.querySelector('b') ? doc.querySelector('b').textContent : 'Server Error';
                        $('#products-error-container').html('<div class="error-message"><strong>Server Error:</strong> ' + errorTitle + '<br><small>Please check your PHP error log or contact your system administrator.</small></div>');
                    } catch (e) {
                        $('#products-error-container').html('<div class="error-message">Invalid server response. Please check the console for details.</div>');
                    }
                } else {
                    // Other HTTP errors
                    $('#products-error-container').html('<div class="error-message">HTTP Error ' + xhr.status + ': ' + error + '<br><small>Please check the server logs or contact your system administrator.</small></div>');
                }
                
                // If we get a 404 or similar error, try the fallback
                if (xhr.status === 404) {
                    getReceivingStocksFallback();
                }
            }
        });
    }
    
    // Function to filter and display products based on search term
    function filterAndDisplayProducts(products, searchTerm, showAll = false) {
        var tbody = $('#products_tbody');
        tbody.empty();
        
        if (!products || products.length === 0) {
            tbody.append('<tr><td colspan="11" class="text-center">No products found.</td></tr>');
            return;
        }
        
        var hasResults = false;
        var searchLower = searchTerm ? searchTerm.toLowerCase() : '';
        
        // Only show products if there's a search term or showAll is explicitly true
        if (!searchTerm && !showAll) {
            tbody.append('<tr><td colspan="11" class="text-center">Enter a search term to find products or click "Display Products" to show all.</td></tr>');
            return;
        }
        
        products.forEach(function(product) {
            // Create a unique identifier for the product
            var uniqueId = (product.brand || '') + '-' + 
                          (product.type || '') + '-' + 
                          (product.hp || '') + '-' + 
                          (product.indoor_serial || product.outdoor_serial || '');
            
            // Skip products that are already added to the table
            if (addedProducts.has(uniqueId)) {
                return;
            }
            
            // Create search text from specific fields only
            var searchText = (product.brand || '') + ' ' + 
                             (product.indoor || '') + ' ' + 
                             (product.indoor_serial || '') + ' ' + 
                             (product.outdoor || '') + ' ' + 
                             (product.outdoor_serial || '');
            
            // Check if the product matches the search criteria or if we're showing all products
            if (showAll || (searchTerm && searchText.toLowerCase().indexOf(searchLower) > -1)) {
                hasResults = true;
                
                var row = $('<tr>');
                
                // Checkbox cell
                var checkboxCell = $('<td class="checkbox-cell">');
                var checkbox = $('<input type="checkbox" class="product-checkbox product-row-checkbox">');
                checkbox.data('product', product);
                checkboxCell.append(checkbox);
                row.append(checkboxCell);
                
                // Brand - with highlighting
                var brandText = product.brand || '';
                if (searchTerm && !showAll) {
                    brandText = highlightText(brandText, searchTerm);
                }
                row.append($('<td>').html(brandText));
                
                // Type
                row.append($('<td>').text(product.type || ''));
                
                // HP
                row.append($('<td>').text(product.hp || ''));
                
                // Series
                row.append($('<td>').text(product.series || ''));
                
                // Indoor - with highlighting
                var indoorText = product.indoor || '';
                if (searchTerm && !showAll) {
                    indoorText = highlightText(indoorText, searchTerm);
                }
                row.append($('<td>').html(indoorText));
                
                // Indoor Serial - with highlighting
                var indoorSerialText = product.indoor_serial || '';
                if (searchTerm && !showAll) {
                    indoorSerialText = highlightText(indoorSerialText, searchTerm);
                }
                row.append($('<td>').html(indoorSerialText));
                
                // Outdoor - with highlighting
                var outdoorText = product.outdoor || '';
                if (searchTerm && !showAll) {
                    outdoorText = highlightText(outdoorText, searchTerm);
                }
                row.append($('<td>').html(outdoorText));
                
                // Outdoor Serial - with highlighting
                var outdoorSerialText = product.outdoor_serial || '';
                if (searchTerm && !showAll) {
                    outdoorSerialText = highlightText(outdoorSerialText, searchTerm);
                }
                row.append($('<td>').html(outdoorSerialText));
                
                // Price
                row.append($('<td>').text(parseFloat(product.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                
                // Status column
                row.append($('<td>').html('<span class="badge badge-success">Available</span>'));
                
                tbody.append(row);
            }
        });
        
        if (!hasResults) {
            tbody.append('<tr><td colspan="11" class="text-center">No products match your search criteria.</td></tr>');
        }
    }

    $(function(){
        // Helper functions to extract and format numbers
        function extractNumericPart(numberString) {
            return parseInt(numberString.replace(/\D/g, '')) || 0;
        }
        
        function formatNextInvoiceNumber(currentInvoiceNumber) {
            var numericPart = extractNumericPart(currentInvoiceNumber);
            var nextNumeric = numericPart + 1;
            return "SI-" + String(nextNumeric).padStart(5, '0');
        }
        
        function formatNextTransactionNumber(currentTransactionNumber) {
            var numericPart = extractNumericPart(currentTransactionNumber);
            var nextNumeric = numericPart + 1;
            return "" + String(nextNumeric).padStart(7, '0');
        }
        
        // Store initial values when page loads
        var initialInvoiceNumber = $('input[name="invoice_number"]').val();
        var initialTransactionNumber = $('input[name="transaction_number"]').val();
        
        // Initialize invoice number for new sales
        if ($('input[name="id"]').val() === '') {
            // Check if we have a stored next order number
            var nextOrder = localStorage.getItem('next_invoice_number');
            
            if (nextOrder) {
                $('input[name="invoice_number"]').val(nextOrder);
                localStorage.removeItem('next_invoice_number'); // Clear after use
            } else {
                // Use the PHP-generated value if available
                if (initialInvoiceNumber) {
                    $('input[name="invoice_number"]').val(initialInvoiceNumber);
                }
            }
            
            // Initialize transaction number for new sales
            var nextTransaction = localStorage.getItem('next_transaction_number');
            
            if (nextTransaction) {
                $('input[name="transaction_number"]').val(nextTransaction);
                localStorage.removeItem('next_transaction_number'); // Clear after use
            } else {
                // Use the PHP-generated value if available
                if (initialTransactionNumber) {
                    $('input[name="transaction_number"]').val(initialTransactionNumber);
                }
            }
        }
        
        $('.select2').select2({
            placeholder:"Please select here",
            width:'resolve',
        })
        
        // Always call calc() on page load to initialize calculations
        calc();
        
        // Function to fetch and display brand items from receiving_list
        function fetchBrandItems(brand) {
            var tbody = $('#brand_items_tbody');
            
            // Clear previous items
            tbody.empty();
            
            // Show loading indicator
            tbody.append('<tr><td colspan="6" class="text-center"><span class="loading-spinner"></span>Loading...</td></tr>');
            
            // Fetch items for the selected brand from receiving_list
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=get_receiving_items_by_brand",
                method: "POST",
                data: {brand: brand},
                dataType: "json",
                success: function(resp) {
                    tbody.empty(); // Clear loading indicator
                    
                    if (resp.status === 'success' && resp.data.length > 0) {
                        // Add rows with animation
                        var delay = 0;
                        resp.data.forEach(function(item, index) {
                            setTimeout(function() {
                                var row = $('<tr style="opacity: 0; transform: translateY(10px);">');
                                row.append('<td>' + (item.brand || '') + '</td>');
                                row.append('<td>' + (item.serial_no || '') + '</td>');
                                
                                // Add indoor/outdoor as a badge
                                var indoorOutdoor = item.indoor_outdoor || '';
                                var indoorOutdoorHtml = '';
                                if (indoorOutdoor.toLowerCase() === 'indoor') {
                                    indoorOutdoorHtml = '<span class="indoor-badge">Indoor</span>';
                                } else if (indoorOutdoor.toLowerCase() === 'outdoor') {
                                    indoorOutdoorHtml = '<span class="outdoor-badge">Outdoor</span>';
                                }
                                row.append('<td>' + indoorOutdoorHtml + '</td>');
                                row.append('<td>' + (item.type || '') + '</td>');
                                row.append('<td>' + (item.hp || '') + '</td>');
                                row.append('<td>' + (parseFloat(item.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})) + '</td>');
                                tbody.append(row);
                                
                                // Animate row appearance
                                setTimeout(function() {
                                    row.css({
                                        'opacity': 1,
                                        'transform': 'translateY(0)',
                                        'transition': 'all 0.3s ease'
                                    });
                                }, 50);
                            }, delay);
                            delay += 50; // Stagger the animations
                        });
                    } else {
                        tbody.append('<tr><td colspan="6" class="brand-items-empty">No items found</td></tr>');
                    }
                },
                error: err => {
                    console.error("AJAX Error:", err);
                    tbody.empty();
                    tbody.append('<tr><td colspan="6" class="brand-items-empty">Error loading items</td></tr>');
                }
            });
        }
        
        // Function to trigger search
        function triggerSearch() {
            var value = $('#product-search').val();
            
            // Only fetch and display products if there's a search term
            if (value.length > 0) {
                fetchAndDisplayProducts(value, false);
            } else {
                // If search is empty, clear the table and show a message
                var tbody = $('#products_tbody');
                tbody.empty();
                tbody.append('<tr><td colspan="11" class="text-center">Enter a search term to find products or click "Display Products" to show all.</td></tr>');
                
                // Show modal if not already open
                if (!isModalOpen) {
                    $('#productsModal').modal('show');
                    isModalOpen = true;
                }
            }
        }
        
        // Search icon click event
        $('#search-icon-btn').on('click', function() {
            triggerSearch();
        });
        
        // Enter key press event in search input
        $('#product-search').on('keypress', function(e) {
            if (e.which === 13) { // 13 is the Enter key
                e.preventDefault(); // Prevent form submission
                triggerSearch();
            }
        });
        
        // Display All Products button click handler
        $('#display_products').click(function(){
            // Get the search value from the main form
            var searchValue = $('#product-search').val();
            
            // Fetch and display all products (showAll = true)
            fetchAndDisplayProducts(searchValue, true);
        });
        
        // Reset modal state when it's closed
        $('#productsModal').on('hidden.bs.modal', function() {
            isModalOpen = false;
            // Clear the search input when modal is closed
            $('#product-search').val('');
            // Uncheck all checkboxes
            $('.product-checkbox').prop('checked', false);
            // Optionally reset the products cache if you want fresh data each time
            // allProducts = null;
        });
        
        // Refresh modal when it's shown
        $('#productsModal').on('shown.bs.modal', function() {
            // Get current search term
            var searchTerm = $('#product-search').val();
            // Refresh the product list
            if (allProducts) {
                filterAndDisplayProducts(allProducts, searchTerm, searchTerm.length === 0);
            }
        });
        
        // Add selected products button click handler
        $('#add-selected-products').on('click', function() {
            var selectedProducts = [];
            $('.product-row-checkbox:checked').each(function() {
                selectedProducts.push($(this).data('product'));
            });
            
            if (selectedProducts.length === 0) {
                alert_toast('Please select at least one product.', 'warning');
                return;
            }
            
            // Add each selected product to the list
            var addedCount = 0;
            selectedProducts.forEach(function(product) {
                if (addProductToList(product)) {
                    addedCount++;
                }
            });
            
            if (addedCount > 0) {
                // Show success message with count
                var successMsg = $('#success-message');
                successMsg.html('<i class="fas fa-check-circle"></i> ' + addedCount + ' product(s) added successfully!');
                successMsg.fadeIn();
                setTimeout(function() {
                    successMsg.fadeOut();
                }, 3000);
                
                // Refresh the modal to remove the added products
                var searchTerm = $('#product-search').val();
                if (allProducts) {
                    filterAndDisplayProducts(allProducts, searchTerm, searchTerm.length === 0);
                }
            }
        });
        
        // Function to add a product to the list
        function addProductToList(product) {
            var brand = product.brand || '';
            var type = product.type || '';
            var hp = product.hp || '';
            var series = product.series || '';
            var indoor = product.indoor || '';
            var indoor_serial = product.indoor_serial || '';
            var outdoor = product.outdoor || '';
            var outdoor_serial = product.outdoor_serial || '';
            var price = parseFloat(product.price || 0);
            var qty = 1; // Default quantity to 1
            var total = qty * price;
            
            // Create a unique identifier using multiple fields
            var uniqueId = brand + '-' + type + '-' + hp + '-' + (indoor_serial || outdoor_serial || '');
            
            // Check if the product already exists in the list
            if($('table#list tbody').find('tr[data-id="'+uniqueId+'"]').length > 0){
                // Skip adding duplicate product
                return false;
            }
            
            // Add to the set of added products
            addedProducts.add(uniqueId);
            
            // Clone the template row
            var tr = $('#clone_list tr').clone();
            
            // Set all form fields
            tr.find('[name="qty[]"]').val(qty);
            tr.find('[name="indoor[]"]').val(indoor);
            tr.find('[name="indoor_serial[]"]').val(indoor_serial);
            tr.find('[name="outdoor[]"]').val(outdoor);
            tr.find('[name="outdoor_serial[]"]').val(outdoor_serial);
            tr.find('[name="brand[]"]').val(brand);
            tr.find('[name="type[]"]').val(type);
            tr.find('[name="hp[]"]').val(hp);
            tr.find('[name="series[]"]').val(series);
            tr.find('[name="item_price[]"]').val(price);
            tr.find('[name="row_perc[]"]').val(0); // Initialize discount to 0
            tr.find('[name="total_amount[]"]').val(total);
            tr.find('[name="unit[]"]').val("SETS"); // Set unit to "SETS"
            
            // Set the data-id attribute for duplicate checking and tracking
            tr.attr('data-id', uniqueId);
            
            // Set the visible text for the new columns
            tr.find('.qty .visible').text(qty);
            tr.find('.indoor .visible').text(indoor);
            tr.find('.indoor_serial .visible').text(indoor_serial);
            tr.find('.outdoor .visible').text(outdoor);
            tr.find('.outdoor_serial .visible').text(outdoor_serial);
            tr.find('.unit .visible').text("SETS");
            
            // Format the item description: DAIKIN (HP) , TYPE TYPE, SERIES SERIES
            var item_description = brand.toUpperCase() + ' (' + hp + ' HP) , ' + type.toUpperCase() + ' TYPE';
            if (series) {
                item_description += ', ' + series.toUpperCase() + ' SERIES';
            }
            tr.find('.item .visible').html(item_description);
            
            // Format the price and total
            tr.find('.price .visible').text(parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2}));
            tr.find('.total .visible').text(parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            // Animate the new row appearance
            tr.css({
                'opacity': 0,
                'transform': 'translateY(10px)'
            });
            $('table#list tbody').append(tr);
            tr.animate({
                'opacity': 1,
                'transform': 'translateY(0)'
            }, 300);
            
            // Add event handler for the remove button
            tr.find('.rem_row').click(function(){
                rem($(this));
            });
            
            // Add event handler for the discount input
            tr.find('.per-item-discount-input').on('input change', function() {
                // Update the hidden row_perc input
                tr.find('[name="row_perc[]"]').val($(this).val());
                calc();
            });
            
            // Recalculate totals
            calc();
            
            return true;
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
        
        // Add row click handler to select item from brand items table
        $(document).on('click', '#brand_items_tbody tr', function() {
            // Remove previous selection
            $('.brand-items-table tr.selected').removeClass('selected');
            
            // Add selection to clicked row
            $(this).addClass('selected');
        });
        
        // Handle select item button in modal
        $('#select_item_btn').click(function() {
            var selectedRow = $('#brand_items_tbody tr.selected');
            
            if (selectedRow.length === 0) {
                alert_toast('Please select an item from the table.', 'warning');
                return false;
            }
            
            // Get data from selected row
            var brand = selectedRow.find('td:eq(0)').text();
            var serialNo = selectedRow.find('td:eq(1)').text();
            var indoorOutdoor = selectedRow.find('td:eq(2) span').attr('class') === 'indoor-badge' ? 'indoor' : 'outdoor';
            var type = selectedRow.find('td:eq(3)').text();
            var hp = selectedRow.find('td:eq(4)').text();
            var price = selectedRow.find('td:eq(5)').text().replace(/,/g, ''); // Remove commas for numeric value
            
            // Create a product object
            var product = {
                brand: brand,
                type: type,
                hp: hp,
                price: parseFloat(price),
                indoor_outdoor: indoorOutdoor
            };
            
            // Add the product to the list
            if (addProductToList(product)) {
                // Close modal
                $('#brandItemsModal').modal('hide');
            }
        });
        
        $('#add_to_list').click(function(){
            var brand = $('#brand').val()
            var type = $('#type').val()
            var hp = $('#hp').val()
            var series = $('#series').val()
            var indoor = $('#indoor').val()
            var indoor_serial = $('#indoor_serial').val()
            var outdoor = $('#outdoor').val()
            var outdoor_serial = $('#outdoor_serial').val()
            var price = $('#price').val() > 0 ? $('#price').val() : 0;
            var qty = 1; // Default quantity to 1
            var total = parseFloat(qty) * parseFloat(price)
            var item_name = brand + ', ' + type + ' Type, ' + hp + (series ? ', ' + series : '');
            var tr = $('#clone_list tr').clone()
            
            if(brand == '' || type == '' || hp == '' || price == ''){
                alert_toast('Brand, Type, HP and Price are required.','warning');
                return false;
            }
            
            // Create a unique identifier using multiple fields
            var uniqueId = brand + '-' + type + '-' + hp + '-' + (indoor_serial || outdoor_serial || '');
            
            if($('table#list tbody').find('tr[data-id="'+uniqueId+'"]').length > 0){
                alert_toast('This item already exists on the list.','error');
                return false;
            }
            
            // Add to the set of added products
            addedProducts.add(uniqueId);
            
            // Set all form fields
            tr.find('[name="qty[]"]').val(qty)
            tr.find('[name="indoor[]"]').val(indoor)
            tr.find('[name="indoor_serial[]"]').val(indoor_serial)
            tr.find('[name="outdoor[]"]').val(outdoor)
            tr.find('[name="outdoor_serial[]"]').val(outdoor_serial)
            tr.find('[name="brand[]"]').val(brand)
            tr.find('[name="type[]"]').val(type)
            tr.find('[name="hp[]"]').val(hp)
            tr.find('[name="series[]"]').val(series)
            tr.find('[name="item_price[]"]').val(price)
            tr.find('[name="row_perc[]"]').val(0) // Initialize discount to 0
            tr.find('[name="total_amount[]"]').val(total)
            tr.find('[name="unit[]"]').val("SETS") // Set unit to "SETS"
            
            // Set the visible text for the new columns
            tr.find('.qty .visible').text(qty)
            tr.find('.indoor .visible').text(indoor)
            tr.find('.indoor_serial .visible').text(indoor_serial)
            tr.find('.outdoor .visible').text(outdoor)
            tr.find('.outdoor_serial .visible').text(outdoor_serial)
            tr.find('.unit .visible').text("SETS")
            
            tr.attr('data-id',uniqueId)
            
            // Format the item description: DAIKIN (HP) , TYPE TYPE, SERIES SERIES
            var item_description = brand.toUpperCase() + ' (' + hp + ' HP) , ' + type.toUpperCase() + ' TYPE';
            if (series) {
                item_description += ', ' + series.toUpperCase() + ' SERIES';
            }
            tr.find('.item .visible').html(item_description)
            
            tr.find('.price .visible').text(parseFloat(price).toLocaleString('en-US',{minimumFractionDigits: 2}))
            tr.find('.total .visible').text(parseFloat(total).toLocaleString('en-US',{minimumFractionDigits: 2}))
            
            // Animate the new row appearance
            tr.css({
                'opacity': 0,
                'transform': 'translateY(10px)'
            });
            $('table#list tbody').append(tr);
            tr.animate({
                'opacity': 1,
                'transform': 'translateY(0)'
            }, 300);
            
            calc()
            $('#brand').val('')
            $('#type').val('')
            $('#hp').val('')
            $('#series').val('')
            $('#indoor').val('')
            $('#indoor_serial').val('')
            $('#outdoor').val('')
            $('#outdoor_serial').val('')
            $('#price').val('')
            tr.find('.rem_row').click(function(){
                rem($(this))
            })
            
            // Add event handler for the discount input
            tr.find('.per-item-discount-input').on('input change', function() {
                // Update the hidden row_perc input
                tr.find('[name="row_perc[]"]').val($(this).val());
                calc();
            });
        })
        
        $('#sale-form').submit(function(e){
            e.preventDefault();
            var _this = $(this)
            
            // Ensure date is in correct format before submission
            var dateInput = $('input[name="date"]');
            if(dateInput.val()) {
                var dateObj = new Date(dateInput.val());
                if(!isNaN(dateObj.getTime())) {
                    // Format as YYYY-MM-DD
                    var formattedDate = dateObj.toISOString().split('T')[0];
                    dateInput.val(formattedDate);
                }
            }
            
            start_loader();
            
            // Create a FormData object from the form
            var formData = new FormData(this);
            
            // Log the form data for debugging
            console.log('Form data being submitted:');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Add the next invoice and transaction numbers to the form data
            var currentInvoiceNumber = $('input[name="invoice_number"]').val();
            var currentTransactionNumber = $('input[name="transaction_number"]').val();
            
            var nextInvoiceNumber = formatNextInvoiceNumber(currentInvoiceNumber);
            var nextTransactionNumber = formatNextTransactionNumber(currentTransactionNumber);
            
            formData.append('next_invoice_number', nextInvoiceNumber);
            formData.append('next_transaction_number', nextTransactionNumber);
            
            $.ajax({
                url:_base_url_+"classes/Master.php?f=save_sale",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: "POST",
                type: "POST",
                dataType: "json",
                error:err=>{
                    console.error("AJAX Error on save:", err);
                    // Check if the response contains HTML error messages
                    if (err.responseText && err.responseText.indexOf('<') === 0) {
                        // Extract the error message from HTML
                        var errorMatch = err.responseText.match(/<b>([^<]+)<\/b>/);
                        var errorMessage = errorMatch ? errorMatch[1] : 'Unknown server error';
                        alert_toast("Server Error: " + errorMessage, 'error');
                    } else {
                        alert_toast("An error occurred while saving.", 'error');
                    }
                    end_loader();
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        // Store next order number in localStorage for future use
                        localStorage.setItem('next_invoice_number', nextInvoiceNumber);
                        localStorage.setItem('next_transaction_number', nextTransactionNumber);
                        
                        // Redirect to the view sale page
                        location.replace(_base_url_+"admin/?page=sales/view_sale&id="+resp.sale_id);
                    }else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                        end_loader();
                    }else{
                        alert_toast("An error occured",'error');
                        end_loader();
                    }
                    $('html,body').animate({scrollTop:0},'fast')
                }
            })
        })
        
        // This block runs when the page is in "edit" mode
        if('<?php echo isset($id) && $id > 0 ?>' == '1'){
            calc()
            $('#supplier_id').trigger('change')
            $('#supplier_id').attr('readonly','readonly')
            $('table#list tbody tr .rem_row').click(function(){
                rem($(this))
            })
            
            // Initialize added products set with existing items
            $('table#list tbody tr').each(function() {
                var brand = $(this).find('[name="brand[]"]').val();
                var type = $(this).find('[name="type[]"]').val();
                var hp = $(this).find('[name="hp[]"]').val();
                var indoor_serial = $(this).find('[name="indoor_serial[]"]').val();
                var outdoor_serial = $(this).find('[name="outdoor_serial[]"]').val();
                
                var uniqueId = brand + '-' + type + '-' + hp + '-' + (indoor_serial || outdoor_serial || '');
                addedProducts.add(uniqueId);
            });
            
            // Add event listeners for existing discount inputs
            $('table#list tbody tr .per-item-discount-input').on('input change', function() {
                // Update the hidden row_perc input
                $(this).closest('tr').find('[name="row_perc[]"]').val($(this).val());
                calc();
            });
        }
        
        // Setup event listeners for tax percentage inputs
        $('[name="tax_perc"]').on('input change',function(){
            calc()
        })
        
        // Add event listener for per-item discount inputs
        $(document).on('input change', '.per-item-discount-input', function() {
            // Update the hidden row_perc input
            $(this).closest('tr').find('[name="row_perc[]"]').val($(this).val());
            calc();
        });
        
        // Refresh button click event
        $('#refresh-btn').on('click', function() {
            // Add spinning animation to the refresh icon
            $(this).addClass('refreshing');
            
            // If we're in edit mode, reload the page to get fresh data
            if ('<?php echo isset($id) && $id > 0 ?>' == '1') {
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                // Reset form fields
                $('#sale-form')[0].reset();
                
                // Clear any added items in the table
                $('#list tbody').empty();
                
                // Reset calculations
                calc();
                
                // Clear added products set
                addedProducts.clear();
                
                // Reset product search
                $('#product-search').val('');
                
                // Generate next invoice and transaction numbers
                var currentInvoiceNumber = $('input[name="invoice_number"]').val();
                var currentTransactionNumber = $('input[name="transaction_number"]').val();
                
                var nextInvoiceNumber = formatNextInvoiceNumber(currentInvoiceNumber);
                var nextTransactionNumber = formatNextTransactionNumber(currentTransactionNumber);
                
                $('input[name="invoice_number"]').val(nextInvoiceNumber);
                $('input[name="transaction_number"]').val(nextTransactionNumber);
                
                // Remove spinning animation after a short delay
                setTimeout(() => {
                    $(this).removeClass('refreshing');
                }, 800);
                
                // Show success message
                showSuccessMessage('Form refreshed successfully');
            }
        });
    })
    
    function rem(_this){
        // Get the unique ID of the product being removed
        var tr = _this.closest('tr');
        var uniqueId = tr.attr('data-id');
        
        // Remove from the set of added products
        if (uniqueId && addedProducts.has(uniqueId)) {
            addedProducts.delete(uniqueId);
        }
        
        // Animate row removal
        tr.animate({
            'opacity': 0,
            'transform': 'translateX(-20px)'
        }, 300, function() {
            $(this).remove();
            calc();
            if($('table#list tbody tr').length <= 0)
                $('#supplier_id').removeAttr('readonly')
            
            // Refresh the modal if it's open to show the removed product again
            if (isModalOpen && allProducts) {
                var searchTerm = $('#product-search').val();
                filterAndDisplayProducts(allProducts, searchTerm, searchTerm.length === 0);
            }
        });
    }
    
function calc(){
    var sub_total = 0;
    var grand_total = 0;
    var totalDiscount = 0; // This will accumulate the discount from each row
    var tax = 0;
    
    // Calculate from displayed values in the table
    $('table#list tbody tr').each(function() {
        var qty = parseFloat($(this).find('[name="qty[]"]').val()) || 0;
        var price = parseFloat($(this).find('[name="item_price[]"]').val()) || 0;
        var rowPerc = parseFloat($(this).find('[name="row_perc[]"]').val()) || 0;
        
        // Calculate item total
        var itemTotal = qty * price;
        
        // Calculate discount amount as percentage of item total
        var discountPerItemAmount = Math.round((itemTotal * rowPerc / 100) * 100) / 100;
        
        // Add to total discount
        totalDiscount += discountPerItemAmount;
        
        // Calculate row total after discount
        var rowTotal = itemTotal - discountPerItemAmount;
        
        // Update the row total display
        $(this).find('.total .visible').text(rowTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $(this).find('[name="total_amount[]"]').val(rowTotal);
        
        // Add to subtotal
        sub_total += rowTotal;
    });
    
    // Round subtotal to 2 decimal places
    sub_total = Math.round(sub_total * 100) / 100;
    totalDiscount = Math.round(totalDiscount * 100) / 100;
    
    // Update subtotal display
    $('table#list tfoot .sub-total').text(sub_total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    
    // Calculate net of VAT: subtotal divided by 1.12
    var net_vat = sub_total / 1.12;
    net_vat = Math.round(net_vat * 100) / 100;
    
    // Calculate VAT amount: difference between subtotal and net of VAT
    var vat_amount = sub_total - net_vat;
    vat_amount = Math.round(vat_amount * 100) / 100;
    
    // Update the net-vat and vat-amount displays
    $('.net-vat').text(net_vat.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('.vat-amount').text(vat_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    
    // Update the hidden VAT input
    $('[name="vat"]').val(vat_amount);
    
    // Calculate overall discount percentage
    var overallDiscountPerc = 0;
    if (sub_total > 0) {
        overallDiscountPerc = (totalDiscount / sub_total) * 100;
    }
    
    // Update discount total display with percentage
    $('.discount-amount').text(totalDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    
    // Calculate withholding tax based on net of VAT amount
    var tax_perc = parseFloat($('[name="tax_perc"]').val()) || 0;
    tax = Math.round(net_vat * tax_perc / 100 * 100) / 100; // Fixed: Calculate based on net_vat
    
    // Calculate grand total (subtotal minus withholding tax)
    grand_total = Math.round((sub_total - tax) * 100) / 100; // Fixed: Deduct withholding tax
    
    // Update displays
    $('.tax').text(tax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('table#list tfoot .grand-total').text(grand_total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('[name="amount"]').val(grand_total);
}


</script>
</body>
</html>
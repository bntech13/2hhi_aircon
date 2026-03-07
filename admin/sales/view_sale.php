<?php
// Initialize all variables to avoid undefined warnings
 $id = $client = $date = $remarks = '';
 $sub_total = 0;
 $discount = 0;
 $tax = 0;
 $grand_total = 0;
 $total_quantity = 0;
 $all_items = [];
 $debug_info = [];
 $current_sale = []; // Initialize to avoid undefined variable errors
 $discount_perc = 0; // Initialize as 0
 $tax_perc = 0; // Initialize as 0
 $invoice_number = ''; // New variable for invoice number
 $transaction_number = ''; // New variable for transaction number
 $row_discount_total = 0; // New variable to track total of row discounts
 $calculated_sub_total = 0; // Initialize calculated sub total
 $netOfVAT = 0; // Initialize net of VAT
 $vatAmount = 0; // Initialize VAT amount
 $withholdingTax = 0; // Initialize withholding tax
 $formatted_date = 'N/A'; // Initialize formatted date

// Get the lookup value from the URL
 $lookup_value = $_GET['id'] ?? null;

if ($lookup_value) {
    // Always search by the 'id' column (numeric)
    if (!is_numeric($lookup_value)) {
        $debug_info[] = "ERROR: Lookup value must be a numeric ID.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM sales_list WHERE id = ?");
        $stmt->bind_param("i", $lookup_value);
        $debug_info[] = "Searching for sales record with numeric ID: " . $lookup_value;
        $stmt->execute();
        $qry = $stmt->get_result();

        if ($qry->num_rows > 0) {
            // Get all records with the same invoice number
            $invoice_value = '';
            
            // First, get the invoice number and transaction number from the first record
            $first_record = $qry->fetch_assoc();
            $invoice_value = $first_record['invoice_number'] ?? '';
            
            // FIXED: Format transaction number to 7 digits with leading zeros
            $transaction_number = $first_record['transaction_number'] ?? '';
            if (!empty($transaction_number)) {
                // Remove any non-numeric characters and pad with zeros
                $numeric_part = preg_replace('/[^0-9]/', '', $transaction_number);
                $transaction_number = str_pad($numeric_part, 7, '0', STR_PAD_LEFT);
            }
            
            // If invoice number is empty, create a formatted fallback
            if (empty($invoice_value)) {
                $invoice_value = 'SI-' . str_pad($first_record['id'], 5, '0', STR_PAD_LEFT);
                $debug_info[] = "Invoice number was empty, using fallback: $invoice_value";
            }
            
            // FIXED: If transaction number is empty, create a formatted fallback based on invoice number
            if (empty($transaction_number)) {
                // Extract numeric part from invoice number
                $numeric_part = preg_replace('/[^0-9]/', '', $invoice_value);
                if (empty($numeric_part)) {
                    $numeric_part = $first_record['id'];
                }
                $transaction_number = str_pad($numeric_part, 7, '0', STR_PAD_LEFT);
                $debug_info[] = "Transaction number was empty, using fallback: $transaction_number";
            }
            
            // FIXED: Get discount percentage and amount directly from the first record
            if (isset($first_record['discount_perc']) && is_numeric($first_record['discount_perc'])) {
                $discount_perc = floatval($first_record['discount_perc']);
                $debug_info[] = "Using discount percentage from first record: " . $discount_perc . "%";
            } else {
                $discount_perc = 0;
                $debug_info[] = "Discount percentage not found, using default: 0%";
            }
            
            if (isset($first_record['discount']) && is_numeric($first_record['discount'])) {
                $discount = floatval($first_record['discount']);
                $debug_info[] = "Using discount amount from first record: " . $discount;
            } else {
                $discount = 0;
                $debug_info[] = "Discount amount not found, using default: 0";
            }
            
            // FIXED: Get tax percentage and amount directly from the first record
            if (isset($first_record['tax_perc']) && is_numeric($first_record['tax_perc'])) {
                $tax_perc = floatval($first_record['tax_perc']);
                $debug_info[] = "Using tax percentage from first record: " . $tax_perc . "%";
            } else {
                $tax_perc = 0;
                $debug_info[] = "Tax percentage not found, using default: 0%";
            }
            
            if (isset($first_record['tax']) && is_numeric($first_record['tax'])) {
                $tax = floatval($first_record['tax']);
                $debug_info[] = "Using tax amount from first record: " . $tax;
            } else {
                $tax = 0;
                $debug_info[] = "Tax amount not found, using default: 0";
            }
            
            // FIXED: Properly handle date formatting
            $date = $first_record['sale_date'] ?? $first_record['date'] ?? '';
            if (!empty($date)) {
                try {
                    $dateObj = new DateTime($date);
                    $formatted_date = $dateObj->format('M d, Y');
                } catch (Exception $e) {
                    $formatted_date = 'Invalid date';
                    $debug_info[] = "Date parsing error: " . $e->getMessage();
                }
            } else {
                $formatted_date = 'N/A';
                $debug_info[] = "No date found in record";
            }
            
            // Now get all records with this invoice number
            $stmt_all = $conn->prepare("SELECT * FROM sales_list WHERE invoice_number = ? ORDER BY id");
            $stmt_all->bind_param("s", $invoice_value);
            $stmt_all->execute();
            $all_sales_qry = $stmt_all->get_result();
            
            $all_items = [];
            $calculated_sub_total = 0;
            $row_discount_total = 0; // Initialize row discount total
            
            if ($all_sales_qry->num_rows > 0) {
                while ($row = $all_sales_qry->fetch_assoc()) {
                    // FIXED: Removed the condition that skips "English" rows
                    $description = strtoupper($row['brand'] ?? '') . ' (' . ($row['hp'] ?? '') . ' HP) , ' . strtoupper($row['type'] ?? '') . ' TYPE';
                    if (!empty($row['series'])) {
                        $description .= ', ' . strtoupper($row['series']) . ' SERIES';
                    }
                    
                    // FIXED: Removed the condition that skips rows with "ENGLISH" in description
                    // All rows will be added regardless of description content
                    
                    $all_items[] = $row;
                    
                    // Calculate totals from each item
                    if (isset($row['total_amount']) && is_numeric($row['total_amount'])) {
                        $calculated_sub_total += floatval($row['total_amount']);
                    }
                    
                    // NEW: Calculate row discount total
                    if (isset($row['row_perc']) && is_numeric($row['row_perc']) && 
                        isset($row['price']) && is_numeric($row['price']) &&
                        isset($row['quantity']) && is_numeric($row['quantity'])) {
                        $row_price = floatval($row['price']);
                        $row_quantity = floatval($row['quantity'] ?? 1);
                        $row_discount_percent = floatval($row['row_perc']);
                        $row_discount_amount = ($row_price * $row_quantity) * ($row_discount_percent / 100);
                        $row_discount_total += $row_discount_amount;
                    }
                }
                
                // FIXED: Use row discount total instead of calculating from overall discount percentage
                $discount = $row_discount_total;
                $debug_info[] = "Calculated discount from row percentages: " . number_format($discount, 2);
                
                // Set main details from the first record
                $id = $first_record['id'] ?? '';
                $invoice_number = $invoice_value;
                $client = $first_record['client_name'] ?? '';
                $remarks = $first_record['remarks'] ?? '';
                
                // Calculate values for VAT computation
                $totalSalesVATInclusive = $calculated_sub_total;
                $netOfVAT = $totalSalesVATInclusive / 1.12; // Net of VAT = Total / 1.12
                $vatAmount = $totalSalesVATInclusive - $netOfVAT; // VAT = Total - Net of VAT
                
                // FIXED: Calculate withholding tax as a percentage of Net of VAT if tax_perc is set
                if ($tax_perc > 0) {
                    $tax = $netOfVAT * ($tax_perc / 100); // Tax = Net of VAT * (tax percentage / 100)
                    $debug_info[] = "Recalculated withholding tax based on Net of VAT: " . number_format($tax, 2) . " (" . number_format($tax_perc, 2) . "% of " . number_format($netOfVAT, 2) . ")";
                }
                $withholdingTax = $tax; // Set withholding tax to the calculated tax amount
                
                // UPDATED: Calculate grand total as sub total minus withholding tax
                $grand_total = $calculated_sub_total - $withholdingTax;
                
                $debug_info[] = "Found " . count($all_items) . " items with invoice number $invoice_number";
                $debug_info[] = "Transaction number: $transaction_number";
                $debug_info[] = "Calculated sub-total: " . number_format($calculated_sub_total, 2);
                $debug_info[] = "Discount: " . number_format($discount, 2) . " (calculated from row percentages)";
                $debug_info[] = "VAT Amount: " . number_format($vatAmount, 2);
                $debug_info[] = "Net of VAT: " . number_format($netOfVAT, 2);
                $debug_info[] = "Withholding Tax: " . number_format($withholdingTax, 2) . " (" . number_format($tax_perc, 2) . "%)";
                $debug_info[] = "Grand Total (Sub Total - Withholding Tax): " . number_format($grand_total, 2);
            }
            
            $stmt_all->close();
            
            // REMOVED: Alternative approaches that might add extra rows
            // Only display items with the same invoice number from the main sales_list table
            
            // Final check
            if (empty($all_items)) {
                $debug_info[] = "WARNING: No items found for invoice number ID: $id, Invoice: $invoice_number";
            }

        } else {
            $debug_info[] = "ERROR: No sales record found with ID: " . htmlspecialchars($lookup_value);
        }
    }
} else {
    $debug_info[] = "ERROR: No identifier provided in URL.";
}

// Calculate values for VAT computation - Updated to subtract tax
 $totalSalesVATInclusive = $calculated_sub_total;
 $netOfVAT = $totalSalesVATInclusive / 1.12; // Net of VAT = Total / 1.12
 $vatAmount = $totalSalesVATInclusive - $netOfVAT; // VAT = Total - Net of VAT
 $withholdingTax = $tax; // Set withholding tax to the calculated tax amount
 $amountDue = $netOfVAT; // Amount due is net of VAT
 $totalAmountDue = $totalSalesVATInclusive - $withholdingTax; // Final amount after tax only
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
                <h5 style="color: #0066cc; margin: 0;" class="fas fa-shopping-cart" class="card-title"> <b>SALES DETAILS</b></h5>
            </div>
            <div class="card-tools">
                <button class="btn btn-flat btn-sm btn-green" id="print" type="button"><i class="fa fa-print"></i> Print</button>
                <a class="btn btn-flat btn-sm btn-cyan-blue" href="<?php echo base_url.'/admin/?page=sales' ?>"><i class="fa fa-arrow-left"></i> Go to List</a>
            </div>
        </div>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <!-- Main Sales Details -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="control-label text-info font-weight-bold">Sales Invoice No.</label>
                    <div class="border-bottom pb-1"><strong><?php echo !empty($invoice_number) ? htmlspecialchars(strtoupper($invoice_number)) : 'N/A' ?></strong></div>
                </div>
                <div class="col-md-3">
                    <label class="control-label text-info font-weight-bold">Transaction No.</label>
                    <!-- FIXED: Display transaction number with 7 digits and leading zeros -->
                    <div class="border-bottom pb-1"><strong><?php echo !empty($transaction_number) ? htmlspecialchars(strtoupper(str_pad($transaction_number, 7, '0', STR_PAD_LEFT))) : 'N/A' ?></strong></div>
                </div>
                <div class="col-md-3">
                    <label class="control-label text-info font-weight-bold">Date</label>
                    <!-- FIXED: Use the formatted date variable -->
                    <div class="border-bottom pb-1"><strong><?php echo $formatted_date; ?></strong></div>
                </div>
                <div class="col-md-3">
                    <label class="control-label text-info font-weight-bold">Customer</label>
                    <div class="border-bottom pb-1"><strong><?php echo htmlspecialchars(strtoupper(trim($client ?? 'N/A'))); ?></strong></div>
                </div>
            </div>
            
            <!-- Items Table -->
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="list">
                            <colgroup>
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
                                $i = 1;
                                if(!empty($all_items)):
                                    foreach($all_items as $row):
                                        // FIXED: Removed the condition that skips the second row
                                        // All rows will be displayed
                                        
                                        // Format description in uppercase: DAIKIN (HP) , TYPE TYPE, SERIES SERIES
                                        $description = strtoupper($row['brand'] ?? '') . ' (' . ($row['hp'] ?? '') . ' HP) , ' . strtoupper($row['type'] ?? '') . ' TYPE';
                                        if (!empty($row['series'])) {
                                            $description .= ', ' . strtoupper($row['series']) . ' SERIES';
                                        }
                                ?>
                                <tr>
                                    <td class="py-1 px-2 text-center"><strong><?php echo $row['quantity'] ?? 1 ?></strong></td>
                                    <td class="py-1 px-2 text-center"><strong><?php echo strtoupper($row['indoor'] ?? '') ?></strong></td>
                                    <td class="py-1 px-2 text-center"><strong><?php echo strtoupper($row['indoor_serial'] ?? '') ?></strong></td>
                                    <td class="py-1 px-2 text-center"><strong><?php echo strtoupper($row['outdoor'] ?? '') ?></strong></td>
                                    <td class="py-1 px-2 text-center"><strong><?php echo strtoupper($row['outdoor_serial'] ?? '') ?></strong></td>
                                    <td class="py-1 px-2 text-center"><strong><?php echo strtoupper($row['unit'] ?? 'SETS') ?></strong></td>
                                    <td class="py-1 px-2"><strong><?php echo $description ?></strong></td>
                                    <td class="py-1 px-2 text-right"><strong><?php echo number_format($row['price'] ?? 0, 2) ?></strong></td>
                                    <!-- UPDATED: Use row_perc instead of discount_perc and removed decimal places -->
                                    <td class="py-1 px-2 text-center"><strong><?php echo number_format($row['row_perc'] ?? 0, 0) ?>%</strong></td>
                                    <td class="py-1 px-2 text-right"><strong><?php echo number_format($row['total_amount'] ?? 0, 2) ?></strong></td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="10" class="text-center">No items found for this sale.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <!-- Added spacing row above Sub Total -->
                                <tr>
                                    <td colspan="10" style="height: 20px;"></td>
                                </tr>
                                
                                <tr class="total-row">
                                    <th class="text-right py-1 px-2" colspan="9">Sub Total</th>
                                    <th class="text-right py-1 px-2 sub-total"><strong><?php echo number_format($calculated_sub_total, 2) ?></strong></th>
                                </tr>
                                <tr class="total-row">
                                    <th class="text-right py-1 px-2" colspan="9">Less: VAT</th>
                                    <th class="text-right py-1 px-2 vat-amount"><strong><?php echo number_format($vatAmount, 2) ?></strong></th>
                                </tr>
                                <tr class="total-row">
                                    <th class="text-right py-1 px-2" colspan="9">Amount: Net of VAT</th>
                                    <th class="text-right py-1 px-2 net-vat"><strong><?php echo number_format($netOfVAT, 2) ?></strong></th>
                                </tr>

                                <tr>
                                    <td colspan="10" style="height: 20px;"></td>
                                </tr>

                                <tr class="total-row">
                                    <!-- FIXED: Always show discount percentage if it's greater than 0 -->
                                    <th class="text-right py-1 px-2" colspan="9">Less: Discount Total <?php echo $discount_perc > 0 ? number_format($discount_perc, 0) . "%" : "" ?></th>
                                    <!-- FIXED: Use the $discount variable for the amount -->
                                    <th class="text-right py-1 px-2 discount-amount"><strong><?php echo number_format($discount, 2) ?></strong></th>
                                </tr>
                                <tr class="total-row">
                                    <!-- FIXED: Always show tax percentage if it's greater than 0 and removed decimal places -->
                                    <th class="text-right py-1 px-2" colspan="9">Less: Withholding Tax <?php echo $tax_perc > 0 ? number_format($tax_perc, 0) . "%" : "" ?></th>
                                    <th class="text-right py-1 px-2 tax"><strong><?php echo number_format($withholdingTax, 2) ?></strong></th>
                                </tr>
                                <tr class="grand-total-row">
                                    <th class="text-right py-1 px-2" colspan="9">GRAND Total:</th>
                                    <th class="text-right py-1 px-2 grand-total"><strong><?php echo number_format($grand_total, 2) ?></strong></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Remarks Section -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="control-label text-info font-weight-bold">Remarks</label>
                    <div class="border-bottom pb-1">
                        <p><?php echo !empty($remarks) ? nl2br(htmlspecialchars(strtoupper($remarks))) : 'No remarks provided.'; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Debug Information (only visible in development or when enabled) -->
            <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5>Debug Information:</h5>
                        <ul>
                            <?php foreach($debug_info as $info): ?>
                            <li><?php echo $info; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Hidden input to store base URL -->
<input type="hidden" id="base_url" value="<?php echo base_url; ?>">

<!-- Hidden inputs to store VAT computation values -->
<input type="hidden" id="totalSalesVATInclusive" value="<?php echo number_format($totalSalesVATInclusive, 2); ?>">
<input type="hidden" id="vatAmount" value="<?php echo number_format($vatAmount, 2); ?>">
<input type="hidden" id="netOfVAT" value="<?php echo number_format($netOfVAT, 2); ?>">
<input type="hidden" id="withholdingTax" value="<?php echo number_format($withholdingTax, 2); ?>">
<input type="hidden" id="amountDue" value="<?php echo number_format($amountDue, 2); ?>">
<input type="hidden" id="totalAmountDue" value="<?php echo number_format($totalAmountDue, 2); ?>">
<input type="hidden" id="discount" value="<?php echo number_format($discount, 2); ?>">
<input type="hidden" id="discount_perc" value="<?php echo $discount_perc; ?>">
<input type="hidden" id="tax" value="<?php echo number_format($tax, 2); ?>">
<input type="hidden" id="tax_perc" value="<?php echo $tax_perc; ?>">
<!-- Hidden input to store formatted date for print function -->
<input type="hidden" id="formatted_date" value="<?php echo $formatted_date; ?>">

<style>
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
        font-weight: bold; /* Make all table cell text bold */
    }
    
    #list tfoot th {
        background-color: #f8f9fa;
        font-weight: 600;
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
    
    /* Custom button styling */
    .btn-green {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }
    
    .btn-green:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: white;
    }
    
    .btn-cyan-blue {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }
    
    .btn-cyan-blue:hover {
        background-color: #138496;
        border-color: #117a8b;
        color: white;
    }
    
    /* Print styles */
    @media print {
        .card-tools, .btn {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        .table {
            margin-bottom: 0 !important;
        }
        
        .table td, .table th {
            padding: 5px !important;
        }
    }
</style>

<script>
 $(function(){
    $('#print').click(function(){
        start_loader();
        
        // Get the base URL from the hidden input
        var baseUrl = $('#base_url').val();
        // Construct the full path to the logo in the uploads folder
        var logoSrc = baseUrl + '/uploads/logo.png';
        
        // Get VAT computation values from hidden inputs
        var totalSalesVATInclusive = $('#totalSalesVATInclusive').val();
        var withholdingTax = $('#withholdingTax').val();
        
        // Get discount and tax values
        var discount = $('#discount').val();
        var discount_perc = $('#discount_perc').val();
        var tax = $('#tax').val();
        var tax_perc = $('#tax_perc').val();
        
        // Get formatted date from hidden input
        var formattedDate = $('#formatted_date').val();
        
        // Calculate netOfVAT and vatAmount
        var totalSalesVATInclusiveNum = parseFloat(totalSalesVATInclusive.replace(/,/g, ''));
        var netOfVAT = (totalSalesVATInclusiveNum / 1.12).toFixed(2); // Net of VAT = Total / 1.12
        var vatAmount = (totalSalesVATInclusiveNum - parseFloat(netOfVAT)).toFixed(2); // VAT = Total - Net of VAT
        
        // Format values with commas
        var netOfVATFormatted = parseFloat(netOfVAT).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        var vatAmountFormatted = parseFloat(vatAmount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        
        // FIXED: Calculate total amount due as Total Sales (VAT Inclusive) minus Tax only
        var taxNum = parseFloat(tax.replace(/,/g, ''));
        var totalAmountDue = (totalSalesVATInclusiveNum - taxNum).toFixed(2);
        var totalAmountDueFormatted = parseFloat(totalAmountDue).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        
        // Create a new window with receipt-like layout
        var nw = window.open("", "", "width=850,height=1100");
        
        // Extract data from the page using more specific selectors
        var invoiceNumber = $('label:contains("Sales Invoice No.")').next('div').find('strong').text();
        var transactionNumber = $('label:contains("Transaction No.")').next('div').find('strong').text();
        // FIXED: Use the formatted date from hidden input instead of trying to extract it
        var dateCreated = formattedDate;
        var customer = $('label:contains("Customer")').next('div').find('strong').text();
        
        // FIXED: Format transaction number to 7 digits with leading zeros
        function formatTransactionNumber(transactionNumber) {
            // Remove any non-numeric characters
            var numericPart = transactionNumber.replace(/[^0-9]/g, '');
            // Pad with zeros to make it 7 digits
            return numericPart.padStart(7, '0');
        }
        
        // Format the transaction number
        transactionNumber = formatTransactionNumber(transactionNumber);
        
        // Function to trim whitespace from text
        function trimText(text) {
            return text.replace(/^\s+|\s+$/g, '');
        }
        
        // Function to convert text to uppercase
        function toUpperCase(text) {
            return text.toUpperCase();
        }
        
        // Group items by model and description to avoid duplicate Qty and Unit
        var itemGroups = {};
        var groupIndex = 0;
        
        $('table.table-striped tbody tr').each(function() {
            // Skip rows that have "Qty Total" in the first column
            var firstCell = $(this).find('td:eq(0)').text();
            if (firstCell.includes('Qty Total')) {
                return; // Skip this row
            }
            
            // Extract data from the row
            var qty = $(this).find('td:eq(0)').text(); // Changed index from 1 to 0 since we removed the # column
            var indoor = $(this).find('td:eq(1)').text(); // Changed index from 2 to 1
            var indoor_serial = $(this).find('td:eq(2)').text(); // Changed index from 3 to 2
            var outdoor = $(this).find('td:eq(3)').text(); // Changed index from 4 to 3
            var outdoor_serial = $(this).find('td:eq(4)').text(); // Changed index from 5 to 4
            var unit = $(this).find('td:eq(5)').text(); // Changed index from 6 to 5
            var description = $(this).find('td:eq(6)').text(); // Changed index from 7 to 6
            var price = $(this).find('td:eq(7)').text(); // Changed index from 8 to 7
            var total = $(this).find('td:eq(9)').text(); // Changed index from 10 to 9 (skip discount column at index 8)
            
            // Trim all values
            qty = trimText(qty);
            indoor = trimText(indoor);
            outdoor = trimText(outdoor);
            indoor_serial = trimText(indoor_serial);
            outdoor_serial = trimText(outdoor_serial);
            description = trimText(description);
            total = trimText(total);
            unit = trimText(unit);
            price = trimText(price);
            
            // Convert text values to uppercase
            indoor = toUpperCase(indoor);
            outdoor = toUpperCase(outdoor);
            indoor_serial = toUpperCase(indoor_serial);
            outdoor_serial = toUpperCase(outdoor_serial);
            description = toUpperCase(description);
            unit = toUpperCase(unit);
            
            // Create a unique key for grouping (based on indoor, outdoor, and description)
            var groupKey = indoor + '|' + outdoor + '|' + description;
            
            // If this group doesn't exist, create it
            if (!itemGroups[groupKey]) {
                itemGroups[groupKey] = {
                    indoor: indoor,
                    outdoor: outdoor,
                    description: description,
                    unit: unit,
                    price: price,
                    qty: 0,
                    total: 0,
                    serials: []
                };
            }
            
            // Add quantity and total to the group
            itemGroups[groupKey].qty += parseFloat(qty) || 0;
            itemGroups[groupKey].total += parseFloat(total.replace(/,/g, '')) || 0;
            
            // Add serial numbers to the group
            itemGroups[groupKey].serials.push({
                indoor: indoor_serial,
                outdoor: outdoor_serial
            });
        });
        
        // Build receipt HTML
        var receiptHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    @page {
                        size: letter; /* Standard letter size (8.5in x 11in) */
                        margin: 0.5in; /* Standard margin for letter size */
                    }
                    
                    * {
                        box-sizing: border-box;
                        font-family: Arial, sans-serif;
                    }
                    
                    body {
                        margin: 0;
                        padding: 0;
                        width: 7.5in; /* Width after accounting for 0.5in margins */
                        font-size: 12px; /* Larger font for better readability on letter size */
                    }
                    
                    .receipt-container {
                        max-width: 100%;
                        padding: 10px;
                    }
                    
                    .receipt-header {
                        display: flex;
                        align-items: center;
                        margin-bottom: 20px;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 15px;
                    }
                    
                    .logo-container {
                        flex: 0 0 auto;
                        margin-right: 20px;
                    }
                    
                    .logo {
                        max-width: 100px; /* Larger logo for letter size */
                        max-height: 100px;
                    }
                    
                    .company-details {
                        flex: 1;
                        text-align: center;
                    }
                    
                    .company-details h4 {
                        margin: 0;
                        font-size: 18px;
                        font-weight: bold;
                    }
                    
                    .company-details p {
                        margin: 5px 0;
                        font-size: 11px;
                    }
                    
                    .receipt-title {
                        text-align: center;
                        font-weight: bold;
                        font-size: 20px;
                        margin-top: 15px;
                        margin-bottom: 15px;
                        color: white;
                        background-color: #0066cc;
                        padding: 10px;
                        border-radius: 5px;
                    }
                    
                    .receipt-info {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 20px;
                    }
                    
                    .receipt-info .left-info {
                        flex: 1;
                    }
                    
                    .receipt-info .right-info {
                        flex: 1;
                        text-align: right;
                    }
                    
                    .receipt-info p {
                        margin: 5px 0;
                        font-size: 12px;
                    }
                    
                    .receipt-info strong {
                        font-weight: bold;
                    }
                    
                    .receipt-items {
                        margin-bottom: 20px;
                    }
                    
                    .receipt-items table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    
                    .receipt-items th, .receipt-items td {
                        padding: 8px;
                        text-align: left;
                        border-bottom: 1px solid #ddd;
                        font-size: 11px;
                    }
                    
                    .receipt-items th {
                        font-weight: bold;
                        text-align: center;
                        border: 1px solid #000;
                        background-color: #0066cc;
                        color: white;
                    }
                    
                    .receipt-items .qty {
                        text-align: center;
                        font-weight: bold;
                    }
                    
                    .receipt-items .total {
                        text-align: right;
                    }
                    
                    .receipt-items .unit {
                        text-align: center;
                    }
                    
                    .receipt-items tbody tr:last-child {
                        border-bottom: 2px solid #000;
                    }
                    
                    .receipt-totals {
                        margin-top: 20px;
                    }
                    
                    .receipt-totals table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    
                    .receipt-totals td {
                        padding: 5px;
                        text-align: right;
                        font-size: 12px;
                    }
                    
                    .receipt-totals .label {
                        text-align: right;
                        font-weight: bold;
                    }
                    
                    .receipt-totals .grand-total {
                        border-top: 1px solid #ddd;
                        font-weight: bold;
                        font-size: 14px;
                        background-color: #0066cc;
                        color: white;
                    }
                    
                    .receipt-footer {
                        margin-top: 20px;
                        text-align: center;
                        font-size: 10px;
                        color: #777;
                        border-top: 1px solid #ddd;
                        padding-top: 10px;
                    }
                    
                    .model-unit-container {
                        display: flex;
                        flex-direction: column;
                    }
                    
                    .model-unit-row {
                        display: flex;
                        margin-bottom: 5px;
                    }
                    
                    .model-unit-label {
                        font-weight: bold;
                        width: 60px;
                        font-size: 10px;
                    }
                    
                    .serial-container {
                        display: flex;
                        flex-direction: column;
                    }
                    
                    .serial-row {
                        margin-bottom: 5px;
                        font-size: 10px;
                    }
                    
                    /* Updated styling for serial numbers */
                    .serial-row .indoor-serial {
                        margin-bottom: 3px;
                    }
                    
                    .computation-section {
                        margin-top: 20px;
                        border-top: 1px solid #ddd;
                        padding-top: 15px;
                    }
                    
                    .computation-section table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    
                    .computation-section tr {
                        border-bottom: 1px solid #eee;
                    }
                    
                    .computation-section td {
                        padding: 8px 5px;
                        font-size: 12px;
                    }
                    
                    .computation-section .label {
                        text-align: right;
                        font-weight: bold;
                    }
                    
                    .computation-section .value {
                        text-align: right;
                        width: 120px;
                    }
                    
                    .computation-section .grand-total {
                        font-weight: bold;
                        font-size: 14px;
                        border-top: 1px solid #000;
                        background-color: #0066cc;
                        color: white;
                    }
                    
                    .text-danger {
                        color: #dc3545;
                    }
                    
                    /* Print-specific adjustments */
                    @media print {
                        body {
                            width: 7.5in; /* Ensure exact width for letter size with margins */
                            margin: 0;
                            padding: 0;
                        }
                        
                        .receipt-container {
                            padding: 0;
                        }
                        
                        .receipt-footer {
                            margin-bottom: 0;
                            padding-bottom: 0;
                        }
                        
                        .computation-section {
                            margin-bottom: 0;
                            padding-bottom: 0;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="receipt-container">
                    <div class="receipt-header">
                        <div class="logo-container">
                            <!-- Updated logo path to use uploads folder -->
                            <img src="${logoSrc}" alt="Company Logo" class="logo">
                        </div>
                        <div class="company-details">
                            <h4>${toUpperCase(trimText('HARDYMAN HARDWARE AIRCONDITIONING'))}</h4>
                            <p>${toUpperCase(trimText('TIN# 008-237-300-000'))}</p>
                            <p>${toUpperCase(trimText('National Road, Brgy. Odiong Roxas, Oriental Mindoro'))}</p>
                            <p>${toUpperCase(trimText('Phone: 09094644707 | Email: hardyman.airconditioning@gmail.com'))}</p>
                        </div>
                    </div>
                    
                    <div class="receipt-title">${toUpperCase('ORDER FORM')}</div>
                    
                    <div class="receipt-info">
                        <div class="left-info">
                            <p><strong>${toUpperCase('Invoice #')}:</strong> ${toUpperCase(trimText(invoiceNumber))}</p>
                            <p><strong>${toUpperCase('Transaction #')}:</strong> ${toUpperCase(transactionNumber)}</p>
                        </div>
                        <div class="right-info">
                            <p><strong>${toUpperCase('Date')}:</strong> ${trimText(dateCreated)}</p>
                            <p><strong>${toUpperCase('Customer')}:</strong> ${toUpperCase(trimText(customer))}</p>
                        </div>
                    </div>
                    
                    <div class="receipt-items">
                        <table>
                            <thead>
                                <tr>
                                    <th width="8%">${toUpperCase('Qty')}</th>
                                    <th width="28%">${toUpperCase('Model Unit')}</th>
                                    <th width="20%">${toUpperCase('Serial #')}</th>
                                    <th width="26%">${toUpperCase('Description')}</th>
                                    <th width="8%">${toUpperCase('Unit')}</th>
                                    <th width="10%">${toUpperCase('Amount')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${function() {
                                    let rowsHtml = '';
                                    
                                    // Process each group
                                    for (let groupKey in itemGroups) {
                                        let group = itemGroups[groupKey];
                                        let qty = group.qty;
                                        let total = group.total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        let indoor = group.indoor;
                                        let outdoor = group.outdoor;
                                        let description = group.description;
                                        let unit = group.unit;
                                        
                                        // Format model unit with indoor and outdoor on separate lines
                                        var modelUnit = `
                                            <div class="model-unit-container">
                                                <div class="model-unit-row">
                                                    <div class="model-unit-label">${toUpperCase('Indoor')}:</div>
                                                    <div>${indoor}</div>
                                                </div>
                                                <div class="model-unit-row">
                                                    <div class="model-unit-label">${toUpperCase('Outdoor')}:</div>
                                                    <div>${outdoor}</div>
                                                </div>
                                            </div>
                                        `;
                                        
                                        // Format serial numbers - list all serials for this group
                                        var serialNumberHtml = '';
                                        for (let i = 0; i < group.serials.length; i++) {
                                            let serial = group.serials[i];
                                            serialNumberHtml += `
                                                <div class="serial-row">
                                                    <div class="indoor-serial">${serial.indoor}</div>
                                                    <div class="outdoor-serial">${serial.outdoor}</div>
                                                </div>
                                            `;
                                        }
                                        
                                        rowsHtml += `
                                            <tr>
                                                <td class="qty"><strong>${qty}</strong></td>
                                                <td>${modelUnit}</td>
                                                <td>${serialNumberHtml}</td>
                                                <td>${description}</td>
                                                <td class="unit">${unit}</td>
                                                <td class="total"><strong>${total}</strong></td>
                                            </tr>
                                        `;
                                    }
                                    
                                    return rowsHtml;
                                }()}
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- VAT Computation Section - Updated to remove Amount Due and Add: VAT rows -->
                    <div class="computation-section">
                        <table>
                            <tr>
                                <td class="label">${toUpperCase('Total Sales (VAT Inclusive)')}</td>
                                <td class="value"><strong>${totalSalesVATInclusive}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">${toUpperCase('Less: VAT')}</td>
                                <td class="value text-danger"><strong>-${vatAmountFormatted}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">${toUpperCase('Amount: Net of VAT')}</td>
                                <td class="value"><strong>${netOfVATFormatted}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">${toUpperCase('Less: Discount Total')}</td>
                                <td class="value text-danger"><strong>-${discount}</strong></td>
                            </tr>
                            <tr>
                                <td class="label">${toUpperCase('Less: Withholding Tax')}</td>
                                <td class="value text-danger"><strong>-${tax}</strong></td>
                            </tr>
                            <tr class="grand-total">
                                <td class="label">${toUpperCase('TOTAL AMOUNT DUE:')}</td>
                                <td class="value"><strong>${totalAmountDueFormatted}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="receipt-footer">
                        <p>${toUpperCase(trimText('Thank you for your business!'))}</p>
                    </div>
                </div>
            </body>
            </html>
        `;
        
        nw.document.write(receiptHTML);
        nw.document.close();
        setTimeout(() => {
            nw.print();
            setTimeout(() => {
                nw.close();
                end_loader();
            }, 200);
        }, 500);
    });
});
</script>
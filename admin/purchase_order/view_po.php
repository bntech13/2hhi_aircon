<?php
// Rigorous and secure ID validation from URL
 $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// If ID is missing, invalid, or not a positive integer, show an error and stop.
if ($id === false || $id === null) {
    echo '<div class="alert alert-danger">ERROR: A VALID PURCHASE ORDER ID IS REQUIRED. PLEASE GO BACK TO THE LIST AND TRY AGAIN.</div>';
    echo '<script>setTimeout(function(){ window.location.href = "'.base_url.'/admin?page=purchase_order"; }, 3000);</script>';
    return;
}

// Use prepared statement to prevent SQL injection and fetch PO data
 $po_data = [];
// Modified query to include ORDER BY clause to ensure newest POs appear first
 $stmt = $conn->prepare("SELECT p.*, COALESCE(s.name, p.supplier_name) as supplier FROM purchase_order_list p LEFT JOIN supplier_list s ON p.supplier_id = s.id WHERE p.id = ? ORDER BY p.date_created DESC, p.id DESC");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $po_data = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-warning">ERROR: PURCHASE ORDER WITH THE SPECIFIED ID WAS NOT FOUND.</div>';
        echo '<script>setTimeout(function(){ window.location.href = "'.base_url.'/admin?page=purchase_order"; }, 3000);</script>';
        return;
    }
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">DATABASE QUERY FAILED TO PREPARE.</div>';
    return;
}

// Function to format date properly
function formatDisplayDate($dateString) {
    if (empty($dateString)) {
        return 'N/A';
    }
    
    // Try to create a DateTime object
    try {
        $date = new DateTime($dateString);
        return strtoupper($date->format('M d, Y'));
    } catch (Exception $e) {
        // If DateTime fails, try strtotime
        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return 'INVALID DATE';
        }
        return strtoupper(date('M d, Y', $timestamp));
    }
}

// Calculate grand total dynamically
 $sub_total = floatval($po_data['sub_total'] ?? 0);
 $discount = floatval($po_data['discount'] ?? 0);
 $tax = floatval($po_data['tax'] ?? 0);
 $grand_total = $sub_total - $discount + $tax;

// Start with the main PO record as the first item
 $all_items = [$po_data];

// Get all other items for this PO using the po or po_code if available
 $po_identifier = $po_data['po'] ?? $po_data['po_code'] ?? '';

if (!empty($po_identifier)) {
    // Modified query to include ORDER BY clause to ensure newest items appear first
    $items_stmt = $conn->prepare("SELECT * FROM purchase_order_list WHERE (po = ? OR po_code = ?) AND id != ? ORDER BY date_created DESC, id DESC");
    if ($items_stmt) {
        $items_stmt->bind_param("ssi", $po_identifier, $po_identifier, $id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        while ($row = $items_result->fetch_assoc()) {
            // Add the other items to our list
            $all_items[] = $row;
        }
        $items_stmt->close();
    }
}

// Pre-calculate the total item count for the heading
 $total_item_count = 0;
 $main_po_identifier = $po_data['po'] ?? $po_data['po_code'] ?? '';
foreach ($all_items as $row) {
    $item_po_identifier = $row['po'] ?? $row['po_code'] ?? '';
    if (!empty($main_po_identifier) && $item_po_identifier !== $main_po_identifier) {
        continue;
    }
    $total_item_count++;
}

// Calculate the first 10 items for print view (fallback if no filter is set)
 $print_items = [];
 $print_count = 0;
foreach ($all_items as $row) {
    $item_po_identifier = $row['po'] ?? $row['po_code'] ?? '';
    if (!empty($main_po_identifier) && $item_po_identifier !== $main_po_identifier) {
        continue;
    }
    
    if ($print_count < 10) {
        $print_items[] = $row;
        $print_count++;
    }
}

// Calculate totals for print view (fallback if no filter is set)
 $print_subtotal = 0;
foreach ($print_items as $item) {
    $print_subtotal += floatval($item['total'] ?? 0);
}
 $print_grand_total = $print_subtotal - $discount + $tax;
?>

<div class="card card-outline card-primary">

    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div style="display: inline-block; border: 1px solid #000080; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
                <h5 style="color: #000080; margin: 0;" class="fas fa-shopping-cart" class="card-title"> <b>PURCHASED DETAILS</h5>
            </div>
            <!-- Buttons moved here and aligned to the right -->
            <div class="d-flex gap-2">
                <button class="btn btn-flat btn-success" type="button" id="print"><i class="fas fa-print"></i> PRINT</button>
                <a class="btn btn-flat btn-primary" href="<?php echo base_url . '/admin?page=purchase_order/manage_po&id=' . $id ?>"><i class="fas fa-edit"></i> EDIT</a>
                <a class="btn btn-flat btn-dark" href="<?php echo base_url . '/admin?page=purchase_order' ?>"><i class="fas fa-arrow-left"></i> BACK TO LIST</a>
            </div>
        </div>
        
        <!-- PO Details for Print View - Hidden on Screen -->
        <div class="po-details-print-container d-none">
            <div class="d-flex justify-content-between">
                <div><strong>PO NO.:</strong> <?php echo htmlspecialchars(strtoupper($po_data['po'] ?? $po_data['po_code'] ?? 'N/A')); ?></div>
                <div><strong>SUPPLIER:</strong> <?php echo htmlspecialchars(strtoupper(trim($po_data['supplier'] ?? 'N/A'))); ?></div>
                <div><strong>DR NO.:</strong> <?php echo htmlspecialchars(strtoupper($po_data['dr'] ?? 'N/A')); ?></div>
                <div><strong>INVOICE NO.:</strong> <?php echo htmlspecialchars(strtoupper($po_data['invoice'] ?? 'N/A')); ?></div>
                <div><strong>DELIVERY DATE:</strong> <?php echo formatDisplayDate($po_data['delivery_date']); ?></div>
            </div>
        </div>
    </div>

    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <!-- Main PO Details - Using table for perfect alignment -->
            <div class="row mb-3 po-details-row">
                <div class="col-12">
                    <table class="table table-borderless po-details-table">
                        <tr>
                            <td width="15%">
                                <label class="control-label text-info font-weight-bold">P.O NO.</label>
                                <div class="border-bottom pb-1"><strong><?php echo htmlspecialchars(strtoupper($po_data['po'] ?? $po_data['po_code'] ?? 'N/A')); ?></strong></div>
                            </td>
                            <td width="25%">
                                <label class="control-label text-info font-weight-bold">SUPPLIER</label>
                                <div class="border-bottom pb-1"><strong><?php echo htmlspecialchars(strtoupper(trim($po_data['supplier'] ?? 'N/A'))); ?></strong></div>
                            </td>
                            <td width="15%">
                                <label class="control-label text-info font-weight-bold">DR NO.</label>
                                <div class="border-bottom pb-1"><strong><?php echo htmlspecialchars(strtoupper($po_data['dr'] ?? 'N/A')); ?></strong></div>
                            </td>
                            <td width="15%">
                                <label class="control-label text-info font-weight-bold">INVOICE NO.</label>
                                <div class="border-bottom pb-1"><strong><?php echo htmlspecialchars(strtoupper($po_data['invoice'] ?? 'N/A')); ?></strong></div>
                            </td>
                            <td width="30%">
                                <label class="control-label text-info font-weight-bold">DELIVERY DATE</label>
                                <div class="border-bottom pb-1"><strong><?php echo formatDisplayDate($po_data['delivery_date']); ?></strong></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Items Table -->
            <hr>
            <!-- HEADING MODIFIED HERE - Added search bar and range filter -->
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <h4 class="text-info mb-0"><i class="fas fa-list"></i> TOTAL ITEM: <span id="currentItemCount"><?php echo $total_item_count; ?></span></h4>
                <div class="d-flex align-items-center gap-3">
                    <div class="range-filter-container">
                        <label class="text-info font-weight-bold mr-2">Number from:</label>
                        <input type="number" id="rangeFrom" class="form-control" style="width: 80px; display: inline-block;" min="1" max="<?php echo $total_item_count; ?>" placeholder="1">
                        <label class="text-info font-weight-bold mx-2">to:</label>
                        <input type="number" id="rangeTo" class="form-control" style="width: 80px; display: inline-block;" min="1" max="<?php echo $total_item_count; ?>" placeholder="<?php echo $total_item_count; ?>">
                        <button id="applyRangeFilter" class="btn btn-sm btn-primary ml-2"><i class="fas fa-filter"></i> Apply</button>
                        <button id="clearRangeFilter" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-times"></i> Clear</button>
                    </div>
                    <div class="search-container">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search items...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-striped table-bordered sticky-header">
                    <thead class="bg-navy text-white">
                        <tr>
                            <th class="text-center py-2 px-3">#</th>
                            <th class="text-center py-2 px-3">INDOOR</th>
                            <th class="text-center py-2 px-3">INDOOR SERIAL</th>
                            <th class="text-center py-2 px-3">OUTDOOR</th>
                            <th class="text-center py-2 px-3">OUTDOOR SERIAL</th>
                            <th class="text-center py-2 px-3">UNIT</th>
                            <th class="py-2 px-3">DESCRIPTION</th>
                            <th class="text-center py-2 px-3">PRICE</th>
                            <th class="text-center py-2 px-3">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $calculated_sub_total = 0;
                        $row_number = 1; // Initialize row counter
                        
                        foreach ($all_items as $row):
                            // Skip items that don't belong to this PO
                            $item_po_identifier = $row['po'] ?? $row['po_code'] ?? '';
                            if (!empty($main_po_identifier) && $item_po_identifier !== $main_po_identifier) {
                                continue;
                            }
                            
                            $item_total = floatval($row['total'] ?? 0);
                            $calculated_sub_total += $item_total;
                            $brand = htmlspecialchars(strtoupper($row['brand'] ?? ''));
                            $type = htmlspecialchars(strtoupper($row['type'] ?? ''));
                            $hp = htmlspecialchars(strtoupper($row['hp'] ?? ''));
                            
                            // Build item description in the requested format
                            $description_parts = [];
                            if (!empty($brand)) {
                                $description_parts[] = $brand;
                                if (!empty($hp)) {
                                    $description_parts[count($description_parts)-1] .= ' (' . $hp . ' HP)';
                                }
                            }
                            if (!empty($type)) {
                                $description_parts[] = $type . ' TYPE';
                            }
                            if (!empty($row['series'] ?? '')) {
                                $description_parts[] = "" . strtoupper($row['series']) . "";
                            }
                            $item_description = implode(', ', $description_parts);
                    ?>
                        <tr data-row-number="<?php echo $row_number; ?>">
                            <td class="py-2 px-3 text-center">
                                <strong><?php echo $row_number; ?></strong>
                            </td>
                            <td class="py-2 px-3 text-center">
                                <strong><?php echo !empty($row['indoor']) ? htmlspecialchars(strtoupper($row['indoor'])) : '-'; ?></strong>
                            </td>
                            <td class="py-2 px-3 text-center">
                                <strong><?php echo !empty($row['indoor_serial']) ? htmlspecialchars(strtoupper($row['indoor_serial'])) : '-'; ?></strong>
                            </td>
                            <td class="py-2 px-3 text-center">
                                <strong><?php echo !empty($row['outdoor']) ? htmlspecialchars(strtoupper($row['outdoor'])) : '-'; ?></strong>
                            </td>
                            <td class="py-2 px-3 text-center">
                                <strong><?php echo !empty($row['outdoor_serial']) ? htmlspecialchars(strtoupper($row['outdoor_serial'])) : '-'; ?></strong>
                            </td>
                            <td class="py-2 px-3 text-center"><strong>SETS</strong></td>
                            <td class="py-2 px-3"><strong><?php echo $item_description; ?></strong></td>
                            <td class="py-2 px-3 text-right"><strong><?php echo number_format(floatval($row['price'] ?? 0), 2) ?></strong></td>
                            <td class="py-2 px-3 text-right font-weight-bold"><?php echo number_format($item_total, 2) ?></td>
                        </tr>
                    <?php 
                        $row_number++; // Increment row counter
                        endforeach; 
                    ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th class="text-right py-2 px-3" colspan="8">SUB-TOTAL</th>
                            <th class="text-right py-2 px-3" id="footer-subtotal"><?php echo number_format(floatval($po_data['sub_total'] ?? 0), 2) ?></th>
                        </tr>
                        <?php if (isset($po_data['discount']) && floatval($po_data['discount']) > 0): ?>
                        <tr>
                            <th class="text-right py-2 px-3" colspan="8">DISCOUNT <?php echo isset($po_data['discount_perc']) && $po_data['discount_perc'] > 0 ? '('.$po_data['discount_perc'].'%)' : '' ?></th>
                            <th class="text-right py-2 px-3 text-danger" id="footer-discount">-<?php echo number_format(floatval($po_data['discount']), 2) ?></th>
                        </tr>
                        <?php endif; ?>
                        <?php if (isset($po_data['tax']) && floatval($po_data['tax']) > 0): ?>
                        <tr>
                            <th class="text-right py-2 px-3" colspan="8">TAX <?php echo isset($po_data['tax_perc']) && $po_data['tax_perc'] > 0 ? '('.$po_data['tax_perc'].'%)' : '' ?></th>
                            <th class="text-right py-2 px-3 text-success" id="footer-tax">+<?php echo number_format(floatval($po_data['tax']), 2) ?></th>
                        </tr>
                        <?php endif; ?>
                        <tr class="bg-primary text-white grand-total-row">
                            <th class="text-right py-2 px-3" colspan="8"><strong>GRAND TOTAL</strong></th>
                            <th class="text-right py-2 px-3" id="footer-grand-total">
                                <strong><?php echo number_format($grand_total, 2); ?></strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Remarks Section -->
            <?php if (isset($po_data['remarks']) && !empty(trim($po_data['remarks']))): ?>
            <hr>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="text-info"><i class="fas fa-comment"></i> REMARKS</h5>
                    <div class="card">
                        <div class="card-body">
                            <strong><?php echo nl2br(htmlspecialchars(strtoupper($po_data['remarks']))) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
 $(function(){
    // Store original discount and tax values from PHP
    const originalDiscount = <?php echo floatval($po_data['discount'] ?? 0); ?>;
    const originalTax = <?php echo floatval($po_data['tax'] ?? 0); ?>;
    const totalItemCount = <?php echo $total_item_count; ?>;
    
    // Store print-specific data from PHP (fallback if no filter is set)
    const fallbackPrintSubtotal = <?php echo $print_subtotal; ?>;
    const fallbackPrintGrandTotal = <?php echo $print_grand_total; ?>;
    const fallbackPrintItemCount = <?php echo count($print_items); ?>;

    /**
     * Applies both search and range filters to the table
     */
    function applyFilters() {
        const searchValue = $('#searchInput').val().toLowerCase();
        const rangeFrom = parseInt($('#rangeFrom').val()) || 1;
        const rangeTo = parseInt($('#rangeTo').val()) || totalItemCount;
        
        $('#print_out .table tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            const rowNumber = parseInt($(this).data('row-number'));
            
            const matchesSearch = searchValue === '' || rowText.indexOf(searchValue) > -1;
            const matchesRange = rowNumber >= rangeFrom && rowNumber <= rangeTo;
            
            $(this).toggle(matchesSearch && matchesRange);
        });
        
        // Update visible items count
        const visibleCount = $('#print_out .table tbody tr:visible').length;
        $('#currentItemCount').text(visibleCount + ' (of ' + totalItemCount + ')');

        // Recalculate totals based on the filtered results
        calculateTotals();
    }

    /**
     * Calculates and updates the subtotal and grand total based on visible rows.
     */
    function calculateTotals() {
        let subtotal = 0.0;
        // Iterate through only the visible rows in the table body
        $('#print_out .table tbody tr:visible').each(function() {
            // Get the text from the "TOTAL" column (the last <td>)
            const rowTotalText = $('td:last', this).text();
            // Convert to a number by removing commas and parsing
            const rowTotal = parseFloat(rowTotalText.replace(/,/g, ''));
            if (!isNaN(rowTotal)) {
                subtotal += rowTotal;
            }
        });

        // Calculate the grand total
        const grandTotal = subtotal - originalDiscount + originalTax;

        // Format numbers to two decimal places with commas
        function formatNumber(num) {
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Update the footer cells with the new calculated values
        $('#footer-subtotal').text(formatNumber(subtotal));
        $('#footer-grand-total').text(formatNumber(grandTotal));
    }

    $('#print').click(function(){
        // Check if range filter is set
        const rangeFrom = $('#rangeFrom').val();
        const rangeTo = $('#rangeTo').val();
        
        if (rangeFrom === '' || rangeTo === '') {
            // Show warning if filter is not set
            alert('WARNING: Please set a range filter before printing. This is required to specify which items to print.');
            return;
        }
        
        start_loader();
        var _el = $('<div>');
        var _head = $('head').clone();
        _head.find('title').text("PURCHASE ORDER DETAILS - PRINT VIEW");
        
        // Clone the entire card container for printing
        var p = $('.card.card-outline.card-primary').clone();
        
        // Remove buttons and filters from the cloned print view
        p.find('.card-header .d-flex.gap-2').remove();
        p.find('.search-container').remove();
        p.find('.range-filter-container').remove();
        
        // Remove the PO details table when printing
        p.find('.po-details-row').remove();
        
        // Show the PO details print container
        p.find('.po-details-print-container').removeClass('d-none');
        
        // Update the "TOTAL ITEM" count in the print view to show the filtered items
        const printItemCount = $('#print_out .table tbody tr:visible').length;
        p.find('#currentItemCount').text(printItemCount);
        
        // Remove all rows from the table body
        p.find('.table tbody tr').remove();
        
        // Add only the visible (filtered) rows to the print view
        $('#print_out .table tbody tr:visible').each(function() {
            var rowClone = $(this).clone();
            p.find('.table tbody').append(rowClone);
        });
        
        // Recalculate the subtotal for the print view based on the visible rows
        let printSubtotal = 0;
        $('#print_out .table tbody tr:visible').each(function() {
            const rowTotalText = $('td:last', this).text();
            const rowTotal = parseFloat(rowTotalText.replace(/,/g, ''));
            if (!isNaN(rowTotal)) {
                printSubtotal += rowTotal;
            }
        });
        
        // Calculate the grand total for the print view
        const printGrandTotal = printSubtotal - originalDiscount + originalTax;
        
        // Format numbers to two decimal places with commas
        function formatNumber(num) {
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        // Move footer rows to the end of the table body for printing
        var footerRows = p.find('.table tfoot tr').clone();
        p.find('.table tfoot').remove(); // Remove the original footer
        
        // Add a class to identify these as footer rows in print view
        footerRows.addClass('print-footer-row');
        
        // Append the footer rows to the table body
        p.find('.table tbody').append(footerRows);
        
        // Update the footer totals with the print-specific values
        p.find('#footer-subtotal').text(formatNumber(printSubtotal));
        p.find('#footer-grand-total').text(formatNumber(printGrandTotal));
        
        // Ensure discount and tax rows are updated if they exist
        if (originalDiscount > 0) {
            p.find('#footer-discount').text('-' + formatNumber(originalDiscount));
        }
        if (originalTax > 0) {
            p.find('#footer-tax').text('+' + formatNumber(originalTax));
        }
        
        // Set page size to letter landscape with minimal margins for one page
        var printStyle = $('<style>')
            .prop('type', 'text/css')
            .html(`
                @page {
                    size: letter landscape;
                    margin: 0.15in;
                }
                @media print {
                    * {
                        -webkit-print-color-adjust: exact !important;
                        color-adjust: exact !important;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        font-size: 8pt;
                    }
                    .card {
                        border: 1px solid #007bff !important;
                        margin: 0;
                        font-size: 8pt;
                    }
                    .card-header {
                        background-color: #f8f9fa !important;
                        border-bottom: 1px solid #007bff !important;
                        padding: 0.2rem 0.3rem !important;
                    }
                    .card-body {
                        padding: 0.3rem !important;
                    }
                    .container-fluid {
                        padding: 0;
                    }
                    .table {
                        font-size: 7pt;
                        margin-bottom: 0;
                    }
                    .table thead th {
                        background-color: #343a40 !important;
                        color: white !important;
                        padding: 0.1rem 0.2rem !important;
                        font-size: 7pt !important;
                        height: 15px !important;
                        line-height: 1;
                    }
                    .table tbody td {
                        padding: 0.1rem 0.2rem !important;
                        font-size: 7pt !important;
                        height: 15px !important;
                        line-height: 1;
                    }
                    .text-info {
                        color: #17a2b8 !important;
                    }
                    .border-bottom {
                        border-bottom: 1px solid #dee2e6 !important;
                    }
                    .po-details-table td {
                        padding: 0 1px !important;
                        font-size: 7pt;
                    }
                    .po-details-table .control-label {
                        font-size: 6pt !important;
                        margin-bottom: 0 !important;
                    }
                    .po-details-table .border-bottom {
                        font-size: 7pt !important;
                    }
                    .table-responsive {
                        overflow: visible !important;
                        max-height: none !important;
                    }
                    h5 {
                        font-size: 10pt !important;
                        margin-bottom: 0.2rem !important;
                        line-height: 1;
                        color: #000080 !important;
                    }
                    h4 {
                        font-size: 8pt !important;
                        margin-bottom: 0.2rem !important;
                        line-height: 1;
                    }
                    .card {
                        page-break-inside: avoid;
                    }
                    hr {
                        margin: 0.2rem 0 !important;
                    }
                    .row {
                        margin-bottom: 0.2rem !important;
                    }
                    .btn {
                        display: none !important;
                    }
                    /* Ensure table rows don't break across pages */
                    .table tbody tr {
                        page-break-inside: avoid;
                    }
                    /* Compact table layout */
                    .table {
                        border-collapse: collapse !important;
                    }
                    .table th, .table td {
                        border: 1px solid #dee2e6 !important;
                    }
                    /* PO Details Print Container Styling */
                    .po-details-print-container {
                        display: block !important;
                        margin-top: 0.3rem;
                        margin-bottom: 0.3rem;
                        font-size: 7pt;
                    }
                    .po-details-print-container .d-flex {
                        justify-content: space-between;
                    }
                    .po-details-print-container .d-flex > div {
                        margin-right: 5px;
                    }
                    /* Footer rows styling in print view */
                    .print-footer-row th {
                        padding: 0.1rem 0.2rem !important;
                        font-size: 7pt !important;
                        height: 15px !important;
                        line-height: 1;
                        background-color: #f8f9fa !important;
                    }
                    .print-footer-row.text-danger th {
                        color: #dc3545 !important;
                    }
                    .print-footer-row.text-success th {
                        color: #28a745 !important;
                    }
                    .print-footer-row.grand-total-row th {
                        font-weight: bold !important;
                        font-size: 8pt !important;
                        background-color: #007bff !important;
                        color: white !important;
                    }
                    /* Ensure grand total appears at the very end */
                    .print-footer-row.grand-total-row {
                        page-break-inside: avoid !important;
                        page-break-after: auto !important;
                    }
                    /* Navy blue header styling for print */
                    .card-header div[style*="border: 1px solid"] {
                        border-color: #000080 !important;
                    }
                }
            `);
        _head.append(printStyle);
        
        _el.append(_head);
        _el.append(p);
        
        var nw = window.open("","","width=1200,height=900,left=250,location=no,titlebar=yes");
        nw.document.write(_el.html());
        nw.document.close();
        setTimeout(() => {
            nw.print();
            setTimeout(() => {
                nw.close();
                end_loader();
            }, 200);
        }, 500);
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        applyFilters();
    });
    
    // Range filter functionality
    $('#applyRangeFilter').click(function() {
        applyFilters();
    });
    
    // Clear range filter
    $('#clearRangeFilter').click(function() {
        $('#rangeFrom').val('');
        $('#rangeTo').val('');
        applyFilters();
    });
    
    // Allow Enter key to apply range filter
    $('#rangeFrom, #rangeTo').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            applyFilters();
        }
    });
    
    // Initial calculation on page load
    calculateTotals();
    
    // Add blue highlight effect on hover
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            /* Table row hover effect */
            .table tbody tr:hover {
                background-color: rgba(0, 123, 255, 0.69) !important;
                cursor: pointer;
            }
            
            /* Button hover effects */
            .btn:hover {
                box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
                transform: translateY(-2px);
                transition: all 0.3s ease;
            }
            
            /* Link hover effects */
            .card-footer a:hover {
                color: #0056b3 !important;
                text-decoration: underline;
                transition: all 0.3s ease;
            }
            
            /* Header info hover effect */
            .border-bottom:hover {
                background-color: rgba(0, 123, 255, 0.05);
                transition: background-color 0.3s ease;
            }
            
            /* Remarks card hover effect */
            .card:hover {
                box-shadow: 0 4px 8px rgba(0, 123, 255, 0.67);
                transition: box-shadow 0.3s ease;
            }

            /* Sticky Table Header */
            .sticky-header thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                background-color: #343a40; /* Match bg-navy color */
            }
            
            /* Search bar styling */
            .search-container {
                width: 300px;
            }
            
            .search-container .input-group-text {
                background-color: #000080;
                color: white;
                border: 1px solid #000080;
            }
            
            .search-container .form-control:focus {
                border-color: #000080;
                box-shadow: 0 0 0 0.2rem rgba(0, 0, 128, 0.25);
            }
            
            /* Range filter styling */
            .range-filter-container {
                display: flex;
                align-items: center;
            }
            
            .range-filter-container .form-control {
                width: 70px;
            }
            
            .range-filter-container label {
                margin-bottom: 0;
                white-space: nowrap;
            }
            
            /* PO Details Table Styling */
            .po-details-table {
                margin-bottom: 0;
            }
            
            .po-details-table td {
                vertical-align: top;
                padding: 0 5px;
            }
            
            .po-details-table .control-label {
                margin-bottom: 2px;
            }
            
            /* Row number column styling */
            .table th:first-child,
            .table td:first-child {
                width: 40px;
                text-align: center;
            }
            
            /* PO Details Print Container - Hidden on screen */
            .po-details-print-container {
                display: none;
            }
            
            /* Warning styling for range filter */
            .range-filter-container {
                position: relative;
            }
            
            .range-filter-container:after {
                content: "*";
                color: red;
                position: absolute;
                right: -10px;
                top: 0;
                font-weight: bold;
            }
            
            /* Highlight grand total on screen */
            #footer-grand-total {
                font-weight: bold;
                color: #007bff;
            }
        `)
        .appendTo('head');
})
</script>
<?php
// Note: Sales order number generation has been removed
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
        
        /* Brand items container */
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
            background-color: #fdfdfdff;
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
            background-color: #fffcfcff; /* Light blue highlight on hover */
            cursor: pointer;
            transition: background-color 0.2s ease;
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
        #list {
            border: 2px solid #021324ff; /* Bold outer border */
            border-collapse: collapse; /* Ensure borders don't double up */
            background-color: #ffffff; /* Set table background to white */
        }

        #list th {
            background-color: #021324ff;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-align: center;
            border: 2px solid #021324ff; /* Bold header borders */
            padding: 8px 4px; /* Adjust padding for better appearance */
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif; /* Added Arial font */
        }

        #list td {
            vertical-align: middle !important;
            text-align: center !important;
            font-weight: bold !important; /* Ensure bold text */
            border: 2px solid #021324ff; /* Bold cell borders */
            padding: 12px 8px !important; /* Increased padding for better appearance */
            background-color: #ffffff; /* Set cell background to white */
            font-family: Arial, sans-serif !important; /* Added Arial font */
        }

        /* Input styling in table */
        #list input[type="number"] {
            width: 50px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #021324ff; /* Bold input border */
            padding: 4px; /* Add padding to inputs */
            font-family: Arial, sans-serif; /* Added Arial font */
        }

        /* Price column specific styling */
        #list td:nth-child(6) { /* Updated from 7 to 6 */
            text-align: center !important; /* Center price value with importance */
            font-weight: bold !important;
            color: #070707ff;
            font-family: 'Courier New', monospace;
        }

        /* Brand column specific styling */
        #list td:nth-child(7) { /* Updated from 8 to 7 */
            text-align: center;
            font-weight: bold !important;
            color: black; /* Changed to black */
            font-family: Arial, sans-serif; /* Added Arial font */
        }
        
        /* Row number column specific styling - now at position 1 */
        #list td:nth-child(1) {
            color: black; /* Changed to black */
            font-weight: bold !important;
            /* Removed background color */
            font-family: Arial, sans-serif; /* Added Arial font */
        }
        
        /* Indoor Unit column specific styling - now at position 2 */
        #list td:nth-child(2) {
            color: black; /* Changed to black */
            font-weight: bold !important;
            font-family: Arial, sans-serif; /* Added Arial font */
            text-transform: uppercase !important; /* Added uppercase transformation */
        }

        /* Responsive table */
        @media (max-width: 768px) {
            #list {
                font-size: 0.85rem;
            }
            
            #list th, #list td {
                padding: 4px 2px; /* Reduce padding on mobile */
                border-width: 1px; /* Slightly thinner borders on mobile */
            }
        }

        /* New styles for brand display */
        .brand-display {
            background-color: #e3f2fd;
            border-left: 4px solid #0066cc;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: 600;
            color: #0066cc;
        }
        
        .brand-display i {
            margin-right: 8px;
        }
        
        /* New styles for HP display */
        .hp-display {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: 600;
            color: #2e7d32;
        }
        
        .hp-display i {
            margin-right: 8px;
        }
        
        /* New styles for Stock Qty display */
        .stock-qty-display {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: 600;
            color: #e65100;
        }
        
        .stock-qty-display i {
            margin-right: 8px;
        }
        
        /* Brand name styling in table */
        .brand-name {
            font-weight: bold !important;
            color: black; /* Changed to black */
            text-transform: uppercase !important; /* Changed from capitalize to uppercase */
            font-family: Arial, sans-serif; /* Added Arial font */
        }
        
        /* HP sorting indicator */
        .hp-sort-indicator {
            margin-left: 5px;
            font-size: 0.8rem;
            color: #0066cc;
        }
        
        /* Special styling for merged row number cells */
        .merged-row {
            border: 3px solid #0066cc !important;
            font-weight: bold !important;
            color: black !important;
            position: relative;
            z-index: 1;
            text-align: center;
            vertical-align: middle;
            background-color: #ffffff !important; /* Set merged cell background to white */
            font-family: Arial, sans-serif !important; /* Added Arial font */
        }
        
        /* Hide the borders for cells that are part of a merged row group */
        .hide-row-top-border {
            border-top: none !important;
        }
        
        .hide-row-bottom-border {
            border-bottom: none !important;
        }
        
        /* New style for hidden table */
        .table-container {
            display: none;
        }
        
        /* Style for no brand selected message */
        .no-brand-message {
            text-align: center;
            padding: 40px 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px dashed #dee2e6;
        }
        
        .no-brand-message i {
            font-size: 3rem;
            color: #0066cc;
            margin-bottom: 15px;
        }
        
        .no-brand-message h5 {
            color: #0066cc;
            font-weight: 600;
        }
        
        .no-brand-message p {
            color: #6c757d;
        }
        
        /* Pagination styling */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .pagination-controls {
            display: flex;
            align-items: center;
        }
        
        .pagination-controls button {
            margin: 0 5px;
            border-radius: 4px;
        }
        
        .entries-selector {
            display: flex;
            align-items: center;
        }
        
        .entries-selector label {
            margin-right: 10px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .entries-selector select {
            width: auto;
            display: inline-block;
            padding: 0.375rem 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .entries-selector select:focus {
            color: #495057;
            background-color: #fff;
            border-color: #0066cc;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
        
        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .pagination-info {
                margin-bottom: 10px;
            }
            
            .entries-selector {
                margin-bottom: 10px;
            }
        }
        
        /* Brand select watermark styling */
        .brand-select-wrapper {
            position: relative;
        }
        
        .brand-select {
            width: 100%;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 8px 10px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            appearance: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .brand-select:focus {
            border-color: #0066cc;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
        
        .brand-select option {
            color: #495057;
        }
        
        .brand-select option[value=""] {
            color: #6c757d;
            font-style: italic;
        }
        
        .brand-select-watermark {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
            pointer-events: none;
            color: #6c757d;
            font-style: italic;
            z-index: 1;
        }
        
        .brand-select-wrapper.has-value .brand-select-watermark {
            display: none;
        }
        
        /* HP select watermark styling */
        .hp-select-wrapper {
            position: relative;
        }
        
        .hp-select {
            width: 100%;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 8px 10px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            appearance: none;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .hp-select:focus {
            border-color: #0066cc;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
        
        .hp-select option {
            color: #495057;
        }
        
        .hp-select option[value=""] {
            color: #6c757d;
            font-style: italic;
        }
        
        .hp-select-watermark {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            padding: 0 0.75rem;
            pointer-events: none;
            color: #6c757d;
            font-style: italic;
            z-index: 1;
        }
        
        .hp-select-wrapper.has-value .hp-select-watermark {
            display: none;
        }
        
        /* Filter section styling */
        .filter-section {
            margin-bottom: 10px;
        }
        
        .filter-label {
            font-weight: 600;
            color: #0066cc;
            margin-bottom: 5px;
        }
        
        /* Product row styling - removed hover and selected effects */
        .product-row {
            background-color: #ffffff; /* Set row background to white */
        }
        
        /* Add subtle zebra striping - now with white background */
        #list tbody tr:nth-child(even) {
            background-color: #ffffff; /* Changed to white */
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
        
        /* ROW HIGHLIGHT EFFECT - MAIN RULE */
        #list tbody tr:hover {
            background-color: #29a0e6ff !important; /* Light blue highlight */
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        /* ENSURE TEXT REMAINS BOLD ON HOVER */
        #list tbody tr:hover td {
            background-color: inherit !important;
            color: inherit !important;
            font-weight: bold !important; /* Keep text bold */
            text-shadow: none !important;
        }
        
        /* PRESERVE MERGED CELL STYLING ON HOVER */
        #list tbody tr:hover .merged-row {
            background-color: #ffffff !important; /* Keep white background */
            font-weight: bold !important; /* Ensure text stays bold */
            color: black !important; /* Keep text color */
        }
        
        /* Auto-capitalization styles */
        .capitalize {
            text-transform: capitalize !important;
        }
        
        .uppercase {
            text-transform: uppercase !important;
        }
        
        /* New styles for outdoor unit column */
        #list td:nth-child(4) {
            color: black; /* Changed to black */
            font-weight: bold !important;
            font-family: Arial, sans-serif; /* Added Arial font */
            text-transform: uppercase !important; /* Added uppercase transformation */
        }
        
        /* Clear button styling for selects */
        .select-clear {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            font-size: 14px;
            z-index: 3;
            display: none;
        }
        
        .select-clear:hover {
            color: #dc3545;
        }
        
        .brand-select-wrapper.has-value .select-clear,
        .hp-select-wrapper.has-value .select-clear {
            display: block;
        }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
            <h6 style="color: #0066cc; margin: 0;">
                <i class="fas fa-eye"></i> <b>VIEW STOCKS</b>
            </h6>
        </div>
        
        <button id="refresh-btn" type="button" class="btn btn-outline-primary btn-flat" style="display: flex; align-items: center;">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
    <div class="card-body">
        <form action="" id="sale-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <div class="container-fluid">
                <hr>
                
                <!-- Combined Brand and Search Section -->
                <div class="search-section">
                    <h6><i class="fas fa-search"></i>Filter Options</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group filter-section">
                                <label class="filter-label">Select by Brand</label>
                                <div class="brand-select-wrapper">
                                    <select name="brand_filter" id="brand_filter" class="brand-select">
                                        <option value=""></option>
                                        <option value="all">Select all brand</option>
                                        <?php
                                        // Fetch brands from purchase_order_list instead of item_list
                                        $brandQuery = $conn->query("SELECT DISTINCT brand FROM purchase_order_list WHERE (deleted = 0 OR deleted IS NULL) ORDER BY brand ASC");
                                        while($brandRow = $brandQuery->fetch_assoc()){
                                            echo '<option value="'.$brandRow['brand'].'">'.strtoupper($brandRow['brand']).'</option>';
                                        }
                                        ?>
                                    </select>
                                    <div class="brand-select-watermark">Select Brand</div>
                                    <button type="button" class="select-clear" title="Clear selection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group filter-section">
                                <label class="filter-label">Select by HP</label>
                                <div class="hp-select-wrapper">
                                    <select name="hp_filter" id="hp_filter" class="hp-select">
                                        <option value=""></option>
                                        <option value="0.8">0.8</option>
                                        <option value="1">1</option>
                                        <option value="1.5">1.5</option>
                                        <option value="2">2</option>
                                        <option value="2.5">2.5</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                    </select>
                                    <div class="hp-select-watermark">Select HP</div>
                                    <button type="button" class="select-clear" title="Clear selection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Brand Display Area -->
                <div id="brand-display" class="brand-display" style="display: none;">
                    <i class="fas fa-tag"></i> <span id="selected-brand-name"></span>
                </div>
                
                <!-- HP Display Area -->
                <div id="hp-display" class="hp-display" style="display: none;">
                    <i class="fas fa-tachometer-alt"></i> <span id="selected-hp-name"></span>
                </div>
                
                <!-- Stock Quantity Display Area -->
                <div id="stock-qty-display" class="stock-qty-display" style="display: none;">
                    <i class="fas fa-boxes"></i> <span id="selected-stock-qty"></span>
                </div>
                
                <!-- No Brand Selected Message -->
                <div id="no-brand-message" class="no-brand-message">
                    <i class="fas fa-tags"></i>
                    <h5>Please Select a Brand or HP</h5>
                    <p>Choose a brand or HP from the dropdown menu above to view available stock</p>
                </div>
                
                <!-- Table Container (initially hidden) -->
                <div id="table-container" class="table-container">
                    <hr>
                    <table class="table table-striped table-bordered" id="list">
                        <colgroup>
                            <col width="5%">   <!-- # (Row Number) -->
                            <col width="20%">  <!-- INDOOR UNIT -->
                            <col width="15%">  <!-- INDOOR SERIAL -->
                            <col width="15%">  <!-- OUTDOOR UNIT -->
                            <col width="15%">  <!-- OUTDOOR SERIAL -->
                            <col width="15%">  <!-- PRICE -->
                            <col width="15%" id="brand-col"> <!-- BRAND -->
                        </colgroup>
                        <thead>
                            <tr class="text-light bg-navy">
                                <th class="text-center py-1 px-2">
                                    # <i class="fas fa-sort-amount-up hp-sort-indicator"></i>
                                </th>
                                <th class="text-center py-1 px-2">INDOOR UNIT</th>
                                <th class="text-center py-1 px-2">INDOOR SERIAL</th>
                                <th class="text-center py-1 px-2">OUTDOOR UNIT</th>
                                <th class="text-center py-1 px-2">OUTDOOR SERIAL</th>
                                <th class="text-center py-1 px-2">PRICE</th>
                                <th class="text-center py-1 px-2" id="brand-header">BRAND</th>
                            </tr>
                        </thead>
                        <tbody id="product-tbody">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-arrow-up fa-2x mb-2"></i>
                                        <p>Loading products...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Pagination Controls -->
                    <div class="pagination-container">
                        <div class="entries-selector">
                            <label for="entries-per-page">Show</label>
                            <select id="entries-per-page" class="form-control">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <label style="margin-left: 10px;">entries</label>
                        </div>
                        
                        <div class="pagination-info">
                            Showing <span id="start-entry">0</span> to <span id="end-entry">0</span> of <span id="total-entries">0</span> entries
                        </div>
                        
                        <div class="pagination-controls">
                            <button id="prev-page" type="button" class="btn btn-outline-primary btn-flat" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <button id="next-page" type="button" class="btn btn-outline-primary btn-flat" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

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
    let currentBrand = '';
    let currentHP = '';
    let currentPage = 1;
    let entriesPerPage = 10;
    let totalPages = 0;

    // Function to extract numeric HP values
    function extractNumericHP(hpString) {
        if (!hpString) return 0;
        // Extract numeric part from HP string (handles formats like "1.5 HP", "2", etc.)
        var numericValue = parseFloat(hpString.toString().replace(/[^\d.]/g, ''));
        return isNaN(numericValue) ? 0 : numericValue;
    }

    // Function to highlight matching text
    function highlightText(text, searchTerm) {
        if (!searchTerm) return text;
        
        var regex = new RegExp('(' + searchTerm + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    // Function to adjust table based on brand selection
    function adjustTableForBrand(brand) {
        if (brand && brand !== 'all') {
            // Hide brand column when specific brand is selected
            $('#brand-col').hide();
            $('#brand-header').hide();
            // Adjust colspan for loading and empty messages
            $('#product-tbody tr td[colspan]').attr('colspan', '6');
        } else {
            // Show brand column when "All Brands" is selected
            $('#brand-col').show();
            $('#brand-header').show();
            // Adjust colspan for loading and empty messages
            $('#product-tbody tr td[colspan]').attr('colspan', '7');
        }
    }

    // Function to update pagination info
    function updatePaginationInfo() {
        if (!allProducts || allProducts.length === 0) {
            $('#start-entry').text('0');
            $('#end-entry').text('0');
            $('#total-entries').text('0');
            $('#prev-page').prop('disabled', true);
            $('#next-page').prop('disabled', true);
            return;
        }
        
        const startEntry = (currentPage - 1) * entriesPerPage + 1;
        const endEntry = Math.min(currentPage * entriesPerPage, allProducts.length);
        
        $('#start-entry').text(startEntry);
        $('#end-entry').text(endEntry);
        $('#total-entries').text(allProducts.length);
        
        // Update button states
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);
    }

    // Function to fetch and display products by brand and HP
    function fetchAndDisplayProductsByFilters(brand, hp, searchTerm = '') {
        // Only proceed if a brand or HP is selected
        if ((!brand || brand === '') && (!hp || hp === '')) {
            // Hide table and show no brand message
            $('#table-container').hide();
            $('#no-brand-message').show();
            $('#brand-display').hide();
            $('#hp-display').hide();
            $('#stock-qty-display').hide();
            return;
        }
        
        // Show table and hide no brand message
        $('#table-container').show();
        $('#no-brand-message').hide();
        
        var tbody = $('#product-tbody');
        
        // Adjust table based on brand selection
        adjustTableForBrand(brand);
        
        // Show loading indicator
        tbody.empty();
        tbody.append('<tr><td colspan="' + (brand && brand !== 'all' ? '6' : '7') + '" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        // Check if the function exists before making the AJAX call
        if (typeof _base_url_ === 'undefined') {
            console.error("_base_url_ is not defined");
            tbody.empty();
            tbody.append('<tr><td colspan="' + (brand && brand !== 'all' ? '6' : '7') + '" class="text-center text-danger">Error: Base URL not defined</td></tr>');
            return;
        }
        
        // Store the current brand and HP
        currentBrand = brand;
        currentHP = hp;
        
        // Reset pagination
        currentPage = 1;
        
        // Fetch products data from purchase_order_list instead of receiving_list
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=get_all_purchase_order_stocks",
            method: "GET",
            data: {brand: (brand === 'all' || !brand) ? '' : brand}, // Pass empty string for all brands or when brand is not selected
            dataType: "json",
            success: function(resp) {
                tbody.empty(); // Clear loading indicator
                
                if (resp.status === 'success' && resp.data && resp.data.length > 0) {
                    // Filter products to ensure only selected brand is displayed
                    var filteredProducts = resp.data;
                    if (brand && brand !== 'all') {
                        filteredProducts = resp.data.filter(function(product) {
                            return product.brand === brand;
                        });
                        
                        // If a brand is selected, update the HP dropdown options
                        updateHPDropdown(filteredProducts);
                    }
                    
                    // Further filter by HP if selected
                    if (hp) {
                        filteredProducts = filteredProducts.filter(function(product) {
                            // Extract numeric value from product HP
                            var productHP = extractNumericHP(product.hp);
                            var selectedHP = parseFloat(hp);
                            return productHP === selectedHP;
                        });
                    }
                    
                    allProducts = filteredProducts;
                    
                    // Calculate total pages
                    totalPages = Math.ceil(allProducts.length / entriesPerPage);
                    
                    // Show brand display if a specific brand is selected
                    if (brand && brand !== 'all') {
                        $('#brand-display').show();
                        $('#selected-brand-name').text(brand.toUpperCase());
                    } else {
                        $('#brand-display').hide();
                    }
                    
                    // Show HP display if HP is selected
                    if (hp) {
                        $('#hp-display').show();
                        $('#selected-hp-name').text(hp + ' HP');
                    } else {
                        $('#hp-display').hide();
                    }
                    
                    // Show stock quantity display
                    $('#stock-qty-display').show();
                    $('#selected-stock-qty').text(allProducts.length + ' AVAILABLE STOCKS');
                    
                    // Display the first page
                    displayProductsPage(searchTerm);
                } else {
                    tbody.append('<tr><td colspan="' + (brand && brand !== 'all' ? '6' : '7') + '" class="text-center">No products found.</td></tr>');
                    
                    // Hide displays if no products found
                    $('#brand-display').hide();
                    $('#hp-display').hide();
                    $('#stock-qty-display').hide();
                    
                    // Update pagination info
                    updatePaginationInfo();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                tbody.empty();
                tbody.append('<tr><td colspan="' + (brand && brand !== 'all' ? '6' : '7') + '" class="text-center text-danger">Error loading products. Please try again.</td></tr>');
                
                // Hide displays on error
                $('#brand-display').hide();
                $('#hp-display').hide();
                $('#stock-qty-display').hide();
                
                // Update pagination info
                updatePaginationInfo();
            }
        });
    }
    
    // Function to update HP dropdown based on available products
    function updateHPDropdown(products) {
        // Extract unique HP values from products
        var hpValues = {};
        products.forEach(function(product) {
            var hp = extractNumericHP(product.hp);
            if (hp > 0) {
                hpValues[hp] = true;
            }
        });
        
        // Get current selected HP value
        var currentHPValue = $('#hp_filter').val();
        
        // Clear existing options except the first empty option
        $('#hp_filter').find('option:not(:first)').remove();
        
        // Add available HP options
        Object.keys(hpValues).sort(function(a, b) {
            return parseFloat(a) - parseFloat(b);
        }).forEach(function(hp) {
            $('#hp_filter').append('<option value="' + hp + '">' + hp + '</option>');
        });
        
        // Restore selected HP value if it still exists in the new options
        if (currentHPValue && hpValues[currentHPValue]) {
            $('#hp_filter').val(currentHPValue);
            $('.hp-select-wrapper').addClass('has-value');
        } else if (!currentHPValue) {
            $('.hp-select-wrapper').removeClass('has-value');
        }
    }
    
    // Function to display a specific page of products
    function displayProductsPage(searchTerm = '') {
        var tbody = $('#product-tbody');
        tbody.empty();
        
        if (!allProducts || allProducts.length === 0) {
            tbody.append('<tr><td colspan="' + (currentBrand && currentBrand !== 'all' ? '6' : '7') + '" class="text-center">No products found.</td></tr>');
            updatePaginationInfo();
            return;
        }
        
        // Calculate start and end indices for current page
        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = Math.min(startIndex + entriesPerPage, allProducts.length);
        const pageProducts = allProducts.slice(startIndex, endIndex);
        
        var hasResults = false;
        var searchLower = searchTerm ? searchTerm.toLowerCase() : '';
        
        // Filter page products by search term
        var filteredProducts = pageProducts.filter(function(product) {
            // Create search text from specific fields only
            var searchText = (product.indoor || '') + ' ' + 
                             (product.indoor_serial || '') + ' ' + 
                             (product.outdoor || '') + ' ' + 
                             (product.outdoor_serial || '');
            
            return !searchTerm || searchText.toLowerCase().indexOf(searchLower) > -1;
        });
        
        // Sort filtered products by HP in ascending order
        filteredProducts.sort(function(a, b) {
            var hpA = extractNumericHP(a.hp);
            var hpB = extractNumericHP(b.hp);
            return hpA - hpB;
        });
        
        // Variable to track row numbers
        var rowNum = startIndex + 1;
        
        // Now display the sorted products - each product gets its own row
        for (var i = 0; i < filteredProducts.length; i++) {
            var product = filteredProducts[i];
            var row = $('<tr class="product-row">');
            
            // Row number - individual cell for each row (no merging)
            var rowNumCell = $('<td class="text-center">').text(rowNum);
            row.append(rowNumCell);
            
            // Indoor Unit - with highlighting and uppercase
            var indoorText = product.indoor || '';
            if (searchTerm) {
                indoorText = highlightText(indoorText, searchTerm);
            }
            row.append($('<td class="uppercase">').html(indoorText)); // Changed from capitalize to uppercase
            
            // Indoor Serial - with highlighting
            var indoorSerialText = product.indoor_serial || '';
            if (searchTerm) {
                indoorSerialText = highlightText(indoorSerialText, searchTerm);
            }
            row.append($('<td class="uppercase">').html(indoorSerialText));
            
            // Outdoor Unit - with highlighting and uppercase
            var outdoorText = product.outdoor || '';
            if (searchTerm) {
                outdoorText = highlightText(outdoorText, searchTerm);
            }
            row.append($('<td class="uppercase">').html(outdoorText)); // Changed from capitalize to uppercase
            
            // Outdoor Serial - with highlighting
            var outdoorSerialText = product.outdoor_serial || '';
            if (searchTerm) {
                outdoorSerialText = highlightText(outdoorSerialText, searchTerm);
            }
            row.append($('<td class="uppercase">').html(outdoorSerialText));
            
            // Price
            row.append($('<td class="text-right">').text(parseFloat(product.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2})));
            
            // Brand - Only show if "All Brands" is selected
            if (!currentBrand || currentBrand === 'all') {
                var brandName = product.brand || '';
                if (searchTerm) {
                    brandName = highlightText(brandName, searchTerm);
                }
                row.append($('<td class="brand-name uppercase">').html(brandName.toUpperCase()));
            }
            
            tbody.append(row);
            rowNum++;
            hasResults = true;
        }
        
        if (!hasResults) {
            tbody.append('<tr><td colspan="' + (currentBrand && currentBrand !== 'all' ? '7' : '8') + '" class="text-center">No products match your search criteria.</td></tr>');
        }
        
        // Update pagination info
        updatePaginationInfo();
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

    $(function(){
        // Initially hide the table and show the no brand message
        $('#table-container').hide();
        $('#no-brand-message').show();
        $('#stock-qty-display').hide();
        
        // Brand select change event
        $('#brand_filter').on('change', function() {
            var selectedBrand = $(this).val();
            var selectedHP = $('#hp_filter').val();
            
            // Update watermark visibility
            if (selectedBrand) {
                $('.brand-select-wrapper').addClass('has-value');
                
                // If "Select all brand" is chosen, clear the HP dropdown
                if (selectedBrand === 'all') {
                    $('#hp_filter').val('');
                    $('.hp-select-wrapper').removeClass('has-value');
                    selectedHP = ''; // Update the selectedHP variable
                }
            } else {
                $('.brand-select-wrapper').removeClass('has-value');
                
                // If brand is cleared, reset HP dropdown to all options
                resetHPDropdown();
            }
            
            // Only fetch products if a brand or HP is selected
            if (selectedBrand || selectedHP) {
                fetchAndDisplayProductsByFilters(selectedBrand, selectedHP);
            } else {
                // If no brand is selected, hide the table and show the message
                $('#table-container').hide();
                $('#no-brand-message').show();
                $('#brand-display').hide();
                $('#hp-display').hide();
                $('#stock-qty-display').hide();
            }
        });
        
        // HP select change event
        $('#hp_filter').on('change', function() {
            var selectedBrand = $('#brand_filter').val();
            var selectedHP = $(this).val();
            
            // Update watermark visibility
            if (selectedHP) {
                $('.hp-select-wrapper').addClass('has-value');
            } else {
                $('.hp-select-wrapper').removeClass('has-value');
            }
            
            // Only fetch products if a brand or HP is selected
            if (selectedBrand || selectedHP) {
                fetchAndDisplayProductsByFilters(selectedBrand, selectedHP);
            } else {
                // If no HP is selected, hide the table and show the message
                $('#table-container').hide();
                $('#no-brand-message').show();
                $('#brand-display').hide();
                $('#hp-display').hide();
                $('#stock-qty-display').hide();
            }
        });
        
        // Brand clear button click event
        $('.brand-select-wrapper .select-clear').on('click', function() {
            $('#brand_filter').val('');
            $('.brand-select-wrapper').removeClass('has-value');
            
            // Trigger change event to update the display
            $('#brand_filter').trigger('change');
        });
        
        // HP clear button click event
        $('.hp-select-wrapper .select-clear').on('click', function() {
            $('#hp_filter').val('');
            $('.hp-select-wrapper').removeClass('has-value');
            
            // Trigger change event to update the display
            $('#hp_filter').trigger('change');
        });
        
        // Function to reset HP dropdown to all options
        function resetHPDropdown() {
            // Get current selected HP value
            var currentHPValue = $('#hp_filter').val();
            
            // Clear existing options except the first empty option
            $('#hp_filter').find('option:not(:first)').remove();
            
            // Add all HP options
            var hpOptions = ['0.8', '1', '1.5', '2', '2.5', '3', '4', '5', '6', '7'];
            hpOptions.forEach(function(hp) {
                $('#hp_filter').append('<option value="' + hp + '">' + hp + '</option>');
            });
            
            // Restore selected HP value if it exists
            if (currentHPValue && hpOptions.includes(currentHPValue)) {
                $('#hp_filter').val(currentHPValue);
                $('.hp-select-wrapper').addClass('has-value');
            } else if (!currentHPValue) {
                $('.hp-select-wrapper').removeClass('has-value');
            }
        }
        
        // Entries per page change event
        $('#entries-per-page').on('change', function() {
            entriesPerPage = parseInt($(this).val());
            currentPage = 1; // Reset to first page
            
            // Recalculate total pages
            if (allProducts) {
                totalPages = Math.ceil(allProducts.length / entriesPerPage);
            }
            
            // Redisplay current page
            displayProductsPage($('#product-search').val());
        });
        
        // Previous page button click event
        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                displayProductsPage($('#product-search').val());
            }
        });
        
        // Next page button click event
        $('#next-page').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                displayProductsPage($('#product-search').val());
            }
        });
        
        // Function to trigger search
        function triggerSearch() {
            var value = $('#product-search').val();
            var selectedBrand = $('#brand_filter').val();
            var selectedHP = $('#hp_filter').val();
            
            if (selectedBrand || selectedHP) {
                // If we have products loaded, filter them
                currentPage = 1; // Reset to first page when searching
                displayProductsPage(value);
            } else if (selectedBrand !== undefined || selectedHP !== undefined) {
                // If no products loaded yet, fetch them with search term
                fetchAndDisplayProductsByFilters(selectedBrand, selectedHP, value);
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
        
        // Refresh button click event - UPDATED
        $('#refresh-btn').on('click', function() {
            // Add spinning animation to the refresh icon
            $(this).addClass('refreshing');
            
            // Get current filter values (don't reset them)
            var currentBrandValue = $('#brand_filter').val();
            var currentHPValue = $('#hp_filter').val();
            
            // Reset variables but keep filter values
            allProducts = null;
            currentPage = 1;
            
            // If there are filters applied, refresh the data with those filters
            if (currentBrandValue || currentHPValue) {
                // Keep the table visible
                $('#table-container').show();
                $('#no-brand-message').hide();
                
                // Fetch fresh data with current filters
                fetchAndDisplayProductsByFilters(currentBrandValue, currentHPValue);
            } else {
                // If no filters are applied, hide the table and show the message
                $('#table-container').hide();
                $('#no-brand-message').show();
                $('#brand-display').hide();
                $('#hp-display').hide();
                $('#stock-qty-display').hide();
                
                // Update pagination info
                updatePaginationInfo();
            }
            
            // Remove spinning animation after a short delay
            setTimeout(() => {
                $(this).removeClass('refreshing');
            }, 800);
            
            // Show success message
            showSuccessMessage('Page refreshed successfully');
        });
    })
</script>
</body>
</html>
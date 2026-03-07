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
            font-family: Arial, sans-serif;
        }

        #list td {
            vertical-align: middle !important;
            text-align: center !important;
            font-weight: bold !important; /* Ensure bold text */
            border: 2px solid #021324ff; /* Bold cell borders */
            padding: 12px 8px !important; /* Increased padding for better appearance */
            background-color: #ffffff; /* Set cell background to white */
            font-family: Arial, sans-serif !important;
        }

        /* Input styling in table */
        #list input[type="number"] {
            width: 50px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #021324ff;
            padding: 4px;
            font-family: Arial, sans-serif;
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
        
        /* Product row styling */
        .product-row {
            background-color: #ffffff;
        }
        
        /* Add subtle zebra striping */
        #list tbody tr:nth-child(even) {
            background-color: #ffffff;
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
        
        /* ROW HIGHLIGHT EFFECT */
        #list tbody tr:hover {
            background-color: #29a0e6ff !important;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        /* ENSURE TEXT REMAINS BOLD ON HOVER */
        #list tbody tr:hover td {
            background-color: inherit !important;
            color: inherit !important;
            font-weight: bold !important;
            text-shadow: none !important;
        }
        
        /* Auto-capitalization styles */
        .capitalize {
            text-transform: capitalize !important;
        }
        
        .uppercase {
            text-transform: uppercase !important;
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
        
        .hp-select-wrapper.has-value .select-clear {
            display: block;
        }

        /* Status Badge Styling */
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
            <h6 style="color: #0066cc; margin: 0;">
                <i class="fas fa-eye"></i> <b>VIEW Customers</b>
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
                
                <!-- Combined Search and Filter Section -->
                <div class="search-section">
                    <h6><i class="fas fa-search"></i>Filter Options</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group filter-section">
                                <label class="filter-label">Search Customer</label>
                                <input type="text" id="product-search" class="form-control" placeholder="Enter Name or Remarks...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group filter-section">
                                <label class="filter-label">Filter by Status</label>
                                <div class="hp-select-wrapper">
                                    <select name="status_filter" id="hp_filter" class="hp-select">
                                        <option value=""></option>
                                        <option value="Pending">Pending</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                    <div class="hp-select-watermark">Select Status</div>
                                    <button type="button" class="select-clear" title="Clear selection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stock Quantity Display Area -->
                <div id="stock-qty-display" class="stock-qty-display" style="display: none;">
                    <i class="fas fa-boxes"></i> <span id="selected-stock-qty"></span>
                </div>
                
                <!-- No Data Message -->
                <div id="no-brand-message" class="no-brand-message" style="display:none;">
                    <i class="fas fa-users"></i>
                    <h5>No Customers Found</h5>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
                
                <!-- Table Container -->
                <div id="table-container">
                    <hr>
                    <table class="table table-striped table-bordered" id="list">
                        <colgroup>
                            <col width="5%">   <!-- # -->
                            <col width="20%">  <!-- Customer Name -->
                            <col width="10%">  <!-- Date -->
                            <col width="10%">  <!-- Service Type -->
                            <col width="15%">  <!-- Installer/Cleaner -->
                            <col width="20%">  <!-- Remarks -->
                            <col width="10%">  <!-- Status -->
                            <col width="10%">  <!-- Actions -->
                        </colgroup>
                        <thead>
                            <tr class="text-light bg-navy">
                                <th class="text-center py-1 px-2">#</th>
                                <th class="text-center py-1 px-2">Customer Name</th>
                                <th class="text-center py-1 px-2">Date</th>
                                <th class="text-center py-1 px-2">Service Type</th>
                                <th class="text-center py-1 px-2">Installer/Cleaner</th>
                                <th class="text-center py-1 px-2">Remarks</th>
                                <th class="text-center py-1 px-2">Status</th>
                                <th class="text-center py-1 px-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="product-tbody">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                                        <p>Loading data...</p>
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
        var pathArray = window.location.pathname.split('/');
        var newPathname = "";
        for (var i = 0; i < pathArray.length - 1; i++) {
            newPathname += pathArray[i];
            newPathname += "/";
        }
        _base_url_ = window.location.origin + newPathname;
    }

    // Variables
    let allProducts = null;
    let currentFilter = '';
    let currentPage = 1;
    let entriesPerPage = 10;
    let totalPages = 0;

    // Function to highlight matching text
    function highlightText(text, searchTerm) {
        if (!searchTerm) return text;
        var regex = new RegExp('(' + searchTerm + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    // Function to update pagination info
    function updatePaginationInfo(filteredCount) {
        if (!allProducts || filteredCount === 0) {
            $('#start-entry').text('0');
            $('#end-entry').text('0');
            $('#total-entries').text('0');
            $('#prev-page').prop('disabled', true);
            $('#next-page').prop('disabled', true);
            return;
        }
        
        const startEntry = (currentPage - 1) * entriesPerPage + 1;
        const endEntry = Math.min(currentPage * entriesPerPage, filteredCount);
        
        $('#start-entry').text(startEntry);
        $('#end-entry').text(endEntry);
        $('#total-entries').text(filteredCount);
        
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);
    }

    // Function to get status badge HTML
    function getStatusBadge(status) {
        var badgeClass = 'status-pending';
        if(status) {
            var s = status.toLowerCase();
            if(s === 'completed') badgeClass = 'status-completed';
            else if(s === 'cancelled') badgeClass = 'status-cancelled';
        }
        return '<span class="status-badge ' + badgeClass + '">' + (status || 'Pending') + '</span>';
    }

    // Function to fetch data
    function fetchAndDisplayProductsByFilters(filter, searchTerm = '') {
        $('#table-container').show();
        $('#no-brand-message').hide();
        
        var tbody = $('#product-tbody');
        tbody.empty();
        tbody.append('<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        currentFilter = filter;
        currentPage = 1;
        
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=get_customers", // Updated endpoint
            method: "GET",
            data: {},
            dataType: "json",
            success: function(resp) {
                tbody.empty();
                
                if (resp.status === 'success' && resp.data && resp.data.length > 0) {
                    allProducts = resp.data;
                    displayProductsPage(searchTerm);
                } else {
                    tbody.append('<tr><td colspan="8" class="text-center">No data found.</td></tr>');
                    $('#stock-qty-display').hide();
                    updatePaginationInfo(0);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                tbody.empty();
                tbody.append('<tr><td colspan="8" class="text-center text-danger">Error loading data.</td></tr>');
                $('#stock-qty-display').hide();
                updatePaginationInfo(0);
            }
        });
    }
    
    // Function to display page
    function displayProductsPage(searchTerm = '') {
        var tbody = $('#product-tbody');
        tbody.empty();
        
        if (!allProducts || allProducts.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center">No data available.</td></tr>');
            $('#no-brand-message').show();
            $('#stock-qty-display').hide();
            updatePaginationInfo(0);
            return;
        }
        
        var searchLower = searchTerm ? searchTerm.toLowerCase() : '';
        var statusFilter = $('#hp_filter').val();

        var filteredProducts = allProducts.filter(function(product) {
            // Status Filter
            var matchesStatus = true;
            if (statusFilter) {
                matchesStatus = (product.status && product.status.toLowerCase() === statusFilter.toLowerCase());
            }

            // Search Filter
            var matchesSearch = true;
            if (searchTerm) {
                var searchText = (product.customer_name || '') + ' ' + 
                                 (product.remarks || '') + ' ' + 
                                 (product.staff || '') + ' ' +
                                 (product.service_type || '');
                matchesSearch = (searchText.toLowerCase().indexOf(searchLower) > -1);
            }

            return matchesStatus && matchesSearch;
        });

        // Sort by date descending (newest first)
        filteredProducts.sort(function(a, b) {
            return new Date(b.date) - new Date(a.date);
        });

        totalPages = Math.ceil(filteredProducts.length / entriesPerPage);
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = Math.min(startIndex + entriesPerPage, filteredProducts.length);
        const pageProducts = filteredProducts.slice(startIndex, endIndex);
        
        var hasResults = false;
        var rowNum = startIndex + 1;
        
        for (var i = 0; i < pageProducts.length; i++) {
            var product = pageProducts[i];
            var row = $('<tr class="product-row">');
            
            // 1. Row Number
            row.append($('<td class="text-center">').text(rowNum));
            
            // 2. Customer Name
            var customerName = product.customer_name || 'N/A';
            if (searchTerm) customerName = highlightText(customerName, searchTerm);
            row.append($('<td class="text-left" style="text-align:left !important;">').html(customerName));
            
            // 3. Date
            row.append($('<td>').text(product.date || 'N/A'));
            
            // 4. Service Type
            row.append($('<td>').text(product.service_type || 'N/A'));
            
            // 5. Installer/Cleaner
            row.append($('<td>').text(product.staff || 'N/A'));
            
            // 6. Remarks
            var remarks = product.remarks || 'N/A';
            if (searchTerm) remarks = highlightText(remarks, searchTerm);
            row.append($('<td>').html(remarks));
            
            // 7. Status
            row.append($('<td>').html(getStatusBadge(product.status)));
            
            // 8. Actions
            var actions = product.actions || '<button class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></button> <button class="btn btn-sm btn-success"><i class="fas fa-edit"></i></button>';
            row.append($('<td>').html(actions));
            
            tbody.append(row);
            rowNum++;
            hasResults = true;
        }
        
        if (!hasResults) {
            $('#no-brand-message').show();
            $('#stock-qty-display').hide();
        } else {
            $('#no-brand-message').hide();
            $('#stock-qty-display').show();
            $('#selected-stock-qty').text(filteredProducts.length + ' RECORDS FOUND');
        }

        updatePaginationInfo(filteredProducts.length);
    }
    
    function showSuccessMessage(message) {
        $('.success-message').remove();
        const successDiv = $('<div class="success-message">' + message + '</div>');
        $('body').append(successDiv);
        successDiv.fadeIn();
        setTimeout(() => {
            successDiv.fadeOut(() => { successDiv.remove(); });
        }, 3000);
    }

    $(function(){
        // Initial Load
        fetchAndDisplayProductsByFilters('');
        
        // Search input event
        $('#product-search').on('input', function() {
            currentPage = 1;
            displayProductsPage($(this).val());
        });
        
        // Status filter change event
        $('#hp_filter').on('change', function() {
            var selectedStatus = $(this).val();
            if (selectedStatus) $('.hp-select-wrapper').addClass('has-value');
            else $('.hp-select-wrapper').removeClass('has-value');
            
            currentPage = 1;
            displayProductsPage($('#product-search').val());
        });
        
        // Clear button
        $('.hp-select-wrapper .select-clear').on('click', function() {
            $('#hp_filter').val('');
            $('.hp-select-wrapper').removeClass('has-value');
            $('#hp_filter').trigger('change');
        });
        
        // Entries per page
        $('#entries-per-page').on('change', function() {
            entriesPerPage = parseInt($(this).val());
            currentPage = 1;
            displayProductsPage($('#product-search').val());
        });
        
        // Pagination buttons
        $('#prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                displayProductsPage($('#product-search').val());
            }
        });
        
        $('#next-page').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                displayProductsPage($('#product-search').val());
            }
        });
        
        // Refresh button
        $('#refresh-btn').on('click', function() {
            $(this).addClass('refreshing');
            $('#product-search').val('');
            $('#hp_filter').val('');
            $('.hp-select-wrapper').removeClass('has-value');
            allProducts = null;
            currentPage = 1;
            fetchAndDisplayProductsByFilters('');
            setTimeout(() => { $(this).removeClass('refreshing'); }, 800);
            showSuccessMessage('Page refreshed successfully');
        });
    })
</script>
</body>
</html>
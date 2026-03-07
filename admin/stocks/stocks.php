<?php
// Database connection
 $servername = "localhost";
 $username = "root";
 $password = "";
 $dbname = "aircon"; // Change this to your database name

// Create connection
 $conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all unique brands for the filter dropdown from receiving_list
 $brands_qry = $conn->query("SELECT DISTINCT brand FROM `purchase_order_list` ORDER BY brand ASC");
 $brands = [];
while($row = $brands_qry->fetch_assoc()) {
    $brands[] = $row['brand'];
}

// Initialize filter variables
 $selected_brand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
 $search_term = isset($_GET['search']) ? $_GET['search'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving List</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header" style="background-color: white; border-bottom: 1px solid #0066cc;">
                <div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
                    <h6 style="color: #0066cc; margin: 0;">
                        <i class="nav-icon fas fa-warehouse"></i> <b>RECEIVING LIST</b>
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="brandFilter" class="filter-label">
                                <i class="fas fa-filter"></i> Filter by Brand:
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-tags"></i>
                                    </span>
                                </div>
                                <select class="form-control" id="brandFilter" name="brand">
                                    <option value="all" <?php echo $selected_brand == 'all' ? 'selected' : ''; ?>>All Brands</option>
                                    <?php foreach($brands as $brand): ?>
                                        <option value="<?php echo urlencode($brand); ?>" <?php echo $selected_brand == urlencode($brand) ? 'selected' : ''; ?>>
                                            <?php echo $brand; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="searchInput" class="filter-label">
                                <i class="fas fa-search"></i> Search by Item Code:
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                      <i class="fas fa-barcode"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="searchInput" placeholder="Enter Item Code / Serial No." value="<?php echo htmlspecialchars($search_term); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Selected Brand Display -->
                <?php if ($selected_brand != 'all'): ?>
                    <div class="alert alert-primary mb-4">
                        <h5 class="mb-0"><i class="fas fa-tag mr-2"></i> Showing records for brand: <strong><?php echo htmlspecialchars(urldecode($selected_brand)); ?></strong></h5>
                    </div>
                <?php endif; ?>
                
                <div class="container-fluid">
                    <table class="table table-bordered stocks-table">
                        <colgroup>
                            <col width="10%">
                            <col width="10%">
                            <col width="15%">
                            <col width="10%">
                            <col width="10%">
                            <col width="10%">
                            <col width="10%">
                            <col width="10%">
                            <col width="15%">
                        </colgroup>
                        <thead>
                            <tr class="bg-primary text-white text-center">
                                <th class="align-middle header-cell">
                                    <i class="fas fa-calendar-alt"></i> Date Received
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-tag"></i> Brand Name
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-barcode"></i> Item Code
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-cogs"></i> Type
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-tachometer-alt"></i> HP
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-home"></i> Indoor/Outdoor
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-boxes"></i> Quantity
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-money-bill-wave"></i> Price (SRP)
                                </th>
                                <th class="align-middle header-cell">
                                    <i class="fas fa-info-circle"></i> Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Build the base query for receiving_list
                            $base_query = "SELECT * FROM `receiving_list`";
                            
                            // Apply brand filter if not "all"
                            if ($selected_brand != 'all') {
                                $brand_name = urldecode($selected_brand);
                                $base_query .= " WHERE brand = '{$brand_name}'";
                            }
                            
                            // Apply search filter if provided
                            if (!empty($search_term)) {
                                $search_term_safe = $conn->real_escape_string($search_term);
                                if ($selected_brand != 'all') {
                                    $base_query .= " AND (item_code LIKE '%{$search_term_safe}%' OR serial_no LIKE '%{$search_term_safe}%')";
                                } else {
                                    $base_query .= " WHERE (item_code LIKE '%{$search_term_safe}%' OR serial_no LIKE '%{$search_term_safe}%')";
                                }
                            }
                            
                            $base_query .= " ORDER BY brand ASC, item_code ASC";
                            
                            // Get filtered items from receiving_list
                            $items_qry = $conn->query($base_query);
                            
                            while($item = $items_qry->fetch_assoc()):
                                $item_id = $item['id'];
                                $brand_name = $item['brand'];
                                $item_code = $item['item_code'] ?? $item['id'];
                                $serial_no = $item['serial_no'] ?? 'N/A';
                                $type = $item['type'] ?? 'N/A';
                                $hp = $item['hp'] ?? 'N/A';
                                $indoor_outdoor = $item['indoor_outdoor'] ?? 'N/A';
                                $price_srp = $item['price_srp'] ?? 0;
                                
                                // Get date received (assuming it's in the receiving_list table)
                                $date_received = isset($item['date_received']) ? date('M d, Y', strtotime($item['date_received'])) : 'N/A';
                                
                                // Get quantity (assuming it's in the receiving_list table)
                                $quantity = $item['quantity'] ?? 0;
                                
                                // Determine status based on quantity
                                if ($quantity <= 0) {
                                    $status = "Out of Stock";
                                    $status_class = "text-danger font-weight-bold";
                                } elseif ($quantity <= 10) {
                                    $status = "Low Stock";
                                    $status_class = "text-warning font-weight-bold";
                                } else {
                                    $status = "In Stock";
                                    $status_class = "text-success";
                                }
                            ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <?php echo $date_received; ?>
                                    </td>
                                    <td class="text-left align-middle">
                                        <i class="fas fa-industry text-primary"></i> <?php echo $brand_name; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php echo $item_code; ?>
                                        <?php if ($serial_no != 'N/A'): ?>
                                            <br><small class="text-muted">SN: <?php echo $serial_no; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php echo $type; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php echo $hp; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php echo $indoor_outdoor; ?>
                                    </td>
                                    <td class="text-center align-middle <?php echo ($quantity < 10) ? 'text-danger font-weight-bold' : ''; ?>">
                                        <?php echo number_format($quantity); ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?php echo number_format($price_srp, 2); ?>
                                    </td>
                                    <td class="text-center align-middle <?php echo $status_class; ?>">
                                        <?php echo $status; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if ($items_qry->num_rows == 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>No records found
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Include DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    
    <style>
        /* Set all table rows to white background */
        .stocks-table tbody tr {
            background-color: white !important;
        }
        
        /* Add hover effect to table rows */
        .stocks-table tbody tr:hover {
            background-color: rgba(83, 167, 235, 0.66) !important;
            transition: background-color 0.2s ease;
        }
        
        /* Header cell styling with adjusted border height */
        .header-cell {
            color: white !important;
            text-align: center !important;
            font-weight: 400 !important;
            padding: 12px 8px !important; /* Increased padding for taller headers */
            border: 1px solid #0056b3 !important; /* Darker border for better definition */
            border-bottom-width: 2px !important; /* Thicker bottom border */
            line-height: 1.2 !important; /* Adjusted line height */
            background-color: #021324ff !important; /* Ensure consistent background */
        }
        
        /* Style for left-aligned columns */
        .text-left {
            text-align: left !important;
            padding-left: 10px !important; /* Add some left padding for better appearance */
        }
        
        /* Style for right-aligned columns */
        .text-right {
            text-align: right !important;
            padding-right: 10px !important; /* Add some right padding for better appearance */
        }
        
        /* Style for center-aligned columns */
        .text-center {
            text-align: center !important;
        }
        
        /* Ensure all cells have proper padding and vertical alignment */
        .stocks-table td, .stocks-table th {
            padding: 8px 5px !important; /* Consistent padding */
            vertical-align: middle !important;
            border: 1px solid #dee2e6 !important; /* Consistent border */
        }
        
        /* Table border styling */
        .stocks-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        
        .stocks-table th, .stocks-table td {
            border-color: #dee2e6 !important;
        }
        
        /* Button styling */
        .btn-primary {
            background-color: #0066cc !important;
            border-color: #0066cc !important;
            transition: all 0.3s ease !important;
        }
        
        .btn-primary:hover {
            background-color: #0052a3 !important;
            border-color: #004b94 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-lg {
            padding: 0.75rem 1.5rem !important;
            font-size: 1.1rem !important;
            border-radius: 50px !important;
        }
        
        /* Filter section styling */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .input-group-append .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        /* Filter label styling */
        .filter-label {
            font-weight: 600;
            color: #0066cc;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .filter-label i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        /* Input group styling */
        .input-group-text {
            border-right: none;
            background-color: #f8f9fa;
        }
        
        .input-group .form-control:not(:last-child) {
            border-right: none;
        }
        
        .input-group .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
        
        .input-group .form-control:focus + .input-group-append .input-group-text {
            border-color: #0066cc;
        }
        
        /* Icon styling in input groups */
        .input-group-text i {
            color: #0066cc;
        }
        
        /* Icon styling in table cells */
        .stocks-table i {
            margin-right: 5px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filter-label {
                font-size: 0.9rem;
            }
            
            .input-group-text {
                padding: 0.375rem 0.5rem;
            }
        }
    </style>
    
    <script>
        $(document).ready(function(){
            $('.table td,.table th').addClass('py-1 px-2 align-middle')
            $('.table').dataTable({
                paging: false,
                searching: false,
                info: false,
                lengthChange: false
            });
            
            // Handle brand filter change
            $('#brandFilter').change(function() {
                applyFilters();
            });
            
            // Handle search button click
            $('#searchBtn').click(function() {
                applyFilters();
            });
            
            // Handle Enter key in search input
            $('#searchInput').keypress(function(e) {
                if (e.which == 13) { // Enter key
                    applyFilters();
                }
            });
            
            function applyFilters() {
                var brand = $('#brandFilter').val();
                var search = $('#searchInput').val();
                
                // Build URL with parameters
                var url = window.location.pathname + '?brand=' + encodeURIComponent(brand);
                if (search) {
                    url += '&search=' + encodeURIComponent(search);
                }
                
                // Redirect to the filtered page
                window.location.href = url;
            }
        })
    </script>
</body>
</html>
<?php
// Close the database connection
 $conn->close();
?>
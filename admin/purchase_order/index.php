<?php
// Get the base URL of the website
 $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
 $host = $_SERVER['HTTP_HOST'];
 $base_url = $protocol . '://' . $host;

// Get the current directory path
 $current_path = dirname($_SERVER['PHP_SELF']);

// Remove the 'admin' directory from the path to get the root
 $root_path = str_replace('/admin', '', $current_path);

// Now, if the classes directory is in the root, then:
 $classes_url = $base_url . $root_path . '/classes/';

// Get suppliers for dropdown - removed the WHERE clause for 'deleted' column
 $suppliers = $conn->query("SELECT * FROM supplier_list ORDER BY name ASC");

// Check if suppliers query was successful
if (!$suppliers) {
    die("Error fetching suppliers: " . $conn->error);
}
?>

<!-- Add this script to define the correct base URL for JavaScript -->
<script>
    var _base_url_ = '<?php echo $classes_url; ?>';
    console.log('Base URL set to:', _base_url_);
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded. AJAX functionality will not work.');
    }
</script>

<div class="card card-outline card-primary">
    <title>PURCHASED LIST</title>
    <div class="card">
        <div class="card-header" style="background-color: #004492ff; border-bottom: 2px solid #004492ff; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1; display: flex; justify-content: center;">
                <div style="display: inline-block; border: 2px solid white; border-radius: 10px; padding: 8px 20px; margin-top: 0px; position: relative; z-index: 2; background-color: rgba(255, 255, 255, 0.1);">
                    <h6 style="color: white; margin: 0; text-shadow: 0 1px 2px #004492ff; text-align: center;">
                      <i class="fas fa-shopping-cart"></i> <b>PURCHASED LIST</b>
                    </h6>
                </div>
            </div>
            <div style="display: flex; align-items: center;">
                <button type="button" class="btn btn-sm" id="checkDuplicatesBtn" style="background-color: rgba(255,193,7,0.2); border: 1px solid #ff1707ff; color: #fffffeff; padding: 5px 15px; margin-right: 10px;">
                    <i class="fas fa-copy"></i> Check Duplicates
                </button>
                <button type="button" class="btn btn-sm" id="refreshBtn" style="background-color: rgba(255,255,255,0.2); border: 1px solid white; color: white; padding: 5px 15px;">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped receiving-table">
                <colgroup>
                    <col width="3%"> <!-- # -->                        
                    <col width="12%"> <!-- Delivery Date -->
                    <col width="10%"> <!-- P.O. # -->
                    <col width="28%"> <!-- invoice # -->
                    <col width="12%"> <!-- DR # -->
                    <col width="16%"> <!-- Indoor -->
                    <col width="16%">  <!-- In_Serial # -->
                    <col width="16%">  <!-- Outdoor -->
                    <col width="16%">   <!-- Out_Serial # -->
                    <col width="25%">   <!-- Supplier -->
                    <col width="25%">   <!-- Brand  -->
                    <col width="4%">    <!-- HP -->
                    <col width="12%">   <!-- TYPE -->
                    <col width="17%">    <!-- Series -->
                    <col width="8%">     <!-- Unit -->
                    <col width="14%">    <!-- Price -->
                    <col width="10%">    <!-- Remarks -->
                    <col width="15%">     <!-- Action -->
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Delivery Date</th>
                        <th class="text-center">P.O. #</th>
                        <th class="text-center">Invoice#</th>
                        <th class="text-center">DR #</th>
                        <th class="text-center">Indoor</th>
                        <th class="text-center">Serial #</th>
                        <th class="text-center">Outdoor</th>
                        <th class="text-center">Serial #</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Brand</th>
                        <th class="text-center">HP</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Series</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Remarks</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                   <?php 
                        // Define $show_deleted if it's not already defined
                        $show_deleted = isset($show_deleted) ? $show_deleted : false;
                        $i = 1;
                        $where_clause = $show_deleted ? "p.deleted = 1" : "p.deleted = 0";
                        
                        // Updated query to order by id DESC to show newest first
                        $qry = $conn->query("SELECT p.*, s.name as supplier, s.id as supplier_id 
                                           FROM purchase_order_list p 
                                           LEFT JOIN supplier_list s ON p.supplier_id = s.id 
                                           WHERE $where_clause 
                                           ORDER BY p.id DESC");
                        while($row = $qry->fetch_assoc()):
                        ?>

                        <tr id="row-<?php echo $row['id']; ?>">
                                <td class="text-center"><b><?php echo $i++; ?></b></td>
                                <td class="text-center"><b><?php echo date("Y-m-d", strtotime($row['delivery_date'])) ?></b></td>
                                <td class="text-center"><b><?php echo $row['po'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['invoice'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['dr'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['indoor'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['indoor_serial'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['outdoor'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['outdoor_serial'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['supplier'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['brand'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['hp'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['type'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['series'] ?></b></td>
                                <td class="text-center"><b><?php echo $row['unit'] ?></b></td>
                                <td class="text-center"><b><?php echo number_format($row['price'], 2) ?></b></td>
                                <td class="text-center"><b><?php echo $row['remarks'] ?></b></td>
                                <td align="center">
                                    <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown"> Action<span class="sr-only">Toggle Dropdown</span>  </button>
                                    <div class="dropdown-menu" role="menu">  
                                        <?php if($row['status'] == 0): ?>
                                        <a class="dropdown-item" href="<?php echo base_url.'admin?page=receiving/manage_receiving&po_id='.$row['id'] ?>" data-id="<?php echo $row['id'] ?>"><span class="fa fa-boxes text-dark"></span> Receive</a>
                                        <div class="dropdown-divider"></div>
                                        <?php endif; ?>
                                        <a class="dropdown-item edit-btn" href="javascript:void(0)" 
                                           data-id="<?php echo $row['id'] ?>" 
                                           data-po="<?php echo $row['po'] ?>" 
                                           data-invoice="<?php echo $row['invoice'] ?>" 
                                           data-dr="<?php echo $row['dr'] ?>" 
                                           data-indoor="<?php echo $row['indoor'] ?>" 
                                           data-indoor-serial="<?php echo $row['indoor_serial'] ?>" 
                                           data-outdoor="<?php echo $row['outdoor'] ?>" 
                                           data-outdoor-serial="<?php echo $row['outdoor_serial'] ?>" 
                                           data-supplier="<?php echo htmlspecialchars($row['supplier']) ?>" 
                                           data-supplier-id="<?php echo $row['supplier_id'] ?>" 
                                           data-brand="<?php echo $row['brand'] ?>" 
                                           data-hp="<?php echo $row['hp'] ?>" 
                                           data-type="<?php echo $row['type'] ?>" 
                                           data-series="<?php echo $row['series'] ?>" 
                                           data-unit="<?php echo $row['unit'] ?>" 
                                           data-price="<?php echo $row['price'] ?>" 
                                           data-remarks="<?php echo $row['remarks'] ?>" 
                                           data-delivery-date="<?php echo date("Y-m-d", strtotime($row['delivery_date'])) ?>">
                                           <span class="fa fa-edit text-primary"></span> Edit
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo base_url.'admin?page=purchase_order/view_po&id='.$row['id'] ?>" data-id="<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> View</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>" data-po="<?php echo $row['po'] ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
        </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Purchase Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editDeliveryDate">Delivery Date</label>
                                <input type="date" class="form-control" id="editDeliveryDate" name="delivery_date" required>
                            </div>
                            <div class="form-group">
                                <label for="editPo">P.O. #</label>
                                <input type="text" class="form-control" id="editPo" name="po" required>
                            </div>
                            <div class="form-group">
                                <label for="editInvoice">Invoice #</label>
                                <input type="text" class="form-control" id="editInvoice" name="invoice" required>
                            </div>
                            <div class="form-group">
                                <label for="editDr">DR #</label>
                                <input type="text" class="form-control" id="editDr" name="dr" required>
                            </div>
                            <div class="form-group">
                                <label for="editIndoor">Indoor</label>
                                <input type="text" class="form-control" id="editIndoor" name="indoor" required>
                            </div>
                            <div class="form-group">
                                <label for="editIndoorSerial">In_Serial #</label>
                                <input type="text" class="form-control" id="editIndoorSerial" name="indoor_serial" required>
                            </div>
                            <div class="form-group">
                                <label for="editOutdoor">Outdoor</label>
                                <input type="text" class="form-control" id="editOutdoor" name="outdoor" required>
                            </div>
                            <div class="form-group">
                                <label for="editOutdoorSerial">Out_Serial #</label>
                                <input type="text" class="form-control" id="editOutdoorSerial" name="outdoor_serial" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editSupplier">Supplier</label>
                                <select class="form-control" id="editSupplier" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    <?php 
                                    // Reset the pointer to the beginning of the result set
                                    if($suppliers):
                                        $suppliers->data_seek(0);
                                        while($srow = $suppliers->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $srow['id'] ?>"><?php echo htmlspecialchars($srow['name']) ?></option>
                                    <?php 
                                        endwhile;
                                    endif;
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="editBrand">Brand</label>
                                <input type="text" class="form-control" id="editBrand" name="brand" required>
                            </div>
                            <div class="form-group">
                                <label for="editHp">HP</label>
                                <input type="text" class="form-control" id="editHp" name="hp" required>
                            </div>
                            <div class="form-group">
                                <label for="editType">Type</label>
                                <input type="text" class="form-control" id="editType" name="type" required>
                            </div>
                            <div class="form-group">
                                <label for="editSeries">Series</label>
                                <input type="text" class="form-control" id="editSeries" name="series" required>
                            </div>
                            <div class="form-group">
                                <label for="editPrice">Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₱</span>
                                    </div>
                                    <input type="number" class="form-control text-center" id="editPrice" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="editRemarks">Remarks</label>
                                <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h4>Are you sure you want to permanently delete this record?</h4>
                    <p class="mb-0"><strong id="poNumber"></strong>.</p>
                    <p class="text-muted small"><h5>This action cannot be undone.</h5></p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed bottom-0 right-0 p-3" style="z-index: 1055;">
    <div id="deleteToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
        <div class="toast-header">
            <i class="fas fa-info-circle mr-2 text-primary"></i>
            <strong class="mr-auto" id="toastTitle">Notification</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="toastMessage">
            Message here
        </div>
    </div>
</div>

<style>
    .receiving-table {
        table-layout: auto;
        min-width: 100%;
        border: 3px solid #000; /* Bold black border */
        border-collapse: collapse; /* Ensures borders don't double up */
    }
    .receiving-table th, .receiving-table td {
        padding: 8px;
        text-transform: uppercase;
        vertical-align: middle;
        border: 2px solid #000; /* Bold black borders for cells */
        text-align: center; /* Center all text */
        font-weight: bold; /* Make all text bold */
        color: #000; /* Black text color for data cells */
        font-family: 'Calibri', 'Arial', sans-serif; /* Calibri font with fallbacks */
    }
    .receiving-table th {
        background-color: #004492ff; /* Blue color for header */
        color: #fff; /* White text color for headers */
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
        border-color: #000; /* Ensure header borders are black */
    }
    .receiving-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .receiving-table tbody tr {
        transition: background-color 0.3s ease; /* Smooth transition for hover effect */
    }
    .receiving-table tbody tr:hover {
        background-color: #a8d0ff; /* Noticeable blue highlight on hover */
        cursor: pointer; /* Change cursor to pointer to indicate interactivity */
    }
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    .text-right {
        text-align: center; /* Override right alignment */
    }
    .text-center {
        text-align: center;
    }
    .modal-header {
        background-color: #1916e9ff;
        color: white;
    }
    .modal-header .close {
        color: white;
    }
    
    /* Custom styles for delete confirmation modal */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }
    
    .modal-content {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .modal-header.bg-danger {
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    .modal-footer {
        border-top: none;
    }
    
    /* Toast notification styles */
    .toast-container {
        z-index: 1055;
    }
    
    .toast {
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 0.25rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    }
    
    .toast-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: rgba(255, 255, 255, 0.85);
    }
    
    .toast-success .toast-header {
        color: #155724;
        background-color: rgba(212, 237, 218, 0.85);
        border-bottom-color: #c3e6cb;
    }
    
    .toast-error .toast-header {
        color: #721c24;
        background-color: rgba(248, 215, 218, 0.85);
        border-bottom-color: #f5c6cb;
    }
    
    .toast-info .toast-header {
        color: #0c5460;
        background-color: rgba(207, 224, 237, 0.85);
        border-bottom-color: #b8daff;
    }
    
    /* Refresh button animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .fa-sync-alt.spinning {
        animation: spin 1s linear infinite;
    }
    
    /* Duplicate highlighting styles */
    .duplicate-highlight {
        background-color: rgba(214, 106, 4, 0.63) !important;
        border-left: 4px solid #ffc107 !important;
        font-weight: bold;
    }

    .receiving-table tbody tr.duplicate-highlight:hover {
        background-color: rgba(255, 193, 7, 0.5) !important;
    }

    /* Animation for highlighting */
    @keyframes highlightPulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
    }

    .duplicate-highlight td {
        animation: highlightPulse 1.5s;
    }
</style>

<!-- Add this script before the closing body tag -->
<script>
 $(document).ready(function() {
    var deleteId = null;
    var deleteRow = null;
    var currentSupplierId = null;
    var currentSupplierName = null;
    
    // Handle check duplicates button click
    $('#checkDuplicatesBtn').click(function() {
        // Clear any previous highlights
        $('.receiving-table tbody tr').removeClass('duplicate-highlight');
        
        // Create an object to store serial combinations and their row counts
        var serialMap = {};
        var rows = $('.receiving-table tbody tr');
        
        // First pass: count occurrences of each serial combination
        rows.each(function() {
            var row = $(this);
            var indoorSerial = row.find('td:eq(6)').text().trim();
            var outdoorSerial = row.find('td:eq(8)').text().trim();
            var key = indoorSerial + '|' + outdoorSerial;
            
            if (serialMap[key]) {
                serialMap[key].count++;
                serialMap[key].rows.push(row);
            } else {
                serialMap[key] = {
                    count: 1,
                    rows: [row]
                };
            }
        });
        
        // Second pass: highlight rows that have duplicates
        var duplicateSets = 0;
        var totalDuplicateRows = 0;
        
        for (var key in serialMap) {
            if (serialMap[key].count > 1) {
                duplicateSets++;
                totalDuplicateRows += serialMap[key].count;
                // Highlight all rows in this duplicate set
                serialMap[key].rows.forEach(function(row) {
                    $(row).addClass('duplicate-highlight');
                });
            }
        }
        
        // Show notification
        if (duplicateSets > 0) {
            showToast('info', 'Duplicates Found', 
                'Found ' + duplicateSets + ' set(s) of duplicates (' + totalDuplicateRows + ' rows total)');
        } else {
            showToast('info', 'No Duplicates', 'No duplicate serial numbers found');
        }
        
        // Scroll to first highlighted row if duplicates found
        if (duplicateSets > 0) {
            var firstHighlighted = $('.receiving-table tbody tr.duplicate-highlight:first');
            if (firstHighlighted.length) {
                $('.table-responsive').scrollTop(
                    firstHighlighted.position().top - $('.table-responsive').offset().top + 
                    $('.table-responsive').scrollTop()
                );
            }
        }
    });
    
    // Handle refresh button click
    $('#refreshBtn').click(function() {
        // Show loading state
        $(this).prop('disabled', true);
        $(this).find('.fa-sync-alt').addClass('spinning');
        $(this).html('<i class="fas fa-sync-alt spinning"></i> Refreshing...');
        
        // Reload the page after a short delay
        setTimeout(function() {
            location.reload();
        }, 500);
    });
    
    // Handle delete button click
    $('.delete_data').click(function(e) {
        e.preventDefault();
        
        deleteId = $(this).data('id');
        deleteRow = $('#row-' + deleteId);
        var poNumber = $(this).data('po');
        
        console.log('Attempting to delete record ID:', deleteId);
        
        // Check if row exists
        if (deleteRow.length === 0) {
            console.error("Row with id 'row-" + deleteId + "' not found.");
            showToast('error', 'Error', 'Could not find the row to delete.');
            return;
        }
        
        // Set PO number in the modal
        $('#poNumber').text(poNumber);
        
        // Show the confirmation modal
        $('#deleteConfirmationModal').modal('show');
    });
    
    // Handle confirm delete button click
    $('#confirmDeleteBtn').click(function() {
        // Hide the modal
        $('#deleteConfirmationModal').modal('hide');
        
        // Show loading state
        $('#confirmDeleteBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
        
        // Send AJAX request
        $.ajax({
            url: _base_url_ + 'Master.php?f=delete_purchase_order',
            type: 'POST',
            data: {id: deleteId},
            dataType: 'json',
            success: function(response) {
                console.log('Server response:', response);
                
                // Reset button state
                $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete');
                
                if(response.status === 'success') {
                    // Remove row from table with animation
                    deleteRow.fadeOut(500, function() {
                        $(this).remove();
                    });
                    
                    // Show success toast
                    showToast('success', 'Success', response.msg || "Record marked as deleted successfully!");
                } else {
                    // Show error toast
                    showToast('error', 'Error', response.msg || "Unknown error occurred");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log("Response text:", xhr.responseText);
                
                // Reset button state
                $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete');
                
                // Show error toast
                showToast('error', 'Error', "Error occurred while processing your request: " + error);
            }
        });
    });
    
    // Function to show toast notification
    function showToast(type, title, message) {
        var toast = $('#deleteToast');
        var toastTitle = $('#toastTitle');
        var toastMessage = $('#toastMessage');
        var toastIcon = toast.find('.toast-header i');
        
        // Reset toast classes
        toast.removeClass('toast-success toast-error toast-info');
        
        // Set toast content
        toastTitle.text(title);
        toastMessage.text(message);
        
        // Set toast style based on type
        if (type === 'success') {
            toast.addClass('toast-success');
            toastIcon.removeClass('text-primary text-danger text-info').addClass('text-success');
            toastIcon.removeClass('fa-info-circle fa-exclamation-circle').addClass('fa-check-circle');
        } else if (type === 'error') {
            toast.addClass('toast-error');
            toastIcon.removeClass('text-primary text-success text-info').addClass('text-danger');
            toastIcon.removeClass('fa-info-circle fa-check-circle').addClass('fa-exclamation-circle');
        } else if (type === 'info') {
            toast.addClass('toast-info');
            toastIcon.removeClass('text-primary text-success text-danger').addClass('text-info');
            toastIcon.removeClass('fa-check-circle fa-exclamation-circle').addClass('fa-info-circle');
        }
        
        // Show the toast
        toast.toast('show');
    }
    
    // Edit button click handler
    $('.edit-btn').click(function(){
        const id = $(this).data('id');
        const po = $(this).data('po');
        const invoice = $(this).data('invoice');
        const dr = $(this).data('dr');
        const indoor = $(this).data('indoor');
        const indoorSerial = $(this).data('indoor-serial');
        const outdoor = $(this).data('outdoor');
        const outdoorSerial = $(this).data('outdoor-serial');
        const supplier = $(this).data('supplier');
        const supplierId = $(this).data('supplier-id');
        const brand = $(this).data('brand');
        const hp = $(this).data('hp');
        const type = $(this).data('type');
        const series = $(this).data('series');
        const price = $(this).data('price');
        const remarks = $(this).data('remarks');
        const deliveryDate = $(this).data('delivery-date');
        
        // Store supplier info in global variables
        currentSupplierId = supplierId;
        currentSupplierName = supplier;
        
        // Populate form fields
        $('#editId').val(id);
        $('#editDeliveryDate').val(deliveryDate);
        $('#editPo').val(po);
        $('#editInvoice').val(invoice);
        $('#editDr').val(dr);
        $('#editIndoor').val(indoor);
        $('#editIndoorSerial').val(indoorSerial);
        $('#editOutdoor').val(outdoor);
        $('#editOutdoorSerial').val(outdoorSerial);
        $('#editBrand').val(brand);
        $('#editHp').val(hp);
        $('#editType').val(type);
        $('#editSeries').val(series);
        $('#editPrice').val(price);
        $('#editRemarks').val(remarks);
        
        // Show the modal first
        $('#editModal').modal('show');
    });
    
    // Set supplier value when modal is shown
    $('#editModal').on('shown.bs.modal', function () {
        console.log('Modal shown, setting supplier value to:', currentSupplierId);
        
        // Set supplier value
        $('#editSupplier').val(currentSupplierId);
        
        // Verify if the value was set correctly
        setTimeout(function() {
            const selectedValue = $('#editSupplier').val();
            console.log('Supplier dropdown value after setting:', selectedValue);
            
            if (selectedValue != currentSupplierId) {
                console.log('Setting by ID failed, trying by name:', currentSupplierName);
                
                // Try to find and set by name
                let found = false;
                $('#editSupplier option').each(function() {
                    if ($(this).text() === currentSupplierName) {
                        $(this).prop('selected', true);
                        found = true;
                        console.log('Supplier set by name');
                        return false;
                    }
                });
                
                if (!found) {
                    console.error('Could not set supplier. ID:', currentSupplierId, 'Name:', currentSupplierName);
                    
                    // Add the supplier to the dropdown if it doesn't exist
                    $('#editSupplier').append($('<option>', {
                        value: currentSupplierId,
                        text: currentSupplierName
                    }));
                    
                    // Now set it
                    $('#editSupplier').val(currentSupplierId);
                    console.log('Added supplier to dropdown and set it');
                }
            } else {
                console.log('Supplier set successfully by ID');
            }
        }, 100);
    });
    
    // Save changes button click handler
    $('#saveChanges').click(function(){
        const id = $('#editId').val();
        const deliveryDate = $('#editDeliveryDate').val();
        const po = $('#editPo').val();
        const invoice = $('#editInvoice').val();
        const dr = $('#editDr').val();
        const indoor = $('#editIndoor').val();
        const indoorSerial = $('#editIndoorSerial').val();
        const outdoor = $('#editOutdoor').val();
        const outdoorSerial = $('#editOutdoorSerial').val();
        const supplierId = $('#editSupplier').val();
        const brand = $('#editBrand').val();
        const hp = $('#editHp').val();
        const type = $('#editType').val();
        const series = $('#editSeries').val();
        const price = $('#editPrice').val();
        const remarks = $('#editRemarks').val();
        
        console.log('Current supplier ID:', supplierId);
        
        // Validation
        if(!deliveryDate) {
            showToast('error', 'Error', 'Please select a delivery date');
            return;
        }
        
        if(!po) {
            showToast('error', 'Error', 'Please enter a P.O. #');
            return;
        }
        
        if(!invoice) {
            showToast('error', 'Error', 'Please enter an Invoice #');
            return;
        }
        
        if(!dr) {
            showToast('error', 'Error', 'Please enter a DR #');
            return;
        }
        
        if(!indoor) {
            showToast('error', 'Error', 'Please enter an Indoor value');
            return;
        }
        
        if(!outdoor) {
            showToast('error', 'Error', 'Please enter an Outdoor value');
            return;
        }
        
        if(!supplierId) {
            showToast('error', 'Error', 'Please select a supplier');
            return;
        }
        
        if(!brand) {
            showToast('error', 'Error', 'Please enter a brand');
            return;
        }
        
        if(!hp) {
            showToast('error', 'Error', 'Please enter HP value');
            return;
        }
        
        if(!type) {
            showToast('error', 'Error', 'Please enter a type');
            return;
        }
        
        if(!series) {
            showToast('error', 'Error', 'Please enter a series');
            return;
        }
        
        if(!price || price <= 0) {
            showToast('error', 'Error', 'Please enter a valid price');
            return;
        }
        
        // Show loading state
        $('#saveChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
        
        // Prepare data for AJAX
        const data = {
            id: id,
            delivery_date: deliveryDate,
            po: po,
            invoice: invoice,
            dr: dr,
            indoor: indoor,
            indoor_serial: indoorSerial,
            outdoor: outdoor,
            outdoor_serial: outdoorSerial,
            supplier_id: supplierId,
            brand: brand,
            hp: hp,
            type: type,
            series: series,
            price: price,
            remarks: remarks
        };
        
        $.ajax({
            url: _base_url_ + "Master.php?f=update_purchase_order",
            method: "POST",
            data: data,
            dataType: "json",
            error: function(err) {
                console.log(err);
                showToast('error', 'Error', 'An error occurred');
                $('#saveChanges').prop('disabled', false).html('Update');
            },
            success: function(resp) {
                if(typeof resp == 'object' && resp.status == 'success') {
                    $('#editModal').modal('hide');
                    showToast('success', 'Success', 'Purchase order updated successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', 'Error', 'An error occurred');
                    $('#saveChanges').prop('disabled', false).html('Update');
                }
            }
        });
    });
    
    // Initialize DataTables with no default order to maintain server-side order
    $('.receiving-table').dataTable({
        columnDefs: [
            { orderable: false, targets: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] }
        ],
        order: [] // This maintains the server-side order (newest first)
    });
    
    $('.dataTable td,.dataTable th').addClass('py-1 px-2 align-middle');
});
</script>
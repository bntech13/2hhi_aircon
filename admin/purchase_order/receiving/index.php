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
    <title>RECEIVED STOCKS</title>
    <div class="card">
        <div class="card-header" style="background-color: #1683e9ff; border-bottom: 2px solid #1683e9ff; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; justify-content: center; align-items: center;">
            <div style="display: inline-block; border: 2px solid white; border-radius: 10px; padding: 8px 20px; margin-top: 0px; position: relative; z-index: 2; background-color: rgba(255, 255, 255, 0.1);">
                <h6 style="color: white; margin: 0; text-shadow: 0 1px 2px #1683e9ff; text-align: center;">
                 <i class="fas fa-money-bill-wave fa-lg" style="color: white;"></i> <b>RECEIVED STOCKS</b>
                </h6>
            </div>
        </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped receiving-table">
                <colgroup>
                    <col width="3%">
                    <col width="8%">
                    <col width="8%">
                    <col width="7%">
                    <col width="7%">
                    <col width="6%">
                    <col width="6%">
                    <col width="6%">
                    <col width="4%">
                    <col width="7%">
                    <col width="8%">
                    <col width="5%">
                    <col width="5%">
                    <col width="8%">
                    <col width="5%">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Received</th>
                        <th>P.O. No.</th>
                        <th>Indoor</th>
                        <th>Indoor Serial</th>
                        <th>Outdoor</th>
                        <th>Outdoor Serial</th>
                        <th>Brand</th>
                        <th>HP</th>
                        <th>Type</th>
                        <th>Series</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    $qry = $conn->query("SELECT p.*, s.name as supplier 
                                       FROM receiving_list p 
                                       LEFT JOIN supplier_list s ON p.supplier_id = s.id 
                                       ORDER BY p.delivery_date DESC");
                    while($row = $qry->fetch_assoc()):
                    ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td class="text-center"><?php echo $i++; ?></td>
                            <td class="text-center"><?php echo date("Y-m-d", strtotime($row['delivery_date'])) ?></td>
                            <td class="text-center"><?php echo $row['po'] ?></td>
                            <td class="text-center"><?php echo $row['indoor'] ?></td>
                            <td class="text-center"><?php echo $row['indoor_serial'] ?></td>
                            <td class="text-center"><?php echo $row['outdoor'] ?></td>
                            <td class="text-center"><?php echo $row['outdoor_serial'] ?></td>
                            <td class="text-center"><?php echo $row['brand'] ?></td>
                            <td class="text-center"><?php echo $row['hp'] ?></td>
                            <td class="text-center"><?php echo $row['type'] ?></td>
                            <td class="text-center"><?php echo $row['series'] ?></td>
                            <td class="text-center"><?php echo $row['unit'] ?></td>
                            <td class="text-center"><?php echo number_format($row['price'], 2) ?></td>
                            <td class="text-center"><?php echo $row['remarks'] ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    Action
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item edit-btn" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>" data-po="<?php echo $row['po'] ?>" data-price="<?php echo $row['price'] ?>"><span class="fa fa-edit text-primary"></span> Edit</a>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Receiving Record</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId" name="id">
                    <div class="form-group">
                        <label for="originalPrice">Original Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">₱</span>
                            </div>
                            <input type="text" class="form-control text-center" id="originalPrice" name="original_price" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editPrice">New Price</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">₱</span>
                            </div>
                            <input type="number" class="form-control text-center" id="editPrice" name="price" step="0.01" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Update Price</button>
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
                <div class="text-center mb-4">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h4>Are you sure you want to permanently delete this record?</h4>
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
        background-color: #3498db; /* Blue color for header */
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
        background-color: #3498db;
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
</style>

<script>
 $(document).ready(function(){
    var deleteId = null;
    var deleteRow = null;
    
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
            url: _base_url_ + 'Master.php?f=delete_receiving',
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
                    showToast('success', 'Success', response.msg || "Record deleted successfully!");
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
        toast.removeClass('toast-success toast-error');
        
        // Set toast content
        toastTitle.text(title);
        toastMessage.text(message);
        
        // Set toast style based on type
        if (type === 'success') {
            toast.addClass('toast-success');
            toastIcon.removeClass('text-primary text-danger').addClass('text-success');
            toastIcon.removeClass('fa-info-circle').addClass('fa-check-circle');
        } else if (type === 'error') {
            toast.addClass('toast-error');
            toastIcon.removeClass('text-primary text-success').addClass('text-danger');
            toastIcon.removeClass('fa-info-circle fa-check-circle').addClass('fa-exclamation-circle');
        }
        
        // Show the toast
        toast.toast('show');
    }
    
    // Edit button click handler
    $('.edit-btn').click(function(){
        const id = $(this).data('id');
        const po = $(this).data('po');
        const price = $(this).data('price');
        
        $('#editId').val(id);
        $('#originalPrice').val(parseFloat(price).toFixed(2));
        $('#editPrice').val(price);
        
        $('#editModal').modal('show');
    });
    
    // Save changes button click handler
    $('#saveChanges').click(function(){
        const id = $('#editId').val();
        const originalPrice = $('#originalPrice').val();
        const newPrice = $('#editPrice').val();
        
        if(!newPrice || newPrice <= 0) {
            showToast('error', 'Error', 'Please enter a valid price');
            return;
        }
        
        if(originalPrice == newPrice) {
            showToast('warning', 'Warning', 'New price is the same as original price');
            return;
        }
        
        // Show loading state
        $('#saveChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
        
        $.ajax({
            url: _base_url_ + "Master.php?f=update_receiving",
            method: "POST",
            data: {id: id, price: newPrice},
            dataType: "json",
            error: err => {
                console.log(err);
                showToast('error', 'Error', 'An error occurred');
                $('#saveChanges').prop('disabled', false).html('Update Price');
            },
            success: function(resp) {
                if(typeof resp == 'object' && resp.status == 'success') {
                    $('#editModal').modal('hide');
                    showToast('success', 'Success', 'Price updated successfully');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast('error', 'Error', 'An error occurred');
                    $('#saveChanges').prop('disabled', false).html('Update Price');
                }
            }
        });
    });
    
    $('.receiving-table').dataTable({
        columnDefs: [
            { orderable: false, targets: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13] }
        ],
        order:[[1,'desc']]
    });
    
    $('.dataTable td,.dataTable th').addClass('py-1 px-2 align-middle');
});
</script>
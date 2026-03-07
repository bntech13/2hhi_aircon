<?php 
// ... (previous code remains the same)
?>

<!-- Ensure Bootstrap CSS is included -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

<div class="card card-outline card-primary">
    <div class="card">
        <div class="card-header" style="background-color: #0241b6ff; border-bottom: 2px solid #0241b6ff; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; justify-content: center; align-items: center;">
            <div style="display: inline-block; border: 2px solid white; border-radius: 10px; padding: 8px 20px; margin-top: 0px; position: relative; z-index: 2; background-color: rgba(255, 255, 255, 0.1);">
                <h6 style="color: white; margin: 0; text-shadow: 0 1px 2px #0241b6ff; text-align: center;">
                 <i class="fas fa-money-bill-wave fa-lg" style="color: white;"></i> <b>SALES LIST</b>
                </h6>
            </div>
        </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped receiving-table" id="salesTable">
                <colgroup>
                    <col width="3%"> <!-- ID -->
                    <col width="8%"> <!-- Sale Date (NEW) -->
                    <col width="8%"> <!-- Invoice Number -->
                    <col width="8%"> <!-- Transaction Number -->
                    <col width="7%"> <!-- Indoor -->
                    <col width="7%"> <!-- Indoor Serial -->
                    <col width="6%"> <!-- Outdoor -->
                    <col width="6%"> <!-- Outdoor Serial -->
                    <col width="6%"> <!-- Client Name -->
                    <col width="6%"> <!-- Brand -->
                    <col width="4%"> <!-- HP -->
                    <col width="7%"> <!-- Type -->
                    <col width="8%"> <!-- Series -->
                    <col width="5%"> <!-- Unit -->
                    <col width="5%"> <!-- Price -->
                    <col width="8%"> <!-- Remarks -->
                    <col width="5%"> <!-- Action -->
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th> <!-- NEW COLUMN -->
                        <th>Invoice #</th>
                        <th>Transaction #</th>
                        <th>Indoor</th>
                        <th>Indoor Serial</th>
                        <th>Outdoor</th>
                        <th>Outdoor Serial</th>
                        <th>Customer</th>
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
                    // Initialize deleted items array if not already set
                    if (!isset($_SESSION['deleted_sales_items'])) {
                        $_SESSION['deleted_sales_items'] = array();
                    }
                    
                    // Modified query to fetch from sales_list with new fields, excluding deleted records
                    // ORDER BY id DESC to get newest records first
                    $qry = $conn->query("SELECT s.*, s.invoice_number, s.transaction_number 
                                       FROM sales_list s 
                                       WHERE s.deleted = 0 OR s.deleted IS NULL
                                       ORDER BY s.id DESC");
                    
                    // Check if query was successful
                    if (!$qry) {
                        // Display error for debugging (remove in production)
                        echo "<tr><td colspan='17' class='text-center text-danger'>Error: " . $conn->error . "</td></tr>";
                    } else {
                        // Start with ID 1 for the first (newest) record
                        $i = 1;
                        
                        while($row = $qry->fetch_assoc()):
                            // Skip deleted items in session as well (for backward compatibility)
                            if (in_array($row['id'], $_SESSION['deleted_sales_items'])) {
                                continue;
                            }
                    ?>
                        <tr id="row-<?php echo $row['id']; ?>" data-id="<?php echo $row['id']; ?>">
                            <td class="text-center"><?php echo $i++; ?></td> <!-- Display ascending ID starting from 1 -->
                            <td class="text-center"><?php echo date("Y-m-d", strtotime($row['sale_date'])) ?></td> <!-- NEW COLUMN -->
                            <td class="text-center"><?php echo htmlspecialchars($row['invoice_number']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['transaction_number']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['indoor']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['indoor_serial']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['outdoor']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['outdoor_serial']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['client_name']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['brand']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['hp']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['type']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['series']) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['unit']) ?></td>
                            <td class="text-center"><?php echo number_format($row['price'], 2) ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['remarks']) ?></td>
                            <td align="center">
                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown"> Action<span class="sr-only">Toggle Dropdown</span>  </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?php echo base_url.'admin?page=sales/view_sale&id='.$row['id'] ?>" data-id="<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> View</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item edit-btn" href="javascript:void(0)" 
                                       data-id="<?php echo $row['id'] ?>" 
                                       data-sale-date="<?php echo date("Y-m-d", strtotime($row['sale_date'])) ?>"
                                       data-invoice-number="<?php echo htmlspecialchars($row['invoice_number']) ?>"
                                       data-transaction-number="<?php echo htmlspecialchars($row['transaction_number']) ?>"
                                       data-indoor="<?php echo htmlspecialchars($row['indoor']) ?>"
                                       data-indoor-serial="<?php echo htmlspecialchars($row['indoor_serial']) ?>"
                                       data-outdoor="<?php echo htmlspecialchars($row['outdoor']) ?>"
                                       data-outdoor-serial="<?php echo htmlspecialchars($row['outdoor_serial']) ?>"
                                       data-client-name="<?php echo htmlspecialchars($row['client_name']) ?>"
                                       data-brand="<?php echo htmlspecialchars($row['brand']) ?>"
                                       data-hp="<?php echo htmlspecialchars($row['hp']) ?>"
                                       data-type="<?php echo htmlspecialchars($row['type']) ?>"
                                       data-series="<?php echo htmlspecialchars($row['series']) ?>"
                                       data-unit="<?php echo htmlspecialchars($row['unit']) ?>"
                                       data-price="<?php echo htmlspecialchars($row['price']) ?>"
                                       data-remarks="<?php echo htmlspecialchars($row['remarks']) ?>">
                                       <span class="fa fa-edit text-primary"></span> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>" data-identifier="<?php echo htmlspecialchars($row['invoice_number'] ?: ($row['transaction_number'] ?: $row['id'])) ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; 
                    } // Added missing closing brace for else block ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Sales Record</h5>
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
                                <label for="editSaleDate">Sale Date</label>
                                <input type="date" class="form-control" id="editSaleDate" name="sale_date" required>
                            </div>
                            <div class="form-group">
                                <label for="editInvoiceNumber">Invoice #</label>
                                <input type="text" class="form-control" id="editInvoiceNumber" name="invoice_number" required>
                            </div>
                            <div class="form-group">
                                <label for="editTransactionNumber">Transaction #</label>
                                <input type="text" class="form-control" id="editTransactionNumber" name="transaction_number" required>
                            </div>
                            <div class="form-group">
                                <label for="editIndoor">Indoor</label>
                                <input type="text" class="form-control" id="editIndoor" name="indoor" required>
                            </div>
                            <div class="form-group">
                                <label for="editIndoorSerial">Indoor Serial</label>
                                <input type="text" class="form-control" id="editIndoorSerial" name="indoor_serial" required>
                            </div>
                            <div class="form-group">
                                <label for="editOutdoor">Outdoor</label>
                                <input type="text" class="form-control" id="editOutdoor" name="outdoor" required>
                            </div>
                            <div class="form-group">
                                <label for="editOutdoorSerial">Outdoor Serial</label>
                                <input type="text" class="form-control" id="editOutdoorSerial" name="outdoor_serial" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editClientName">Client Name</label>
                                <input type="text" class="form-control" id="editClientName" name="client_name" required>
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
                                <label for="editUnit">Unit</label>
                                <input type="text" class="form-control" id="editUnit" name="unit" required>
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
        border: 3px solid #000;
        border-collapse: collapse;
    }
    .receiving-table th, .receiving-table td {
        padding: 8px;
        text-transform: uppercase;
        vertical-align: middle;
        border: 2px solid #000;
        text-align: center;
        font-weight: bold;
        color: #000;
        font-family: 'Calibri', 'Arial', sans-serif;
    }
    .receiving-table th {
        background-color: #0241b6ff;
        color: #fff;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
        border-color: #000;
    }
    .receiving-table td {
        overflow: visible;
        position: relative;
    }
    .receiving-table tbody tr {
        transition: background-color 0.3s ease;
    }
    .receiving-table tbody tr:hover {
        background-color: #a8d0ff;
        cursor: pointer;
    }
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        float: left;
        min-width: 10rem;
        padding: 0.5rem 0;
        margin: 0.125rem 0 0;
        font-size: 1rem;
        color: #212529;
        text-align: left;
        list-style: none;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.25rem;
    }
    .dropdown-menu.show {
        display: block;
    }
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    .text-right {
        text-align: center;
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
    .loader {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 2s linear infinite;
        display: inline-block;
        margin-right: 10px;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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

<!-- Add this script to define the correct base URL for JavaScript -->
<script>
    // Make sure this points to the correct location of Master.php
    var _base_url_ = '<?php echo base_url . "classes/"; ?>';
    console.log('Base URL set to:', _base_url_);
</script>

<!-- Include jQuery and Bootstrap JS in correct order -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Use jQuery noConflict to avoid conflicts with other libraries
jQuery.noConflict();

// Use a closure to avoid conflicts
(function($) {
    // All our code will use $ inside this closure
    
    // Document ready function
    $(document).ready(function() {
        // Prevent back button cache
        $(window).on("pageshow", function(event) {
            if (event.originalEvent.persisted) {
                window.location.reload();
            }
        });
        
        // Initialize DataTable
        $('.receiving-table').dataTable({
            columnDefs: [
                { orderable: false, targets: [16] } // Action column is now index 16 (0-indexed)
            ],
            order:[[1,'desc']] // Sort by Date column (index 1) in descending order
        });
        
        $('.dataTable td,.dataTable th').addClass('py-1 px-2 align-middle');
        
        // =========================================================
        // FIX: Use Event Delegation ($(document).on(...)) for buttons
        // This ensures Edit/Delete work after searching/sorting
        // =========================================================

        // 1. Edit button click handler (Delegated)
        $(document).on('click', '.edit-btn', function(){
            const id = $(this).data('id');
            const saleDate = $(this).data('sale-date');
            const invoiceNumber = $(this).data('invoice-number');
            const transactionNumber = $(this).data('transaction-number');
            const indoor = $(this).data('indoor');
            const indoorSerial = $(this).data('indoor-serial');
            const outdoor = $(this).data('outdoor');
            const outdoorSerial = $(this).data('outdoor-serial');
            const clientName = $(this).data('client-name');
            const brand = $(this).data('brand');
            const hp = $(this).data('hp');
            const type = $(this).data('type');
            const series = $(this).data('series');
            const unit = $(this).data('unit');
            const price = $(this).data('price');
            const remarks = $(this).data('remarks');
            
            // Populate form fields
            $('#editId').val(id);
            $('#editSaleDate').val(saleDate);
            $('#editInvoiceNumber').val(invoiceNumber);
            $('#editTransactionNumber').val(transactionNumber);
            $('#editIndoor').val(indoor);
            $('#editIndoorSerial').val(indoorSerial);
            $('#editOutdoor').val(outdoor);
            $('#editOutdoorSerial').val(outdoorSerial);
            $('#editClientName').val(clientName);
            $('#editBrand').val(brand);
            $('#editHp').val(hp);
            $('#editType').val(type);
            $('#editSeries').val(series);
            $('#editUnit').val(unit);
            $('#editPrice').val(price);
            $('#editRemarks').val(remarks);
            
            // Show the modal
            $('#editModal').modal('show');
        });
        
        // 2. Delete button click handler (Delegated)
        var deleteId = null;
        var deleteRow = null;

        $(document).on('click', '.delete_data', function(e) {
            e.preventDefault();
            
            deleteId = $(this).data('id');
            deleteRow = $('#row-' + deleteId); // This selector might return empty after search, handled in AJAX success
            var identifier = $(this).data('identifier');
            
            console.log('Attempting to delete record ID:', deleteId);
            
            // Set identifier in the modal
            $('#poNumber').text(identifier);
            
            // Show the confirmation modal
            $('#deleteConfirmationModal').modal('show');
        });
        
        // Save changes button click handler (Static button, no change needed)
        $('#saveChanges').click(function(){
            const id = $('#editId').val();
            const saleDate = $('#editSaleDate').val();
            const invoiceNumber = $('#editInvoiceNumber').val();
            const transactionNumber = $('#editTransactionNumber').val();
            const indoor = $('#editIndoor').val();
            const indoorSerial = $('#editIndoorSerial').val();
            const outdoor = $('#editOutdoor').val();
            const outdoorSerial = $('#editOutdoorSerial').val();
            const clientName = $('#editClientName').val();
            const brand = $('#editBrand').val();
            const hp = $('#editHp').val();
            const type = $('#editType').val();
            const series = $('#editSeries').val();
            const unit = $('#editUnit').val();
            const price = $('#editPrice').val();
            const remarks = $('#editRemarks').val();
            
            // Validation
            if(!saleDate) { showToast('error', 'Error', 'Please select a sale date'); return; }
            if(!invoiceNumber) { showToast('error', 'Error', 'Please enter an Invoice #'); return; }
            if(!transactionNumber) { showToast('error', 'Error', 'Please enter a Transaction #'); return; }
            if(!indoor) { showToast('error', 'Error', 'Please enter an Indoor value'); return; }
            if(!outdoor) { showToast('error', 'Error', 'Please enter an Outdoor value'); return; }
            if(!clientName) { showToast('error', 'Error', 'Please enter a Client Name'); return; }
            if(!brand) { showToast('error', 'Error', 'Please enter a brand'); return; }
            if(!hp) { showToast('error', 'Error', 'Please enter HP value'); return; }
            if(!type) { showToast('error', 'Error', 'Please enter a type'); return; }
            if(!series) { showToast('error', 'Error', 'Please enter a series'); return; }
            if(!unit) { showToast('error', 'Error', 'Please enter a unit'); return; }
            if(!price || price <= 0) { showToast('error', 'Error', 'Please enter a valid price'); return; }
            
            // Show loading state
            $('#saveChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
            
            // Prepare data for AJAX
            const data = {
                id: id,
                sale_date: saleDate,
                invoice_number: invoiceNumber,
                transaction_number: transactionNumber,
                indoor: indoor,
                indoor_serial: indoorSerial,
                outdoor: outdoor,
                outdoor_serial: outdoorSerial,
                client_name: clientName,
                brand: brand,
                hp: hp,
                type: type,
                series: series,
                unit: unit,
                price: price,
                remarks: remarks
            };
            
            $.ajax({
                url: _base_url_ + "Master.php?f=update_sales",
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
                        showToast('success', 'Success', 'Sales record updated successfully');
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
        
        // Handle confirm delete button click (Static button)
        $('#confirmDeleteBtn').click(function() {
            $('#deleteConfirmationModal').modal('hide');
            $('#confirmDeleteBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: _base_url_ + 'Master.php?f=delete_sales',
                type: 'POST',
                data: {id: deleteId},
                dataType: 'json',
                timeout: 10000,
                cache: false,
                success: function(response) {
                    $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete');
                    
                    if (response.status === 'success') {
                        // Note: When using DataTables with Ajax reload, simply removing the row 
                        // via JS might cause display glitches if the DOM row doesn't exist (e.g. after search).
                        // Reloading is safer.
                        showToast('success', 'Success', response.msg || 'Sales record deleted successfully!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast('error', 'Error', response.msg || 'Failed to delete sales record.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete');
                    showToast('error', 'Error', "Error occurred while processing your request.");
                }
            });
        });
        
        // Dropdown fix (already delegated, kept as is)
        $(document).on('click', '.dropdown-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $dropdown = $(this);
            const $menu = $dropdown.next('.dropdown-menu');
            
            $('.dropdown-menu').not($menu).removeClass('show');
            $menu.toggleClass('show');
            
            $(document).one('click', function() {
                $menu.removeClass('show');
            });
        });
        
        $(document).on('click', '.dropdown-menu', function(e) {
            e.stopPropagation();
        });
        
        // Function to show toast notification
        function showToast(type, title, message) {
            var toast = $('#deleteToast');
            var toastTitle = $('#toastTitle');
            var toastMessage = $('#toastMessage');
            var toastIcon = toast.find('.toast-header i');
            
            toast.removeClass('toast-success toast-error');
            toastTitle.text(title);
            toastMessage.html(message);
            
            if (type === 'success') {
                toast.addClass('toast-success');
                toastIcon.removeClass('text-primary text-danger').addClass('text-success');
                toastIcon.removeClass('fa-info-circle').addClass('fa-check-circle');
            } else if (type === 'error') {
                toast.addClass('toast-error');
                toastIcon.removeClass('text-primary text-success').addClass('text-danger');
                toastIcon.removeClass('fa-info-circle fa-check-circle').addClass('fa-exclamation-circle');
            }
            
            toast.toast('show');
        }
    });
    
})(jQuery); 
</script>
<div class="card">
    <div class="card-header" style="background-color: #f3a464ff; border-bottom: 2px solid #f3a464ff; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; justify-content: center; align-items: center;">
        <div style="display: inline-block; border: 2px solid white; border-radius: 10px; padding: 8px 20px; margin-top: 0px; position: relative; z-index: 2; background-color: rgba(255, 255, 255, 0.1);">
            <h6 style="color: white; margin: 0; text-shadow: 0 1px 2px #f3a464ff; text-align: center; font-family: 'Calibri', sans-serif;">
               <i class="fas fa-calendar"> </i>   <b>TASK LIST</b>
            </h6>
        </div>
    </div>
    <div class="card-body">
        <?php 
        $schedule_id = isset($_GET['id']) ? $_GET['id'] : null;
        $specific_schedule = null;
        
        if ($schedule_id && isset($conn)) {
            $schedule_qry = $conn->query("SELECT * FROM schedule_list WHERE id = $schedule_id");
            if ($schedule_qry && $schedule_qry->num_rows > 0) {
                $specific_schedule = $schedule_qry->fetch_assoc();
            }
        }
        
        // If a specific schedule is requested, show only its details
        if ($specific_schedule):
        ?>
        <div class="card" style="border: 2px solid #f3a464ff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div class="card-header" style="background-color: #f3a464ff; color: white; border-bottom: 2px solid #f3a464ff;">
                <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>TASK DETAILS</h5>
            </div>
            <div class="card-body">
                <!-- Main Schedule Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Service Type:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['service_type_2'] ?: 'N/A')); ?></p>
                        <p><strong>Date:</strong> <?php echo date("F d, Y", strtotime($specific_schedule['start_date'])); ?></p>
                         <p><strong>Service Type:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['service_type'])); ?></p>
                         <p><strong>Date:</strong> <?php echo date("F d, Y", strtotime($specific_schedule['end_date'])); ?></p>

                    </div>
                    <div class="col-md-6">
                        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['customer_name'])); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['address'])); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['customer_cp'] ?: 'N/A')); ?></p>
                        <p><strong>Staff:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['staff_name'])); ?></p>
                    </div>
                </div>
                
                <?php if ($specific_schedule['remarks']): ?>
                <div class="mb-4">
                    <p><strong>Remarks:</strong> <?php echo htmlspecialchars(strtoupper($specific_schedule['remarks'])); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- TASK ITEMS SECTION REMOVED -->
                
                <div class="mt-4 text-center">
                    <button type="button" class="btn btn-primary" id="markCompleteBtn" data-id="<?php echo $specific_schedule['id']; ?>">
                        <i class="fas fa-check mr-1"></i> Mark As Complete
                    </button>
                    <a href="<?php echo base_url.'admin?page=calendar' ?>" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Task List
                    </a>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- If no specific schedule is requested, show the table with all schedules -->
        <div class="container-fluid">
            <table class="table table-bordered table-stripped" id="taskTable">
                <thead class="custom-table-header">
                    <tr>
                        <th>#</th>
                        <?php 
                        if(isset($conn)) {
                            // Get all column names from schedule_list table
                            $columns_query = $conn->query("SHOW COLUMNS FROM schedule_list");
                            $columns = [];
                            while($col = $columns_query->fetch_assoc()) {
                                $columns[] = $col['Field'];
                            }
                            
                            // Columns to exclude (including 'status')
                            $excluded_columns = ['id', 'title', 'description', 'client_name', 'status', 'task_items'];
                            
                            // Display all column headers except excluded ones
                            foreach($columns as $column) {
                                if(!in_array($column, $excluded_columns)) {
                                    // UPDATED: Check if column is 'end_date' to rename specifically
                                    if ($column == 'end_date') {
                                        echo "<th>Date</th>";
                                    } else {
                                        echo "<th>" . ucwords(str_replace('_', ' ', $column)) . "</th>";
                                    }
                                }
                            }
                        }
                        ?>
                        <th>Status</th> <!-- Added Status column header -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    // Check if connection exists
                    if(isset($conn)) {
                        // Get all columns from schedule_list table
                        $columns_query = $conn->query("SHOW COLUMNS FROM schedule_list");
                        $columns = [];
                        while($col = $columns_query->fetch_assoc()) {
                            $columns[] = $col['Field'];
                        }
                        
                        // Columns to exclude (including 'status')
                        $excluded_columns = ['id', 'title', 'description', 'client_name', 'status', 'task_items'];
                        
                        // Modified query to order by start_date in descending order
                        $qry = $conn->query("SELECT * FROM `schedule_list` ORDER BY `start_date` DESC, `id` DESC") or die($conn->error);
                        
                        if($qry && $qry->num_rows > 0):
                            while($row = $qry->fetch_assoc()):
                    ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++; ?></td>
                                    <?php 
                                    // Display all values from schedule_list except excluded columns
                                    foreach($columns as $column) {
                                        if(!in_array($column, $excluded_columns)) {
                                            // Format date columns
                                            if(in_array($column, ['start_date', 'end_date', 'date_created', 'date_updated'])) {
                                           echo "<td class=\"text-center\">" . date("m.d.Y", strtotime($row[$column])) . "</td>";
                                            } else {
                                                // Convert text to uppercase before displaying
                                                echo "<td class=\"text-center\">" . htmlspecialchars(strtoupper($row[$column])) . "</td>";
                                            }
                                        }
                                    }
                                    
                                    // Determine status - PENDING by default, COMPLETED when marked
                                    $status = 'PENDING';
                                    $status_class = 'badge badge-secondary';
                                    
                                    // Check if status is stored in database as completed
                                    if (isset($row['status']) && $row['status'] == 'completed') {
                                        $status = 'COMPLETED';
                                        $status_class = 'badge badge-success';
                                    }
                                    ?>
                                    <td class="text-center"><span class="<?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                                    <td class="text-center" align="center">
                                        <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                                Action
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu" role="menu">
                                            <a class="dropdown-item" href="<?php echo base_url.'admin?page=calendar&id='.$row['id'] ?>" data-id="<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> View</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item edit-btn" href="javascript:void(0)" 
                                               data-id="<?php echo $row['id'] ?>" 
                                               data-service-type="<?php echo htmlspecialchars($row['service_type']) ?>" 
                                               data-service-type-2="<?php echo htmlspecialchars($row['service_type_2']) ?>" 
                                               data-start-date="<?php echo date("Y-m-d", strtotime($row['start_date'])) ?>" 
                                               data-end-date="<?php echo date("Y-m-d", strtotime($row['end_date'])) ?>" 
                                               data-customer-name="<?php echo htmlspecialchars($row['customer_name']) ?>" 
                                               data-address="<?php echo htmlspecialchars($row['address']) ?>" 
                                               data-customer-cp="<?php echo htmlspecialchars($row['customer_cp']) ?>" 
                                               data-staff-name="<?php echo htmlspecialchars($row['staff_name']) ?>" 
                                               data-remarks="<?php echo htmlspecialchars($row['remarks']) ?>">
                                               <span class="fa fa-edit text-primary"></span> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; 
                        else: ?>
                            <tr>
                                <td colspan="<?php echo count($columns) - count($excluded_columns) + 2; ?>" class="text-center">No schedule records found</td>
                            </tr>
                        <?php endif;
                    } else { ?>
                        <tr>
                            <td colspan="10" class="text-center">Database connection not established</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #f3a464ff; color: white;">
                <h5 class="modal-title" id="editModalLabel">Edit Task</h5>
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
                                <label for="editServiceType">Service Type</label>
                                <input type="text" class="form-control" id="editServiceType" name="service_type" required>
                            </div>
                            <div class="form-group">
                                <label for="editServiceType2">Service Type</label>
                                <input type="text" class="form-control" id="editServiceType2" name="service_type_2">
                            </div>
                            <div class="form-group">
                                <label for="editStartDate">Date</label>
                                <input type="date" class="form-control" id="editStartDate" name="start_date" required>
                            </div>
                            <div class="form-group">
                                <label for="editEndDate">Date</label>
                                <input type="date" class="form-control" id="editEndDate" name="end_date" required>
                            </div>
                            <div class="form-group">
                                <label for="editCustomerName">Customer Name</label>
                                <input type="text" class="form-control" id="editCustomerName" name="customer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAddress">Address</label>
                                <textarea class="form-control" id="editAddress" name="address" rows="2" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="editCustomerCp">Contact </label>
                                <input type="text" class="form-control" id="editCustomerCp" name="customer_cp">
                            </div>
                            <div class="form-group">
                                <label for="editStaffName">Staff</label>
                                <input type="text" class="form-control" id="editStaffName" name="staff_name" required>
                            </div>
                            <div class="form-group">
                                <label for="editRemarks">Remarks</label>
                                <textarea class="form-control" id="editRemarks" name="remarks" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Task Items Section -->
                    <div class="mt-4">
                        <h5>Task Items</h5>
                        <div id="taskItemsContainer">
                            <!-- Task items will be dynamically added here -->
                        </div>
                        <button type="button" id="addTaskItemBtn" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-plus mr-1"></i> Add Task Item
                        </button>
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

<!-- Success Message Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Task Completed Successfully!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <p>The task has been marked as completed successfully.</p>
                <p class="text-muted">You will be redirected to the completed tasks page...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="goToCompletedTasksBtn">Go to Completed Tasks</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Message Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">Error Occurred</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                    <p id="errorMessage">An error occurred while processing your request.</p>
                </div>
                <div class="mt-3">
                    <h6>Debug Information:</h6>
                    <pre id="debugInfo" class="bg-light p-2 rounded"></pre>
                </div>
                <div class="mt-3">
                    <h6>Server Response:</h6>
                    <pre id="serverResponse" class="bg-light p-2 rounded">[Empty Response]</pre>
                </div>
                <div class="mt-3">
                    <h6>Troubleshooting Steps:</h6>
                    <ol>
                        <li>Check if the server-side script exists at the specified URL</li>
                        <li>Verify that the server-side script has no PHP errors</li>
                        <li>Check server error logs for more details</li>
                        <li>Ensure the database connection is working</li>
                    </ol>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .dark-orange-header {
        background-color: #f3a464ff !important;
        color: white !important;
    }
    .dark-orange-header .card-title {
        color: white !important;
    }
    .dark-orange-header .btn {
        color: white !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
    }
    .dark-orange-header .btn:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }
    
    /* Table header styles */
    .custom-table-header {
        background-color: #f3a464ff !important;
    }
    
    /* Table border and text alignment styles */
    .table {
        border: 2px solid #f3a464ff !important;
        border-collapse: collapse !important;
    }
    
    /* Table body cells */
    .table td {
        border: 2px solid #f3a464ff !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-weight: bold !important;
        padding: 8px !important;
        text-transform: uppercase !important; /* Added to capitalize all text */
    }
    
    /* Table header cells - with white borders */
    .table th {
        background-color: #f3a464ff !important;
        color: white !important;
        border: 2px solid white !important; /* White border for all sides */
        text-shadow: 0 1px 2px #f3a464ff;
        text-align: center !important;
        font-weight: bold !important;
        padding: 8px !important;
        text-transform: uppercase !important; /* Added to capitalize all text */
    }
    
    .table-bordered {
        border: 2px solid #f3a464ff !important;
    }
    .table-bordered th, .table-bordered td {
        border: 2px solid #f3a464ff !important;
        font-weight: bold !important;
        text-transform: uppercase !important; /* Added to capitalize all text */
    }
    
    /* Override for header borders specifically */
    .table-bordered .custom-table-header th {
        border: 2px solid white !important;
    }
    
    /* Add hover effect for table rows */
    .table tbody tr:hover {
        background-color: rgba(196, 90, 3, 0.2) !important; /* Orange highlight with transparency */
        cursor: pointer;
    }
    
    /* Add hover effect for action buttons */
    .table tbody tr:hover .btn {
        background-color: rgba(255, 255, 255, 0.3) !important;
    }
    
    /* Style for the schedule details card */
    .schedule-details-card {
        border: 2px solid #f3a464ff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Style for the back button */
    .btn-back {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    .btn-back:hover {
        background-color: #5a6268;
        color: white;
        border-color: #545b62;
    }
    
    /* Status badge styles */
    .badge {
        padding: 5px 10px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 4px;
    }
    
    .badge-secondary {
        background-color: #6c757d;
    }
    
    .badge-success {
        background-color: #28a745;
    }
    
    /* Edit modal styles */
    .modal-header {
        background-color: #f3a464ff;
        color: white;
    }
    
    .modal-header .close {
        color: white;
    }
    
    /* Task items section styles */
    .task-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
    }
    
    .task-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .task-item-body {
        display: flex;
        flex-wrap: wrap;
    }
    
    .task-item-field {
        flex: 1 0 30%;
        margin-right: 10px;
        margin-bottom: 10px;
    }
    
    .task-item-field label {
        font-weight: bold;
        font-size: 0.85rem;
    }
    
    .task-item-field input, .task-item-field textarea {
        font-size: 0.85rem;
    }
</style>

<script>
    // Set base URL if not already defined
    if (typeof _base_url_ === 'undefined') {
        var _base_url_ = '<?php echo base_url; ?>';
        console.log("Base URL set to: " + _base_url_);
    }
    
    $(document).ready(function(){
        $('.delete_data').click(function(){
            _conf("Are you sure to delete this Schedule Record permanently?","delete_schedule",[$(this).attr('data-id')])
        })
        $('.table td,.table th').addClass('py-1 px-2 align-middle')
        
        // Initialize DataTable with sorting by start_date column in descending order
        $('#taskTable').dataTable({
            order: [[2, 'desc']], // Sort by the third column (start_date) in descending order
            drawCallback: function(settings) {
                var api = this.api();
                // Update row numbers
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + api.page.info().start;
                });
            }
        });
        
        // Handle Mark As Complete button click
        $('#markCompleteBtn').click(function(){
            var scheduleId = $(this).data('id');
            console.log("Marking schedule ID: " + scheduleId + " as complete");
            
            // Show confirmation dialog
            if(confirm("Are you sure you want to mark this task as completed?")){
                start_loader();
                
                // Use the most likely URL
                var url = _base_url_ + "classes/Master.php?f=mark_schedule_complete";
                
                // AJAX request to update status
                $.ajax({
                    url: url,
                    method: "POST",
                    data: {id: scheduleId},
                    dataType: "json",
                    beforeSend: function(xhr) {
                        console.log("Sending AJAX request to: " + url);
                        console.log("Data: ", {id: scheduleId});
                    },
                    error: function(xhr, status, error){
                        console.log("AJAX Error:");
                        console.log("Status: " + status);
                        console.log("Error: " + error);
                        console.log("Response Text: " + xhr.responseText);
                        console.log("Response Code: " + xhr.status);
                        
                        end_loader();
                        $('#errorMessage').text("Failed to mark task as complete. Please check the server logs for details.");
                        $('#debugInfo').text("URL: " + url + "\nStatus: " + status + "\nError: " + error);
                        $('#serverResponse').text(xhr.responseText);
                        $('#errorModal').modal('show');
                    },
                    success: function(response){
                        console.log("Server Response (raw):", response);
                        
                        end_loader();
                        
                        if(typeof response == 'object' && response.status == 'success'){
                            // Show success modal
                            $('#successModal').modal('show');
                            
                            // Set up redirect to completed tasks page when modal is closed
                            $('#successModal').on('hidden.bs.modal', function () {
                                window.location.href = "<?php echo base_url.'admin?page=calendar/completed_task' ?>";
                            });
                            
                            // Also set up redirect when "Go to Completed Tasks" button is clicked
                            $('#goToCompletedTasksBtn').click(function() {
                                $('#successModal').modal('hide');
                                // The redirect will happen when the modal is hidden
                            });
                            
                            // Auto-redirect after 3 seconds
                            setTimeout(function() {
                                if ($('#successModal').hasClass('show')) {
                                    $('#successModal').modal('hide');
                                } else {
                                    window.location.href = "<?php echo base_url.'admin?page=calendar/completed_task' ?>";
                                }
                            }, 3000);
                        } else {
                            var errorMsg = response.msg || "Unknown error occurred";
                            console.log("Error in response: " + errorMsg);
                            
                            // Show error modal with server message
                            $('#errorMessage').text(errorMsg);
                            $('#debugInfo').text("URL: " + url + "\nParsed JSON: " + JSON.stringify(response, null, 2));
                            $('#serverResponse').text(JSON.stringify(response));
                            $('#errorModal').modal('show');
                        }
                    }
                });
            }
        });
        
        // Edit button click handler
        $('.edit-btn').click(function(){
            const id = $(this).data('id');
            const serviceType = $(this).data('service-type');
            const serviceType2 = $(this).data('service-type-2');
            const startDate = $(this).data('start-date');
            const endDate = $(this).data('end-date');
            const customerName = $(this).data('customer-name');
            const address = $(this).data('address');
            const customerCp = $(this).data('customer-cp');
            const staffName = $(this).data('staff-name');
            const remarks = $(this).data('remarks');
            
            // Populate form fields
            $('#editId').val(id);
            $('#editServiceType').val(serviceType);
            $('#editServiceType2').val(serviceType2);
            $('#editStartDate').val(startDate);
            $('#editEndDate').val(endDate);
            $('#editCustomerName').val(customerName);
            $('#editAddress').val(address);
            $('#editCustomerCp').val(customerCp);
            $('#editStaffName').val(staffName);
            $('#editRemarks').val(remarks);
            
            // Clear existing task items
            $('#taskItemsContainer').empty();
            
            // Add at least one task item by default
            addTaskItem();
            
            // Show the modal
            $('#editModal').modal('show');
        });
        
        // Add Task Item button click handler
        $('#addTaskItemBtn').click(function(){
            addTaskItem();
        });
        
        // Function to add a new task item
        function addTaskItem() {
            const itemCount = $('#taskItemsContainer .task-item').length;
            const taskItemHtml = `
                <div class="task-item">
                    <div class="task-item-header">
                        <h6>Task Item #${itemCount + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-task-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="task-item-body">
                        <div class="task-item-field">
                            <label>Service Type</label>
                            <input type="text" class="form-control" name="task_items[${itemCount}][item_service_type]">
                        </div>
                        <div class="task-item-field">
                            <label>Service Type </label>
                            <input type="text" class="form-control" name="task_items[${itemCount}][item_service_type_2]">
                        </div>
                        <div class="task-item-field">
                            <label>Start Date</label>
                            <input type="date" class="form-control" name="task_items[${itemCount}][item_start_date]">
                        </div>
                        <div class="task-item-field">
                            <label>Date</label>
                            <input type="date" class="form-control" name="task_items[${itemCount}][item_end_date]">
                        </div>
                        <div class="task-item-field">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" name="task_items[${itemCount}][item_customer_name]">
                        </div>
                        <div class="task-item-field">
                            <label>Address</label>
                            <textarea class="form-control" name="task_items[${itemCount}][item_address]" rows="1"></textarea>
                        </div>
                        <div class="task-item-field">
                            <label>Contact</label>
                            <input type="text" class="form-control" name="task_items[${itemCount}][item_customer_cp]">
                        </div>
                        <div class="task-item-field">
                            <label>Staff</label>
                            <input type="text" class="form-control" name="task_items[${itemCount}][item_staff_name]">
                        </div>
                        <div class="task-item-field">
                            <label>Remarks</label>
                            <textarea class="form-control" name="task_items[${itemCount}][item_remarks]" rows="1"></textarea>
                        </div>
                    </div>
                </div>
            `;
            
            $('#taskItemsContainer').append(taskItemHtml);
        }
        
        // Remove task item button click handler (using event delegation)
        $('#taskItemsContainer').on('click', '.remove-task-item', function(){
            if ($('#taskItemsContainer .task-item').length > 1) {
                $(this).closest('.task-item').remove();
                
                // Re-number the remaining task items
                $('#taskItemsContainer .task-item').each(function(index) {
                    $(this).find('h6').text('Task Item #' + (index + 1));
                    
                    // Update the name attributes of the inputs
                    $(this).find('input, textarea').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const newName = name.replace(/task_items\[\d+\]/, `task_items[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });
            } else {
                alert('At least one task item is required.');
            }
        });
        
        // Save changes button click handler
        $('#saveChanges').click(function(){
            let id = $('#editId').val();
            let serviceType = $('#editServiceType').val();
            let serviceType2 = $('#editServiceType2').val();
            let startDate = $('#editStartDate').val();
            let endDate = $('#editEndDate').val();
            const customerName = $('#editCustomerName').val();
            const address = $('#editAddress').val();
            const customerCp = $('#editCustomerCp').val();
            const staffName = $('#editStaffName').val();
            const remarks = $('#editRemarks').val();
            
            // LOGIC ADDED: Check if Service Type is "Install"
            if(serviceType.trim().toLowerCase() === 'install') {
                // Set Service Type 2 to "Cleaning"
                serviceType2 = 'Cleaning';
                $('#editServiceType2').val(serviceType2);
                
                // Calculate 6 months from start date
                if(startDate) {
                    let dateObj = new Date(startDate);
                    dateObj.setMonth(dateObj.getMonth() + 6);
                    
                    let year = dateObj.getFullYear();
                    let month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    let day = String(dateObj.getDate()).padStart(2, '0');
                    
                    endDate = `${year}-${month}-${day}`;
                    $('#editEndDate').val(endDate);
                }
            }
            
            // Validation
            if(!serviceType) {
                alert('Please enter a service type');
                return;
            }
            
            if(!startDate) {
                alert('Please select a start date');
                return;
            }
            
            if(!endDate) {
                alert('Please select an date');
                return;
            }
            
            if(!customerName) {
                alert('Please enter a customer name');
                return;
            }
            
            if(!address) {
                alert('Please enter an address');
                return;
            }
            
            if(!staffName) {
                alert('Please enter a staff name');
                return;
            }
            
            // Collect task items data
            const taskItems = [];
            $('#taskItemsContainer .task-item').each(function() {
                const item = {};
                $(this).find('input, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name && name.startsWith('task_items[')) {
                        const key = name.match(/task_items\[\d+\]\[(.*)\]/)[1];
                        item[key] = $(this).val();
                    }
                });
                taskItems.push(item);
            });
            
            // Show loading state
            $('#saveChanges').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
            
            // Prepare data for AJAX
            const data = {
                id: id,
                service_type: serviceType,
                service_type_2: serviceType2,
                start_date: startDate,
                end_date: endDate,
                customer_name: customerName,
                address: address,
                customer_cp: customerCp,
                staff_name: staffName,
                remarks: remarks,
                task_items: JSON.stringify(taskItems)
            };
            
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=update_schedule",
                method: "POST",
                data: data,
                dataType: "json",
                error: function(err) {
                    console.log(err);
                    alert('An error occurred');
                    $('#saveChanges').prop('disabled', false).html('Update');
                },
                success: function(resp) {
                    if(typeof resp== 'object' && resp.status == 'success') {
                        $('#editModal').modal('hide');
                        alert('Schedule updated successfully');
                        location.reload();
                    } else {
                        alert('An error occurred');
                        $('#saveChanges').prop('disabled', false).html('Update');
                    }
                }
            });
        });
    })
    
    function delete_schedule($id){
        start_loader();
        $.ajax({
            url:_base_url_+"classes/Master.php?f=delete_schedule",
            method:"POST",
            data:{id: $id},
            dataType:"json",
            error:err=>{
                console.log(err)
                alert_toast("An error occured.",'error');
                end_loader();
            },
            success:function(resp){
                if(typeof resp== 'object' && resp.status == 'success'){
                    location.reload();
                }else{
                    alert_toast("An error occured.",'error');
                    end_loader();
                }
            }
        })
    }
</script>

<?php
// Check if status column exists in schedule_list table, if not add it
if (isset($conn)) {
    $check_column = $conn->query("SHOW COLUMNS FROM schedule_list LIKE 'status'");
    
    // If status column doesn't exist, add it
    if ($check_column->num_rows == 0) {
        $alter_query = "ALTER TABLE schedule_list ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
        $conn->query($alter_query);
    }
    
    // Check if task_items column exists, if not add it
    $check_column = $conn->query("SHOW COLUMNS FROM schedule_list LIKE 'task_items'");
    
    // If task_items column doesn't exist, add it
    if ($check_column->num_rows == 0) {
        $alter_query = "ALTER TABLE schedule_list ADD COLUMN task_items TEXT DEFAULT NULL";
        $conn->query($alter_query);
    }
}
?>
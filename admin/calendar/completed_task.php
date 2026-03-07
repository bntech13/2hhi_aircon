<?php
// Ensure this is at the top of your file if needed
// include 'config.php'; // or your database connection file
?>

<div class="card card-outline card-success">
    <div class="card">
        <div class="card-header" style="background-color: #28a745; border-bottom: 2px solid #28a745; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2); display: flex; justify-content: center; align-items: center;">
            <div style="display: inline-block; border: 2px solid white; border-radius: 10px; padding: 8px 20px; margin-top: 0px; position: relative; z-index: 2; background-color: rgba(255, 255, 255, 0.1);">
                <h6 style="color: white; margin: 0; text-shadow: 0 1px 2px #28a745; text-align: center;">
                 <i class="fas fa-tasks fa-lg" style="color: white;"></i> <b>COMPLETED TASKS</b>
                </h6>
            </div>
        </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-striped completed-tasks-table">
                <colgroup>
                    <col width="3%">
                    <col width="10%">
                    <col width="12%">
                    <col width="12%">
                    <col width="15%">
                    <col width="10%">
                    <col width="10%">
                    <col width="10%">
                    <col width="10%">
                    <col width="8%">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Created</th>
                        <th>Service Type</th>
                        <th>Customer Name</th>
                        <th>Address</th>
                        <th>Customer CP</th>
                        <th>Staff Name</th>
                        <th>Remarks</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    
                    // Check if database connection exists
                    if(!isset($conn) || $conn->connect_error) {
                        echo '<tr><td colspan="10" class="text-center text-danger">Database connection failed</td></tr>';
                    } else {
                        // Check if table exists
                        $tableCheck = $conn->query("SHOW TABLES LIKE 'completed_tasks'");
                        if($tableCheck->num_rows == 0) {
                            echo '<tr><td colspan="10" class="text-center text-danger">Table "completed_tasks" does not exist</td></tr>';
                        } else {
                            $qry = $conn->query("SELECT * FROM completed_tasks ORDER BY date_created DESC");
                            
                            if(!$qry) {
                                echo '<tr><td colspan="10" class="text-center text-danger">Query failed: ' . $conn->error . '</td></tr>';
                            } else {
                                if($qry->num_rows == 0) {
                                    echo '<tr><td colspan="10" class="text-center">No completed tasks found</td></tr>';
                                } else {
                                    while($row = $qry->fetch_assoc()):
                            ?>
                                        <tr>
                                            <td class="text-center"><?php echo $i++; ?></td>
                                            <td class="text-center"><?php echo date("Y-m-d", strtotime($row['date_created'])); ?></td>
                                            <td class="text-center"><?php echo $row['service_type_2']; ?></td>
                                            <td class="text-center"><?php echo $row['customer_name']; ?></td>
                                            <td class="text-center"><?php echo $row['address']; ?></td>
                                            <td class="text-center"><?php echo $row['customer_cp']; ?></td>
                                            <td class="text-center"><?php echo $row['staff_name']; ?></td>
                                            <td class="text-center"><?php echo $row['remarks']; ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-success">Completed</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-flat btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                                    Action
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <div class="dropdown-menu" role="menu">
                                                    <a class="dropdown-item view-btn" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-eye text-primary"></span> View</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item delete_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"><span class="fa fa-trash text-danger"></span> Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                            <?php 
                                    endwhile;
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #28a745; color: white;">
                <h5 class="modal-title" id="taskModalLabel">
                    <i class="fas fa-tasks"></i> Task Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card" style="border: 2px solid #28a745; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <div class="card-header" style="background-color: #28a745; color: white; border-bottom: 2px solid #28a745;">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i>TASK DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Service Type:</strong> <span id="modal_service_type"></span></p>
                                <p><strong>Date Created:</strong> <span id="modal_date_created"></span></p>
                                <p><strong>Customer Name:</strong> <span id="modal_customer_name"></span></p>
                                <p><strong>Address:</strong> <span id="modal_address"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Customer Contact:</strong> <span id="modal_customer_cp"></span></p>
                                <p><strong>Staff Name:</strong> <span id="modal_staff_name"></span></p>
                                <p><strong>Status:</strong> <span id="modal_status"></span></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p><strong>Remarks:</strong> <span id="modal_remarks"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .completed-tasks-table {
        table-layout: auto;
        min-width: 100%;
        border: 3px solid #000;
        border-collapse: collapse;
    }
    .completed-tasks-table th, .completed-tasks-table td {
        padding: 8px;
        text-transform: uppercase;
        vertical-align: middle;
        border: 2px solid #000;
        text-align: center;
        font-weight: bold;
        color: #000;
        font-family: 'Calibri', 'Arial', sans-serif;
    }
    .completed-tasks-table th {
        background-color: #28a745;
        color: #fff;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
        border-color: #000;
    }
    .completed-tasks-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .completed-tasks-table tbody tr {
        transition: background-color 0.3s ease;
    }
    .completed-tasks-table tbody tr:hover {
        background-color: #c3e6cb;
        cursor: pointer;
    }
    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
    }
    .text-center {
        text-align: center;
    }
    .modal-header {
        border-bottom: 2px solid #fff;
    }
    .modal-body .card {
        margin-bottom: 0;
    }
    .modal-body .card-header {
        border-radius: calc(0.25rem - 1px) calc(0.25rem - 1px) 0 0;
    }
    .modal-body .card-body p {
        margin-bottom: 0.5rem;
    }
    .modal-body .card-body strong {
        color: #28a745;
    }
    .modal-body input[readonly], .modal-body textarea[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    .debug-info {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        margin-top: 10px;
        font-family: monospace;
        font-size: 12px;
        color: #6c757d;
    }
</style>

<script>
// Define base URL if not already defined
if (typeof _base_url_ === 'undefined') {
    var _base_url_ = window.location.origin + '/'; // Adjust as needed
    console.log("Base URL not defined, using default:", _base_url_);
}

// Placeholder functions for missing utilities
if (typeof start_loader === 'undefined') {
    function start_loader() {
        console.log('Loader started');
        // You can implement a loading spinner here
        if ($('#loader-overlay').length === 0) {
            $('body').append('<div id="loader-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;"><div class="spinner-border text-light" role="status"><span class="sr-only">Loading...</span></div></div>');
        }
    }
}

if (typeof end_loader === 'undefined') {
    function end_loader() {
        console.log('Loader ended');
        // You can hide the loading spinner here
        $('#loader-overlay').remove();
    }
}

if (typeof _conf === 'undefined') {
    function _conf(message, callback, params) {
        if (confirm(message)) {
            window[callback].apply(null, params);
        }
    }
}

if (typeof alert_toast === 'undefined') {
    function alert_toast(message, type) {
        // Create a toast notification
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
        
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="toast-header ${bgClass} text-white">
                    <strong class="mr-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        $('body').append(toastHtml);
        $(`#${toastId}`).toast('show').on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
}

 $(document).ready(function(){
    $('.completed-tasks-table').dataTable({
        columnDefs: [
            { orderable: false, targets: [9] } // Action column
        ],
        order:[[1,'desc']] // Sort by Date Created (column index 1)
    });
    
    $('.dataTable td,.dataTable th').addClass('py-1 px-2 align-middle');

    // Delete task functionality
    $('.delete_task').click(function(){
        _conf("Are you sure to delete this task permanently?","delete_task",[$(this).attr('data-id')])
    });
    
    // View task functionality
    $('.view-btn').click(function(){
        var id = $(this).attr('data-id');
        console.log("View button clicked for task ID:", id);
        
        start_loader();
        
        // Check if we have the task data already in the table
        var row = $(this).closest('tr');
        var taskData = {
            id: id,
            date_created: row.find('td:eq(1)').text(),
            service_type_2: row.find('td:eq(2)').text(),
            customer_name: row.find('td:eq(3)').text(),
            address: row.find('td:eq(4)').text(),
            customer_cp: row.find('td:eq(5)').text(),
            staff_name: row.find('td:eq(6)').text(),
            remarks: row.find('td:eq(7)').text()
        };
        
        console.log("Task data from table:", taskData);
        
        // Try to fetch detailed data from server
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=get_task_details",
            method: "POST",
            data: {id: id},
            dataType: "json",
            timeout: 10000, // 10 seconds timeout
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    readyState: xhr.readyState,
                    status: xhr.status
                });
                
                // If AJAX fails, use the data from the table
                console.log("Using fallback data from table");
                populateModal(taskData);
                $('#taskModal').modal('show');
                end_loader();
                
                // Show debug info in console
                console.log("Debug Info:", {
                    url: _base_url_ + "classes/Master.php?f=get_task_details",
                    requestData: {id: id},
                    response: xhr.responseText
                });
            },
            success: function(resp) {
                end_loader();
                
                console.log("Server Response:", resp);
                
                // Check if response is valid
                if (!resp) {
                    console.error("Empty response from server");
                    // Use fallback data
                    populateModal(taskData);
                    $('#taskModal').modal('show');
                    return;
                }
                
                if(typeof resp === 'object' && resp.status === 'success') {
                    // Check if data exists
                    if (!resp.data) {
                        console.error("No task data in response");
                        // Use fallback data
                        populateModal(taskData);
                        $('#taskModal').modal('show');
                        return;
                    }
                    
                    // Populate modal with server data
                    populateModal(resp.data);
                    $('#taskModal').modal('show');
                } else {
                    console.error("Server returned error:", resp);
                    // Use fallback data
                    populateModal(taskData);
                    $('#taskModal').modal('show');
                }
            }
        });
    });
});

function populateModal(data) {
    // Populate modal fields with null checks
    $('#modal_date_created').text(data.date_created || '');
    $('#modal_service_type').text(data.service_type_2 || '');
    $('#modal_customer_name').text(data.customer_name || '');
    $('#modal_customer_cp').text(data.customer_cp || '');
    $('#modal_address').text(data.address || '');
    $('#modal_staff_name').text(data.staff_name || '');
    $('#modal_remarks').text(data.remarks || '');
    $('#modal_status').text('Completed');
}

function delete_task($id) {
    start_loader();
    console.log("Deleting task with ID:", $id);
    
    $.ajax({
        url: _base_url_ + "classes/Master.php?f=delete_task",
        method: "POST",
        data: {id: $id},
        dataType: "json",
        timeout: 10000, // 10 seconds timeout
        error: function(xhr, status, error) {
            console.error("Delete Error:", {
                status: status,
                error: error,
                responseText: xhr.responseText,
                readyState: xhr.readyState,
                status: xhr.status
            });
            
            // Try to parse response as JSON in case it's an error message
            try {
                var response = JSON.parse(xhr.responseText);
                alert_toast("Error: " + (response.message || "Unknown error occurred"), 'error');
            } catch (e) {
                alert_toast("Error: " + xhr.statusText + " (Status: " + xhr.status + ")", 'error');
            }
            end_loader();
        },
        success: function(resp) {
            end_loader();
            console.log("Delete Response:", resp);
            
            if (!resp) {
                alert_toast("Empty response from server.", 'error');
                return;
            }
            
            if(typeof resp === 'object' && resp.status === 'success') {
                alert_toast("Task deleted successfully.", 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                var errorMsg = "Failed to delete task";
                if (resp && resp.message) {
                    errorMsg += ": " + resp.message;
                } else if (resp) {
                    errorMsg += ": " + JSON.stringify(resp);
                }
                alert_toast(errorMsg, 'error');
            }
        }
    });
}
</script>
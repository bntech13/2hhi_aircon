<?php
// Fix the path to config.php using __DIR__ for proper relative path
require_once(__DIR__ . '/../../config.php');
// Fetch all items from the database
$items = $conn->query("SELECT * FROM `item_list` ORDER BY `brand` ASC");
?>
  
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header bg-gradient-primary">
        <div style="display: inline-block; background: rgba(255,255,255,0.2); border-radius: 10px; padding: 8px 20px; margin-top: 0px; backdrop-filter: blur(5px);">
            <h6 style="color: white; margin: 0;">
               <i class="fas fa-clipboard-list fa-lg"></i>  <b>ITEM DETAILS</b>
            </h6>
        </div>  
        <div class="card-tools">
            <a href="javascript:void(0)" id="create_new" class="btn btn-light btn-flat rounded-pill shadow-sm">
                <span class="fas fa-plus-circle"></span>  Add Details
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <table class="table table-bordered table-hover table-striped" id="itemsTable">
                <colgroup>
                    <col width="25%">
                    <col width="25%">
                    <col width="30%">
                    <col width="25%">
                    <col width="20%">
                </colgroup>
                <thead>
                    <tr align="center" class="bg-gradient-primary">
                        <th><i class="fas fa-tag mr-1"></i> Brand Name</th>
                        <th><i class="fas fa-cogs mr-1"></i> Type</th>
                        <th><i class="fas fa-tachometer-alt mr-1"></i> Horse Power</th>
                        <th><i class="fas fa-credit-card mr-1"></i> Payment Terms</th>
                        <th><i class="fas fa-cog" style="color: white;"></i> Settings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    if($items && $items->num_rows > 0):
                        while($row = $items->fetch_assoc()): 
                    ?>
                    <tr style="background-color: white;">
                        <td><?php echo htmlspecialchars($row['brand']) ?></td>
                        <td><?php echo htmlspecialchars($row['type']) ?></td>
                        <td><?php echo htmlspecialchars($row['hp']) ?></td>
                        <td><?php echo htmlspecialchars($row['payment_terms']) ?></td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                    <i class="fas fa-bars"></i> Actions
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item edit_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                        <span class="fa fa-edit text-primary"></span> <span class="ml-1">Edit Item</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item delete_data" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">
                                        <span class="fa fa-trash text-danger"></span> <span class="ml-1">Delete Item</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr style="background-color: white;">
                        <td colspan="5" class="text-center">
                            <div class="py-3">
                                <i class="fas fa-inbox fa-3x text-muted"></i>
                                <div class="mt-2 text-muted">No Data Found</div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>
    .table tbody tr {
        background-color: white !important;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    .bg-gradient-primary {
        background: linear-gradient(45deg, #0066cc, #0052a3) !important;
    }
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    .dropdown-item i {
        width: 20px;
        text-align: center;
    }
    .btn-group .btn {
        border-radius: 0.25rem !important;
    }
    /* Hide DataTables controls */
    .dataTables_filter, 
    .dataTables_length, 
    .dataTables_info, 
    .dataTables_paginate {
        display: none !important;
    }
</style>
<script>
    $(document).ready(function(){
        $('.delete_data').click(function(){
            _conf("Are you sure to delete this Item permanently?","delete_item",[$(this).attr('data-id')])
        })
        $('#create_new').click(function(){
            uni_modal("<i class='fa fa-plus'></i> Add New Details","maintenance/manage_item.php","mid-large")
        })
        $('.edit_data').click(function(){
            uni_modal("<i class='fa fa-edit'></i> Edit Item Details","maintenance/manage_item.php?id="+$(this).attr('data-id'),"mid-large")
        })
        $('.view_data').click(function(){
            uni_modal("<i class='fa fa-box'></i> Item Details","maintenance/view_item.php?id="+$(this).attr('data-id'),"")
        })
        $('.table td,.table th').addClass('py-1 px-2 align-middle')
        
        // Initialize DataTable with minimal controls
        $('#itemsTable').dataTable({
            "dom": 't',  // Only show the table
            "paging": false,  // Disable pagination
            "info": false  // Hide table information
        });
    })
    function delete_item($id){
        start_loader();
        $.ajax({
            url:_base_url_+"classes/Master.php?f=delete_item",
            method:"POST",
            data:{id: $id},
            dataType:"json",
            error:err=>{
                console.log(err)
                alert_toast("An error occurred.",'error');
                end_loader();
            },
            success:function(resp){
                if(typeof resp== 'object' && resp.status == 'success'){
                    location.reload();
                }else{
                    alert_toast("An error occurred.",'error');
                    end_loader();
                }
            }
        })
    }
</script>
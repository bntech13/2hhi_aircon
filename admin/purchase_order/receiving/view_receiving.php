<?php 
$qry = $conn->query("SELECT * FROM receiving_list where id = '{$_GET['id']}'");
if ($qry->num_rows > 0) {
    foreach ($qry->fetch_array() as $k => $v) {
        $$k = $v;
    }
    if ($from_order == 1) {
        $po_qry = $conn->query("SELECT p.*, s.name as supplier FROM `purchase_order_list` p inner join `supplier_list` s on p.supplier_id = s.id where p.id = '{$form_id}'");
        if ($po_qry->num_rows > 0) {
            foreach ($po_qry->fetch_array() as $k => $v) {
                if (!isset($$k))
                    $$k = $v;
            }
        }
    } else {
        // For direct receiving (not from purchase order), just get supplier info if available
        if (!empty($supplier_id)) {
            $supplier_qry = $conn->query("SELECT name as supplier FROM `supplier_list` where id = '{$supplier_id}'");
            if ($supplier_qry->num_rows > 0) {
                $supplier_data = $supplier_qry->fetch_assoc();
                $supplier = $supplier_data['supplier'];
            }
        }
    }
} else {
    // Handle the case where the query does not return any rows
    echo "No receiving list found with the provided ID.";
    exit;
}
// Ensure stock_ids is set and not null
if (!isset($stock_ids)) {
    $stock_ids = '';
}
// Check if stock_ids is not empty before querying
if (!empty($stock_ids)) {
    $total = 0;
    // Fixed query - check if item_list table exists and has the required columns
    $check_table = $conn->query("SHOW TABLES LIKE 'item_list'");
    if ($check_table->num_rows > 0) {
        // Check if columns exist
        $check_columns = $conn->query("SHOW COLUMNS FROM item_list LIKE 'name'");
        if ($check_columns->num_rows > 0) {
            // Table and columns exist, use the original query
            $qry = $conn->query("SELECT s.*, i.name, i.description FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
        } else {
            // Table exists but columns don't, use a simpler query
            $qry = $conn->query("SELECT s.* FROM `stock_list` s where s.id in ({$stock_ids})");
        }
    } else {
        // Table doesn't exist, use a simpler query
        $qry = $conn->query("SELECT s.* FROM `stock_list` s where s.id in ({$stock_ids})");
    }
    
    while ($row = $qry->fetch_assoc()) {
        $total += $row['total'];
    ?>
    <tr>
        <td class="py-1 px-2 text-center"><?php echo number_format($row['quantity'], 2) ?></td>
        <td class="py-1 px-2 text-center"><?php echo $row['unit'] ?></td>
        <td class="py-1 px-2">
            <?php echo isset($row['name']) ? $row['name'] : 'Item #' . $row['item_id'] ?><br>
            <?php echo isset($row['description']) ? $row['description'] : '' ?>
        </td>
        <td class="py-1 px-2 text-right"><?php echo number_format($row['price']) ?></td>
        <td class="py-1 px-2 text-right"><?php echo number_format($row['total']) ?></td>
    </tr>
    <?php
    }
    // REMOVED THE TFOOT SECTION HERE
} else { ?>
    <tfoot>
        <tr>
            <th class="text-right py-1 px-2" colspan="4">No items to display</th>
        </tr>
    </tfoot>
    <?php } ?>
</div>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h4 class="card-title">Received Order Details - <?php echo isset($po_code) ? $po_code : (isset($form_id) ? 'Direct Receiving #' . $form_id : 'N/A') ?></h4>
    </div>
    <div class="card-body" id="print_out">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label text-info">FROM P.O. Code</label>
                    <div><?php echo isset($po_code) ? $po_code : ($from_order == 1 ? 'N/A' : 'Direct Receiving') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="supplier_id" class="control-label text-info">Supplier</label>
                        <div><?php echo isset($supplier) ? $supplier : 'N/A' ?></div>
                    </div>
                </div>
            </div>
            <h4 class="text-info">Orders</h4>
            <table class="table table-striped table-bordered" id="list">
                <colgroup>
                    <col width="10%">
                    <col width="10%">
                    <col width="30%">
                    <col width="25%">
                    <col width="25%">
                </colgroup>
                <thead>
                    <tr class="text-light bg-navy">
                        <th class="text-center py-1 px-2">Qty</th>
                        <th class="text-center py-1 px-2">Unit</th>
                        <th class="text-center py-1 px-2">Item</th>
                        <th class="text-center py-1 px-2">Cost</th>
                        <th class="text-center py-1 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ensure stock_ids is set and not null
                    if (!isset($stock_ids)) {
                        $stock_ids = '';
                    }
                    // Check if stock_ids is not empty before querying
                    if (!empty($stock_ids)) {
                        $total = 0;
                        // Use the same fixed query as above
                        $check_table = $conn->query("SHOW TABLES LIKE 'item_list'");
                        if ($check_table->num_rows > 0) {
                            // Check if columns exist
                            $check_columns = $conn->query("SHOW COLUMNS FROM item_list LIKE 'name'");
                            if ($check_columns->num_rows > 0) {
                                // Table and columns exist, use the original query
                                $qry = $conn->query("SELECT s.*, i.name, i.description FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
                            } else {
                                // Table exists but columns don't, use a simpler query
                                $qry = $conn->query("SELECT s.* FROM `stock_list` s where s.id in ({$stock_ids})");
                            }
                        } else {
                            // Table doesn't exist, use a simpler query
                            $qry = $conn->query("SELECT s.* FROM `stock_list` s where s.id in ({$stock_ids})");
                        }
                        
                        while ($row = $qry->fetch_assoc()) {
                            $total += $row['total'];
                    ?>
                        <tr>
                            <td class="py-1 px-2 text-center"><?php echo number_format($row['quantity'], 2) ?></td>
                            <td class="py-1 px-2 text-center"><?php echo $row['unit'] ?></td>
                            <td class="py-1 px-2">
                                <?php echo isset($row['name']) ? $row['name'] : 'Item #' . $row['item_id'] ?><br>
                                <?php echo isset($row['description']) ? $row['description'] : '' ?>
                            </td>
                            <td class="py-1 px-2 text-right"><?php echo number_format($row['price']) ?></td>
                            <td class="py-1 px-2 text-right"><?php echo number_format($row['total']) ?></td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center">No items found</td></tr>';
                    }
                    ?>
                </tbody>
                <!-- REMOVED THE ENTIRE TFOOT SECTION HERE -->
            </table>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="remarks" class="control-label text-info">Remarks</label>
                        <p><?php echo isset($remarks) ? $remarks : '' ?></p>
                    </div>
                </div>
                <?php if (isset($status) && $status > 0): ?>
                <div class="col-md-6">
                    <span class="text-info"><?php echo ($status == 2) ? "RECEIVED" : "PARTIALLY RECEIVED" ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-success" type="button" id="print">Print</button>
        <a class="btn btn-flat btn-primary" href="<?php echo base_url.'/admin?page=receiving/manage_receiving&id='.(isset($id) ? $id : '') ?>">Edit</a>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin?page=receiving' ?>">Back To List</a>
    </div>
</div>
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center qty">
            <span class="visible"></span>
            <input type="hidden" name="item_id[]">
            <input type="hidden" name="unit[]">
            <input type="hidden" name="qty[]">
            <input type="hidden" name="price[]">
            <input type="hidden" name="total[]">
        </td>
        <td class="py-1 px-2 text-center unit">
        </td>
        <td class="py-1 px-2 item">
        </td>
        <td class="py-1 px-2 text-right cost">
        </td>
        <td class="py-1 px-2 text-right total">
        </td>
    </tr>
</table>
<script>
    $(function(){
        $('#print').click(function(){
            start_loader()
            var _el = $('<div>')
            var _head = $('head').clone()
                _head.find('title').text("Received Order Details - Print View")
            var p = $('#print_out').clone()
            p.find('tr.text-light').removeClass("text-light bg-navy")
            _el.append(_head)
            _el.append('<div class="d-flex justify-content-center">'+
                      '<div class="col-1 text-right">'+
                      '<img src="<?php echo validate_image($_settings->info('logo')) ?>" width="65px" height="65px" />'+
                      '</div>'+
                      '<div class="col-10">'+
                      '<h4 class="text-center"><?php echo $_settings->info('name') ?></h4>'+
                      '<h4 class="text-center">Received Order</h4>'+
                      '</div>'+
                      '<div class="col-1 text-right">'+
                      '</div>'+
                      '</div><hr/>')
            _el.append(p.html())
            var nw = window.open("","","width=1200,height=900,left=250,location=no,titlebar=yes")
                     nw.document.write(_el.html())
                     nw.document.close()
                     setTimeout(() => {
                         nw.print()
                         setTimeout(() => {
                            nw.close()
                            end_loader()
                         }, 200);
                     }, 500);
        })
    })
</script>
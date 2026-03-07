<?php
require_once('../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `item_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
}
?>
<div class="container-fluid">
    <form action="" id="item-form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="form-group">
            <label for="brand" class="control-label">Aircon Brand Name</label>
            <input type="text" name="brand" id="brand" class="form-control rounded-0"
                   value="<?php echo isset($brand) ? $brand : ''; ?>">
        </div>
        <div class="form-group">
            <label for="type" class="control-label">Type</label>
            <select name="type" id="type" class="form-control rounded-0">
                <option value="">Select Type</option>
                <option value="Window" <?php echo isset($type) && $type == 'Window' ? 'selected' : '' ?>>Window</option>
                <option value="Floor" <?php echo isset($type) && $type == 'Floor' ? 'selected' : '' ?>>Floor</option>
                <option value="Split" <?php echo isset($type) && $type == 'Split' ? 'selected' : '' ?>>Split</option>
            </select>
        </div>
        <div class="form-group">
            <label for="hp" class="control-label">Horse Power</label>
            <select name="hp" id="hp" class="form-control rounded-0">
                <option value="">Select HP</option>
                <option value="1.5 HP" <?php echo isset($hp) && $hp == '1.5 HP' ? 'selected' : '' ?>>1.5 HP</option>
                <option value="2 HP" <?php echo isset($hp) && $hp == '2 HP' ? 'selected' : '' ?>>2 HP</option>
                <option value="2.5 HP" <?php echo isset($hp) && $hp == '2.5 HP' ? 'selected' : '' ?>>2.5 HP</option>
            </select>
        </div>
        <div class="form-group">
            <label for="payment_terms" class="control-label">Payment Terms</label>
            <select name="payment_terms" id="payment_terms" class="form-control rounded-0">
                <option value="">Select Payment Terms</option>
                <option value="Cash" <?php echo isset($payment_terms) && $payment_terms == 'Cash' ? 'selected' : '' ?>>Cash</option>
                <option value="30 Days" <?php echo isset($payment_terms) && $payment_terms == '30 Days' ? 'selected' : '' ?>>30 Days</option>
                <option value="60 Days" <?php echo isset($payment_terms) && $payment_terms == '60 Days' ? 'selected' : '' ?>>60 Days</option>
                <option value="90 Days" <?php echo isset($payment_terms) && $payment_terms == '90 Days' ? 'selected' : '' ?>>90 Days</option>
            </select>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
    if($('.select2').length > 0) {
        $('.select2').select2({placeholder:"Please Select here",width:"relative"});
    }
    
    if(typeof _base_url_ === 'undefined') {
        console.error("_base_url_ is not defined. Please define it in your main JavaScript file.");
        var path = window.location.pathname;
        var base_url = window.location.origin + path.substring(0, path.indexOf('/admin/'));
        console.log("Using auto-detected base URL:", base_url);
        window._base_url_ = base_url;
    }
    
    $('#item-form').submit(function(e){
        e.preventDefault();
        var _this = $(this);
        $('.err-msg').remove();
        start_loader();
        var formData = new FormData(_this[0]);
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=save_item",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            dataType: 'json',
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                alert_toast("An error occurred: " + error, 'error');
                end_loader();
            },
            success: function(resp) {
                if(typeof resp === 'object' && resp.status === 'success'){
                    location.reload();
                } else if(resp.status === 'failed' && resp.msg){
                    var el = $('<div>')
                        .addClass("alert alert-danger err-msg")
                        .text(resp.msg);
                    _this.prepend(el);
                    el.show('slow');
                    end_loader();
                } else {
                    alert_toast("An unexpected error occurred", 'error');
                    console.log("Unexpected response:", resp);
                    end_loader();
                }
            }
        });
    });
});
</script>
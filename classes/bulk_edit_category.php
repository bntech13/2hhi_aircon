<?php
require_once('../../config.php');

$category = isset($_GET['category']) ? $_GET['category'] : '';
$values = isset($_GET['values']) ? json_decode($_GET['values']) : [];

// Validate category
$allowed_categories = ['brand', 'type', 'hp', 'payment_terms'];
if (!in_array($category, $allowed_categories)) {
    echo "<div class='alert alert-danger'>Invalid category</div>";
    exit;
}

// Get proper display name for category
$category_display = '';
switch($category) {
    case 'brand':
        $category_display = 'Brand Name';
        break;
    case 'type':
        $category_display = 'Type';
        break;
    case 'hp':
        $category_display = 'Horse Power';
        break;
    case 'payment_terms':
        $category_display = 'Payment Terms';
        break;
}
?>

<div class="container-fluid">
    <form id="bulk-edit-form">
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
        
        <div class="form-group">
            <label class="control-label">Edit <?php echo htmlspecialchars($category_display); ?> Values:</label>
            <div class="border rounded p-2" style="max-height: 400px; overflow-y: auto;">
                <?php if (!empty($values)): ?>
                    <?php foreach ($values as $index => $value): ?>
                        <div class="form-group row mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="old_values[]" value="<?php echo htmlspecialchars($value); ?>" readonly>
                            </div>
                            <div class="col-md-1 text-center d-flex align-items-center justify-content-center">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="new_values[]" value="<?php echo htmlspecialchars($value); ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">No <?php echo htmlspecialchars($category_display); ?> values found</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-group text-right">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update All</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function(){
    $('#bulk-edit-form').on('submit', function(e){
        e.preventDefault();
        
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=bulk_update_category",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            error: function(err){
                console.log(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if(typeof resp === 'object' && resp.status === 'success'){
                    alert_toast("All values updated successfully.", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("An error occurred: " + (resp.msg || 'Unknown error'), 'error');
                    end_loader();
                }
            }
        });
    });
});
</script>
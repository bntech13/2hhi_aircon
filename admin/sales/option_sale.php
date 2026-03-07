<?php 
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM sales_list where id = '{$_GET['id']}'");
    if($qry->num_rows >0){
        foreach($qry->fetch_array() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<style>
    select[readonly].select2-hidden-accessible + .select2-container {
        pointer-events: none;
        touch-action: none;
        background: #eee;
        box-shadow: none;
    }
    select[readonly].select2-hidden-accessible + .select2-container .select2-selection {
        background: #eee;
        box-shadow: none;
    }
    
    /* Enhanced form control styling */
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        background-color: #fff;
    }
    
    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    /* Rounded corners for form controls */
    .rounded-0 {
        border-radius: 0.25rem !important;
    }
    
    /* Custom select styling */
    .custom-select {
        display: inline-block;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        vertical-align: middle;
        background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e") no-repeat right 0.75rem center/8px 10px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    .custom-select:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .custom-select-sm {
        height: calc(1.5em + 0.5rem + 2px);
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
        padding-left: 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Textarea styling */
    textarea.form-control {
        min-height: 100px;
        padding: 0.375rem 0.75rem;
    }
    
    /* Button styling */
    .btn-flat {
        border-radius: 0.25rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
        border: 1px solid transparent;
    }
    
    .btn-flat:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-flat:active {
        transform: translateY(0);
        box-shadow: none;
    }
    
    /* Table styling */
    .table td, .table th {
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    .table thead th {
        background-color: #021324ff;
        color: white;
        font-weight: 500;
        border-bottom: 2px solid #0056b3;
    }
    
    /* Input group styling */
    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
    }
    
    .input-group > .form-control {
        position: relative;
        flex: 1 1 auto;
        width: 1%;
        min-width: 0;
        margin-bottom: 0;
    }
</style>
<div style="display: inline-block; border: 1px solid #0066cc; border-radius: 10px; padding: 5px 15px; margin-top: 0px;">
    <h6 style="color: #0066cc; margin: 0;">
        <i class="fas fa-plus-circle"></i> <b>ADD SALE</b>
    </h6>
</div>    
    <div class="card-body">
        <form action="" id="sale-form">
            <input type="hidden" name="id" value="<?php echo isset($id) ?$id : ''?>">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sales_order" class="control-label text-info">Sales Order</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                </div>
                                <input type="text" name="sales_order"  class="form-control form-control-sm" value="<?php echo isset($sales_order)?$sales_order : ''?>" placeholder="Enter sales order number">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="client" class="control-label text-info">Client Name</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="client" class="form-control form-control-sm" value="<?php echo isset($client) ? $client : '' ?>" placeholder="Enter client name">
                            </div>
                        </div>
                    </div>
                </div>
                  
                </fieldset>
                <hr>
                <table class="table table-striped table-bordered" id="list">
                    <colgroup>
                        <col width="5%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="25%">
                        <col width="20%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr class="text-light bg-navy">
                            <th class="text-center py-1 px-2"></th>
                            <th class="text-center py-1 px-2">Aircon Code</th>
                            <th class="text-center py-1 px-2">Qty</th>
                            <th class="text-center py-1 px-2">Unit</th>
                            <th class="text-center py-1 px-2">Item</th>
                            <th class="text-center py-1 px-2">Price</th>
                            <th class="text-center py-1 px-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        if(isset($id)):
                        $qry = $conn->query("SELECT s.*,i.brand,i.type,i.hp FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
                        while($row = $qry->fetch_assoc()):
                            $total += $row['total']
                        ?>
                        <tr>
                            <td class="py-1 px-2 text-center">
                                <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
                            </td>
                            <td class="py-1 px-2 text-center aircon-code">
                                <input type="hidden" name="aircon_code[]" value="<?php echo isset($row['aircon_code']) ? $row['aircon_code'] : ''; ?>">
                                <?php echo isset($row['aircon_code']) ? $row['aircon_code'] : ''; ?>
                            </td>
                            <td class="py-1 px-2 text-center qty">
                                <span class="visible"><?php echo number_format($row['quantity']); ?></span>
                                <input type="hidden" name="item_id[]" value="<?php echo $row['item_id']; ?>">
                                <input type="hidden" name="unit[]" value="<?php echo $row['unit']; ?>">
                                <input type="hidden" name="qty[]" value="<?php echo $row['quantity']; ?>">
                                <input type="hidden" name="price[]" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="total[]" value="<?php echo $row['total']; ?>">
                            </td>
                            <td class="py-1 px-2 text-center unit">
                            <?php echo $row['unit']; ?>
                            </td>
                            <td class="py-1 px-2 item">
                            <?php echo $row['brand'] . ' - ' . $row['type'] . ' - ' . $row['hp']; ?>
                            </td>
                            <td class="py-1 px-2 text-right cost">
                            <?php echo number_format($row['price']); ?>
                            </td>
                            <td class="py-1 px-2 text-right total">
                            <?php echo number_format($row['total']); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="6">Sub Total</th>
                            <th class="text-right py-1 px-2 sub-total"><?php echo number_format($total, 2); ?></th>
                        </tr>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="6">Discount <input style="width:40px !important" name="discount_perc" class='' type="number" min="0" max="100" value="<?php echo isset($discount_perc) ? $discount_perc : 0 ?>">%
                                <input type="hidden" name="discount" value="<?php echo isset($discount) ? $discount : 0 ?>">
                            </th>
                            <th class="text-right py-1 px-2 discount"><?php echo isset($discount) ? number_format($discount, 2) : 0 ?></th>
                        </tr>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="6">Tax <input style="width:40px !important" name="tax_perc" class='' type="number" min="0" max="100" value="<?php echo isset($tax_perc) ? $tax_perc : 0 ?>">%
                                <input type="hidden" name="tax" value="<?php echo isset($tax) ? $tax : 0 ?>">
                            </th>
                            <th class="text-right py-1 px-2 tax"><?php echo isset($tax) ? number_format($tax, 2) : 0 ?></th>
                        </tr>
                        <tr>
                            <th class="text-right py-1 px-2" colspan="6">Total
                                <input type="hidden" name="amount" value="<?php echo isset($amount) ? $amount : 0 ?>">
                            </th>
                            <th class="text-right py-1 px-2 grand-total"><?php echo isset($amount) ? number_format($amount, 2) : 0 ?></th>
                        </tr>
                </tfoot>
                </table>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remarks" class="control-label text-info">Remarks</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                                </div>
                                <textarea name="remarks" id="remarks" rows="3" class="form-control" placeholder="Enter any remarks or notes..."><?php echo isset($remarks) ? $remarks : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer py-1 text-center">
        <button class="btn btn-flat btn-primary" type="submit" form="sale-form">
            <i class="fas fa-save"></i> Save
        </button>
        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin/?page=sales' ?>">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</div>
<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center aircon-code">
            <input type="hidden" name="aircon_code[]">
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
    var items = $.parseJSON('<?php echo json_encode($item_arr) ?>')
    
    $(function(){
        $('.select2').select2({
            placeholder:"Please select here",
            width:'resolve',
        })
        $('#add_to_list').click(function(){
            var aircon_code = $('#aircon_code').val()
            var brand = $('#brand').val()
            var indoor_outdoor = $('#indoor_outdoor').val()
            var type = $('#type').val()
            var unit = $('#unit').val()
            var qty = $('#qty').val() > 0 ? $('#qty').val() : 0;
            var price = $('#price').val() > 0 ? $('#price').val() : 0;
            var total = parseFloat(qty) * parseFloat(price)
            var item_name = brand + ' (' + indoor_outdoor + '), ' + type + ' Type, ' + unit;
            var tr = $('#clone_list tr').clone()
            if(aircon_code == '' || brand == '' || indoor_outdoor == '' || type == '' || unit == '' || qty == '' || price == ''){
                alert_toast('Form Item textfields are required.','warning');
                return false;
            }
            if($('table#list tbody').find('tr[data-id="'+aircon_code+'"]').length > 0){
                alert_toast('Item with this aircon code already exists on the list.','error');
                return false;
            }
            tr.find('[name="aircon_code[]"]').val(aircon_code)
            tr.find('[name="item_id[]"]').val(0) // Set to 0 since we're not using item_id directly
            tr.find('[name="unit[]"]').val(unit)
            tr.find('[name="qty[]"]').val(qty)
            tr.find('[name="price[]"]').val(price)
            tr.find('[name="total[]"]').val(total)
            tr.attr('data-id',aircon_code)
            tr.find('.aircon-code').text(aircon_code)
            tr.find('.qty .visible').text(qty)
            tr.find('.unit').text(unit)
            tr.find('.item').html(item_name)
            tr.find('.cost').text(parseFloat(price).toLocaleString('en-US'))
            tr.find('.total').text(parseFloat(total).toLocaleString('en-US'))
            $('table#list tbody').append(tr)
            calc()
            $('#aircon_code').val('')
            $('#brand').val('').trigger('change')
            $('#indoor_outdoor').val('').trigger('change')
            $('#type').val('').trigger('change')
            $('#unit').val('').trigger('change')
            $('#qty').val('')
            $('#price').val('')
            tr.find('.rem_row').click(function(){
                rem($(this))
            })
            
            $('[name="discount_perc"],[name="tax_perc"]').on('input',function(){
                calc()
            })
        })
        $('#sale-form').submit(function(e){
            e.preventDefault();
            var _this = $(this)
             $('.err-msg').remove();
            start_loader();
            $.ajax({
                url:_base_url_+"classes/Master.php?f=save_sale",
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error:err=>{
                    console.log(err)
                    alert_toast("An error occured",'error');
                    end_loader();
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        location.replace(_base_url_+"admin/?page=sales/view_sale&id="+resp.id);
                    }else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            end_loader()
                    }else{
                        alert_toast("An error occured",'error');
                        end_loader();
                        console.log(resp)
                    }
                    $('html,body').animate({scrollTop:0},'fast')
                }
            })
        })
        if('<?php echo isset($id) && $id > 0 ?>' == 1){
            calc()
            $('#supplier_id').trigger('change')
            $('#supplier_id').attr('readonly','readonly')
            $('table#list tbody tr .rem_row').click(function(){
                rem($(this))
            })
        }
    })
    function rem(_this){
        _this.closest('tr').remove()
        calc()
        if($('table#list tbody tr').length <= 0)
            $('#supplier_id').removeAttr('readonly')
    }
    function calc(){
        var sub_total = 0;
        var grand_total = 0;
        var discount = 0;
        var tax = 0;
        $('table#list tbody input[name="total[]"]').each(function(){
            sub_total += parseFloat($(this).val())
            
        })
        $('table#list tfoot .sub-total').text(parseFloat(sub_total).toLocaleString('en-US',{style:'decimal',maximumFractionDigit:2}))
        var discount =   sub_total * (parseFloat($('[name="discount_perc"]').val()) /100)
        sub_total = sub_total - discount;
        var tax =   sub_total * (parseFloat($('[name="tax_perc"]').val()) /100)
        grand_total = sub_total + tax
        $('.discount').text(parseFloat(discount).toLocaleString('en-US',{style:'decimal',maximumFractionDigit:2}))
        $('[name="discount"]').val(parseFloat(discount))
        $('.tax').text(parseFloat(tax).toLocaleString('en-US',{style:'decimal',maximumFractionDigit:2}))
        $('[name="tax"]').val(parseFloat(tax))
        $('table#list tfoot .grand-total').text(parseFloat(grand_total).toLocaleString('en-US',{style:'decimal',maximumFractionDigit:2}))
        $('[name="amount"]').val(parseFloat(grand_total))
    }
</script>
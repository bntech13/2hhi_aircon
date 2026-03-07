<?php 
// Database connection check assumed here. 
// Removed schedule_data query as it was only for the calendar widget.

if(isset($_GET['id'])){
    $qry = $conn->query("SELECT r.*,s.name as supplier FROM return_list r inner join supplier_list s on r.supplier_id = s.id  where r.id = '{$_GET['id']}'");
    if($qry && $qry->num_rows > 0){
        foreach($qry->fetch_array() as $k => $v){
            $$k = $v;
        }
    }
}

 $item_arr = isset($item_arr) ? $item_arr : [];
 $cost_arr = isset($cost_arr) ? $cost_arr : [];
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
    
    body {
        background: linear-gradient(to bottom, #1a2980, #f0f3f3);
        background-attachment: fixed;
        font-family: 'Arial', sans-serif;
    }
    
    .card.card-outline.card-primary::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: 
            radial-gradient(circle, white 1px, transparent 1px),
            radial-gradient(circle, white 1px, transparent 1px);
        background-size: 50px 50px;
        background-position: 0 0, 25px 25px;
        opacity: 0.2;
        z-index: 0;
        pointer-events: none;
    }
    
    .card-header {
        background-color: #023b79ff;
        border-bottom: 1px solid #007bff;
        position: relative;
        z-index: 1;
    }
    
    .card-title {
        color: white;
    }
    
    .card-body {
        background-color: #ffffff00;
        color: white;
        position: relative;
        z-index: 1;
    }
    
    /* Wrapper for Side-by-Side Layout */
    .content-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-start;
    }
    
    /* Left Side Form - Fixed Width */
    .form-container {
        width: 400px; 
        min-width: 100px;
        left: -90px;
        flex-shrink: 0;
        padding: 15px;
        border: 1px solid #023b79ff;
        border-radius: 5px;
        background-color: #023b79ff; 
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 1;
        box-sizing: border-box;
    }
    
    /* Right Side Table Area - Fills remaining space */
    .table-container {
        flex-grow: 1;
        min-width: 0; 
        display: flex;
        flex-direction: column;
    }
    
    /* Single column layout for the form */
    .schedule-form {
        display: grid;
        grid-template-columns: 1fr; 
        grid-auto-rows: auto;
        gap: 8px; 
        margin-top: 8px;
    }
    
    .form-group {
        margin-bottom: 0px;
    }
    
    .form-control, .select2-selection, textarea {
        background-color: rgba(255, 255, 255, 0.9) !important;
        color: #333 !important;
        border: 1px solid #007bff !important;
        border-radius: 4px !important;
        padding: 0.375rem 0.5rem !important;
        font-size: 0.85rem !important;
        height: auto !important;
    }
    
    .form-control:focus, .select2-selection:focus, textarea:focus {
        border-color: #0056b3 !important;
        box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25) !important;
    }
    
    .select2-container--default .select2-selection--single {
        background-color: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid #007bff !important;
        border-radius: 4px !important;
        height: auto !important;
        padding: 0.1rem 0.5rem !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #333 !important;
        line-height: 2 !important;
        padding-left: 0 !important;
        font-size: 0.85rem !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px !important;
    }
    
    .select2-dropdown {
        background-color: rgba(255, 255, 255, 0.95) !important;
        color: #333 !important;
        border: 1px solid #007bff !important;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: white !important;
    }
    
    label.control-label {
        color: white !important;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        font-size: 0.8rem;
        margin-bottom: 2px;
    }
    
    .add-to-list-container {
        margin-top: 15px;
        text-align: center;
        position: relative;
    }
    
    #list {
        color: #333; 
        width: 200%;
        /* CRITICAL: Allows the 'left' property to work */
        position: relative; 
        left: -100px;
    }

    /* CRITICAL: Aligns the Title Header with the table */
    .table-container > h4.text-info {
        margin-left: -100px;
        /* Ensures the title doesn't wrap awkwardly if space is tight */
        white-space: nowrap; 
    }
    
    /* MODIFIED: Minimized height and padding for table header */
    #list thead th {
        background-color: rgba(0, 123, 255, 0.3);
        color: white;
        border-color: #007bff;
        padding: .5rem 0.25rem !important; 
        border-width: 1px;
        font-size: 0.8rem;
        height: auto; 
    }
    
    #list tbody td {
        background-color: white;
        color: #333; 
        border-color: #007bff;
        font-size: 0.85rem;
    }
    
    #list tbody tr:nth-of-type(odd) td {
        background-color: white;
    }
    
    @keyframes fall {
        from {transform: translateY(-100px);}
        to {transform: translateY(100vh);}
    }
    
    @keyframes sway {
        0%, 100% {transform: translateX(0);}
        50% {transform: translateX(20px);}
    }
    
    .snowflake {
        position: fixed;
        top: -10px;
        z-index: 9999;
        user-select: none;
        cursor: default;
        animation: fall linear, sway ease-in-out infinite;
        color: rgba(255, 255, 255, 0.8);
        font-size: 1em;
    }
    
    .centered-toast {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background-color: #28a745;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        display: none;
        max-width: 80%;
    }
    
    .centered-toast.show {
        display: block;
        animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        animation-fill-mode: forwards;
    }
    
    @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
    @keyframes fadeOut { from {opacity: 1;} to {opacity: 0;} }
    
    .task-added-message {
        position: absolute;
        top: 100%; 
        left: 50%;
        transform: translateX(-50%); 
        background-color: #b95c06ff; 
        color: white; 
        font-weight: bold;
        padding: 15px 30px; 
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        font-size: 18px; 
        text-align: center;
        display: none;
        z-index: 100;
        margin-top: 10px; 
        white-space: nowrap; 
        animation: popup 0.5s ease-out;
    }
    
    @keyframes popup {
        0% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
        100% { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
    
    #customer_cp {
        font-family: monospace;
        letter-spacing: 1px;
    }
    
    h4.text-info {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    /* Responsive adjustment for mobile */
    @media (max-width: 900px) {
        .content-wrapper {
            flex-direction: column;
        }
        .form-container {
            width: 100%;
            left: 0;
        }
        .table-container {
            min-width: 100%;
        }
        /* Reset shifts on mobile */
        .table-container > h4.text-info {
            margin-left: 0;
        }
        #list {
            width: 100%;
            left: 0;
        }
    }
</style>
<div class="card-body">
    <form action="" id="manage_task-form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="container-fluid">
            <hr>
            
            <!-- Wrapper for Side-by-Side Layout -->
            <div class="content-wrapper">
                
                <!-- Left Side: Form -->
                <div class="form-container">
                    <h4 class="text-info">Schedule Information</h4>
                    <div class="schedule-form">
                        <div class="form-group">
                            <label for="
                            _type" class="control-label text-info">Service Type <span class="text-danger">*</span></label>
                            <select name="service_type" id="service_type" class="custom-select select2">
                                <option value="" disabled selected hidden>Select service</option>
                                <option value="INSTALL" <?php echo isset($service_type) && $service_type == 'INSTALL' ? 'selected' : '' ?>>INSTALL</option>
                                <option value="CLEANING" <?php echo isset($service_type) && $service_type == 'CLEANING' ? 'selected' : '' ?>>CLEANING</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_date" class="control-label text-info">Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm rounded-0" value="">
                        </div>
                        
                        <!-- Service Type 2 and End Date Removed -->

                        <div class="form-group">
                            <label for="customer_name" class="control-label text-info">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control form-control-sm rounded-0" value="">
                        </div>
                        <div class="form-group">
                            <label for="address" class="control-label text-info">Address</label>
                            <input type="text" name="address" id="address" class="form-control form-control-sm rounded-0" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="customer_cp" class="control-label text-info">Customer Mobile No.</label>
                            <input type="text" name="customer_cp" id="customer_cp" class="form-control form-control-sm rounded-0" value="" placeholder="09XX-XXX-YYYY">
                        </div>
                        <div class="form-group">
                            <label for="staff_name" class="control-label text-info">Staff Name <span class="text-danger">*</span></label>
                            <input type="text" name="staff_name" id="staff_name" class="form-control form-control-sm rounded-0" value="">
                        </div>
                    </div>
                    
                    <div class="form-group mt-2">
                        <label for="remarks" class="text-info control-label">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="2" class="form-control rounded-0"></textarea>
                    </div>
                    
                    <div class="add-to-list-container">
                        <div class="form-group">
                            <button type="button" class="btn btn-flat btn-primary btn-sm" id="add_to_list">Add Details</button>
                            <button type="button" class="btn btn-flat btn-secondary btn-sm" id="clear_form">Clear</button>
                        </div>
                        <div id="task_added_message" class="task-added-message">New Task Added!</div>
                    </div>
                </div>
                
                <!-- Right Side: Table & Buttons -->
                <div class="table-container">
                    <h4 class="text-info">Scheduled Details</h4>
                    <table class="table table-striped table-bordered" id="list">
                        <colgroup>
                            <col width="3%">
                            <col width="3%"> 
                            <col width="15%">
                            <col width="12%">
                            <col width="12%">
                            <col width="20%">
                            <col width="15%">
                            <col width="12%">
                            <col width="15%">
                        </colgroup>
                        <thead>
                            <tr class="text-light bg-navy">
                                <th class="text-center py-1 px-2"></th>
                                <th class="text-center py-1 px-2">#</th> 
                                <th class="text-center py-1 px-2">Service Type</th>
                                <th class="text-center py-1 px-2">Date</th>
                                <th class="text-center py-1 px-2">Customer</th>
                                <th class="text-center py-1 px-2">Address</th>
                                <th class="text-center py-1 px-2">Customer Mobile No.</th>
                                <th class="text-center py-1 px-2">Staff</th>
                                <th class="text-center py-1 px-2">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(isset($id) && isset($stock_ids)):
                                $qry = $conn->query("SELECT s.*,i.name,i.description FROM `stock_list` s inner join item_list i on s.item_id = i.id where s.id in ({$stock_ids})");
                                if($qry) {
                                    $rowNum = 1; 
                                    while($row = $qry->fetch_assoc()):
                                        // Map database columns to variables
                                        $service_type_val = isset($service_type) ? $service_type : (isset($service_type_2) ? $service_type_2 : '');
                                        $start_date_val = isset($start_date) ? $start_date : (isset($end_date) ? $end_date : '');
                            ?>
                            <tr>
                                <td class="py-1 px-2 text-center">
                                    <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
                                </td>
                                <td class="py-1 px-2 text-center"><?php echo $rowNum; ?></td> 
                                <td class="py-1 px-2 text-center service_type">
                                    <span class="visible"><?php echo $service_type_val; ?></span>
                                    <input type="hidden" name="service_type[]" value="<?php echo $service_type_val; ?>">
                                </td>
                                <td class="py-1 px-2 text-center start_date">
                                    <span class="visible"><?php echo $start_date_val; ?></span>
                                    <input type="hidden" name="start_date[]" value="<?php echo $start_date_val; ?>">
                                </td>
                                <td class="py-1 px-2 text-center customer_name">
                                    <span class="visible"><?php echo isset($customer_name) ? $customer_name : ''; ?></span>
                                    <input type="hidden" name="customer_name[]" value="<?php echo isset($customer_name) ? $customer_name : ''; ?>">
                                </td>
                                <td class="py-1 px-2 text-center address">
                                    <span class="visible"><?php echo isset($address) ? $address : 'N/A'; ?></span>
                                    <input type="hidden" name="address[]" value="<?php echo isset($address) ? $address : 'N/A'; ?>">
                                </td>
                                <td class="py-1 px-2 text-center customer_cp">
                                    <span class="visible"><?php echo isset($customer_cp) ? $customer_cp : 'N/A'; ?></span>
                                    <input type="hidden" name="customer_cp[]" value="<?php echo isset($customer_cp) ? $customer_cp : 'N/A'; ?>">
                                </td>
                                <td class="py-1 px-2 text-center staff_name">
                                    <span class="visible"><?php echo isset($staff_name) ? $staff_name : ''; ?></span>
                                    <input type="hidden" name="staff_name[]" value="<?php echo isset($staff_name) ? $staff_name : ''; ?>">
                                </td>
                                <td class="py-1 px-2 remarks">
                                    <span class="visible"><?php echo isset($remarks) ? $remarks : ''; ?></span>
                                    <input type="hidden" name="remarks[]" value="<?php echo isset($remarks) ? $remarks : ''; ?>">
                                </td>
                            </tr>
                            <?php 
                                    $rowNum++; 
                                    endwhile;
                                } else {
                                    echo "<tr><td colspan='9' class='text-center text-danger'>Error loading stock data</td></tr>";
                                }
                            endif; 
                            ?>
                        </tbody>
                    </table>
                    
                    <!-- Buttons moved here -->
                    <div class="form-group mt-3 text-right">
                        <button class="btn btn-flat btn-primary" type="submit" form="manage_task-form">Save</button>
                        <a class="btn btn-flat btn-dark" href="<?php echo base_url.'/admin?page=calendar/index' ?>">Go to List</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Centered Toast Notification -->
<div id="centered-toast" class="centered-toast"></div>

<table id="clone_list" class="d-none">
    <tr>
        <td class="py-1 px-2 text-center">
            <button class="btn btn-outline-danger btn-sm rem_row" type="button"><i class="fa fa-times"></i></button>
        </td>
        <td class="py-1 px-2 text-center row-num"></td> 
        <td class="py-1 px-2 text-center service_type">
            <span class="visible"></span>
            <input type="hidden" name="service_type[]">
        </td>
        <td class="py-1 px-2 text-center start_date">
            <span class="visible"></span>
            <input type="hidden" name="start_date[]">
        </td>
        <td class="py-1 px-2 text-center customer_name">
            <span class="visible"></span>
            <input type="hidden" name="customer_name[]">
        </td>
        <td class="py-1 px-2 text-center address">
            <span class="visible"></span>
            <input type="hidden" name="address[]">
        </td>
        <td class="py-1 px-2 text-center customer_cp">
            <span class="visible"></span>
            <input type="hidden" name="customer_cp[]">
        </td>
        <td class="py-1 px-2 text-center staff_name">
            <span class="visible"></span>
            <input type="hidden" name="staff_name[]">
        </td>
        <td class="py-1 px-2 remarks">
            <span class="visible"></span>
            <input type="hidden" name="remarks[]">
        </td>
    </tr>
</table>
<script>
    var items = <?php echo json_encode($item_arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    var costs = <?php echo json_encode($cost_arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    
    // Snowflake effect
    function createSnowflakes() {
        const snowflakeSymbols = ['❄', '❅', '❆'];
        const snowflakeCount = 50;
        
        for (let i = 0; i < snowflakeCount; i++) {
            const snowflake = document.createElement('div');
            snowflake.className = 'snowflake';
            snowflake.innerHTML = snowflakeSymbols[Math.floor(Math.random() * snowflakeSymbols.length)];
            
            const size = Math.random() * 1.5 + 0.5;
            const duration = Math.random() * 10 + 5;
            const delay = Math.random() * 5;
            const left = Math.random() * 100;
            
            snowflake.style.fontSize = `${size}em`;
            snowflake.style.animationDuration = `${duration}s, ${duration/3}s`;
            snowflake.style.animationDelay = `${delay}s, ${delay/3}s`;
            snowflake.style.left = `${left}%`;
            
            document.body.appendChild(snowflake);
        }
    }
    
    $(function(){
        createSnowflakes();
        
        // Clear form on load
        $('#service_type').val('').trigger('change');
        $('#start_date').val('');
        $('#customer_name').val('');
        $('#address').val('');
        $('#customer_cp').val('');
        $('#staff_name').val('');
        $('#remarks').val('');
        
        $('.select2').select2({
            placeholder:"Please select here",
            width:'resolve',
        });
        
        $('#service_type').select2({
            placeholder:"Select service",
            width:'resolve',
            allowClear: true
        });
        
        $('#customer_cp').on('input', function() {
            let value = this.value.replace(/[^0-9-]/g, '');
            let digitsOnly = value.replace(/-/g, '');
            
            if (digitsOnly.length > 11) {
                digitsOnly = digitsOnly.substring(0, 11);
            }
            
            if (digitsOnly.length > 0) {
                if (digitsOnly.length >= 2 && !digitsOnly.startsWith('09')) {
                    digitsOnly = '09' + digitsOnly.substring(2);
                }
                
                value = digitsOnly.substring(0, 4);
                if (digitsOnly.length > 4) {
                    value += '-' + digitsOnly.substring(4, 7);
                }
                if (digitsOnly.length > 7) {
                    value += '-' + digitsOnly.substring(7, 11);
                }
            }
            
            this.value = value;
        });

        $('#add_to_list').click(function(){
            var service_type = $('#service_type').val();
            var start_date = $('#start_date').val();
            
            var customer_name = $('#customer_name').val();
            var address = $('#address').val();
            var customer_cp = $('#customer_cp').val();
            var staff_name = $('#staff_name').val();
            var remarks = $('#remarks').val();
            
            // Validation
            if(!service_type || !start_date || !customer_name || !staff_name) {
                alert_toast('Please fill in all required fields (Service Type, Date, Customer, Staff).','warning');
                return false;
            }
            
            if(customer_cp) {
                var customer_cp_digits = customer_cp.replace(/-/g, '');
                if(customer_cp_digits.length !== 11 || !customer_cp_digits.startsWith('09')) {
                    alert_toast('Customer Mobile No. must be 11 digits starting with 09 (format: 09XX-XXX-YYYY).','warning');
                    return false;
                }
            }
            
            address = address || 'N/A';
            customer_cp = customer_cp || 'N/A';
            
            var tr = $('#clone_list tr').clone();
            
            var rowCount = $('table#list tbody tr').length + 1;
            tr.find('.row-num').text(rowCount);
            
            // Map values to the renamed fields
            tr.find('[name="service_type[]"]').val(service_type);
            tr.find('.service_type .visible').text(service_type);
            
            tr.find('[name="start_date[]"]').val(start_date);
            tr.find('.start_date .visible').text(start_date);
            
            tr.find('[name="customer_name[]"]').val(customer_name);
            tr.find('.customer_name .visible').text(customer_name);
            
            tr.find('[name="address[]"]').val(address);
            tr.find('.address .visible').text(address);
            
            tr.find('[name="customer_cp[]"]').val(customer_cp);
            tr.find('.customer_cp .visible').text(customer_cp);
            
            tr.find('[name="staff_name[]"]').val(staff_name);
            tr.find('.staff_name .visible').text(staff_name);
            
            tr.find('[name="remarks[]"]').val(remarks);
            tr.find('.remarks .visible').text(remarks);
            
            $('table#list tbody').append(tr);
            
            tr.find('.rem_row').click(function(){
                rem($(this));
            });
            
            $('#task_added_message').show();
            
            $('html, body').animate({
                scrollTop: $("#list").offset().top - 100
            }, 800);
            
            setTimeout(function() {
                $('#task_added_message').fadeOut();
            }, 3000);
            
            // Clear form
            $('#service_type').val('').trigger('change');
            $('#start_date').val('');
            $('#customer_name').val('');
            $('#address').val('');
            $('#customer_cp').val('');
            $('#staff_name').val('');
            $('#remarks').val('');
        });
        
        $('#clear_form').click(function(){
            $('#service_type').val('').trigger('change');
            $('#start_date').val('');
            $('#customer_name').val('');
            $('#address').val('');
            $('#customer_cp').val('');
            $('#staff_name').val('');
            $('#remarks').val('');
            $('#task_added_message').hide();
        });
        
        function showCenteredToast(message, type = 'success') {
            var toast = $('#centered-toast');
            toast.text(message);
            
            if (type === 'success') toast.css('background-color', '#28a745');
            else if (type === 'error') toast.css('background-color', '#dc3545');
            else if (type === 'warning') toast.css('background-color', '#ffc107');
            
            toast.addClass('show');
            setTimeout(function() { toast.removeClass('show'); }, 3000);
        }
        
        $('#manage_task-form').submit(function(e){
            e.preventDefault();
            var _this = $(this)
            $('.err-msg').remove();
            start_loader();
            
            if($('table#list tbody tr').length == 0) {
                showCenteredToast('Please add at least one schedule item.', 'warning');
                end_loader();
                return false;
            }
            
            var validNumbers = true;
            $('table#list tbody tr').each(function() {
                var customer_cp = $(this).find('.customer_cp .visible').text();
                if(customer_cp && customer_cp !== 'N/A') {
                    var digitsOnly = customer_cp.replace(/-/g, '');
                    if(digitsOnly.length !== 11 || !digitsOnly.startsWith('09')) validNumbers = false;
                }
            });
            
            if(!validNumbers) {
                showCenteredToast('All customer mobile numbers must be 11 digits starting with 09.', 'warning');
                end_loader();
                return false;
            }
            
            $.ajax({
                url:_base_url_+"classes/Master.php?f=save_schedule",
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error:err=>{
                    console.log(err);
                    showCenteredToast("An error occurred", 'error');
                    end_loader();
                },
                success:function(resp){
                    end_loader();
                    if(resp.status == 'success'){
                        showCenteredToast(resp.msg || 'Task successfully added!', 'success');
                        _this[0].reset();
                        $('table#list tbody').empty();
                        $('#service_type').val('').trigger('change');
                        $('#address').val('');
                        $('#customer_cp').val('');
                        $('#task_added_message').hide();
                        setTimeout(function() { location.reload(); }, 1500);
                    } else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>').addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        showCenteredToast(resp.msg, 'error');
                    } else {
                        showCenteredToast("An error occurred", 'error');
                    }
                    $('html,body').animate({scrollTop:0},'fast');
                }
            });
        });
        
        if(<?php echo isset($id) && $id > 0 ? 'true' : 'false' ?>){
            $('table#list tbody tr .rem_row').click(function(){ rem($(this)); });
        }
    });
    
    function rem(_this){
        var row = _this.closest('tr');
        
        var service_type = row.find('.service_type .visible').text();
        var start_date = row.find('.start_date .visible').text();
        var customer_name = row.find('.customer_name .visible').text();
        var address = row.find('.address .visible').text();
        var customer_cp = row.find('.customer_cp .visible').text();
        var staff_name = row.find('.staff_name .visible').text();
        var remarks = row.find('.remarks .visible').text();
        
        if (address === 'N/A') address = '';
        if (customer_cp === 'N/A') customer_cp = '';
        
        // Map back to form inputs
        $('#service_type').val(service_type).trigger('change');
        $('#start_date').val(start_date);
        $('#customer_name').val(customer_name);
        $('#address').val(address);
        $('#customer_cp').val(customer_cp);
        $('#staff_name').val(staff_name);
        $('#remarks').val(remarks);
        
        $('#task_added_message').hide();
        
        row.remove();
        
        $('table#list tbody tr').each(function(index) {
            $(this).find('.row-num').text(index + 1);
        });
    }
</script>
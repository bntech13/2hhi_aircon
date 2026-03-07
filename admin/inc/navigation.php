<?php
// Get current page
 $page = isset($_GET['page']) ? $_GET['page'] : '';

// Get count of tasks due within 24 hours
 $count = 0;
if (isset($_settings->conn)) {
    // Check if connection is valid
    if ($_settings->conn instanceof mysqli) {
        // First, let's test if the table exists and has data
        $test_sql = "SELECT COUNT(*) as total FROM task_list";
        $test_result = $_settings->conn->query($test_sql);
        if ($test_result) {
            $test_row = $test_result->fetch_assoc();
            $total_tasks = $test_row['total'];
            error_log("Total tasks in database: " . $total_tasks);
        } else {
            error_log("Error checking task_list table: " . $_settings->conn->error);
        }
        
        // Now get tasks due within 24 hours
        $sql = "SELECT COUNT(*) as count FROM task_list 
                WHERE end_date >= NOW() 
                AND end_date <= DATE_ADD(NOW(), INTERVAL 1 DAY)
                AND status != 1"; // Assuming status 1 means completed
        
        error_log("SQL Query: " . $sql);
        
        $result = $_settings->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
            error_log("Tasks due within 24 hours: " . $count);
        } else {
            error_log("Query Error: " . $_settings->conn->error);
        }
    } else {
        error_log("Database connection is not a valid mysqli instance");
    }
} else {
    error_log("Database connection not set");
}
?>

<style>
  /* Remove blue highlight from active navigation items */
  .nav-pills .nav-link.active,
  .nav-pills .show > .nav-link {
    background-color: transparent !important;
    color: #fff !important;
    border-left: 3px solid #fff !important;
  }
  
  /* Style for parent dropdown when active */
  .nav-item.has-treeview.menu-open > .nav-link {
    background-color: transparent !important;
    color: #fff !important;
  }
  
  /* Remove any background hover effects */
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }
  
  /* Style for treeview items */
  .nav-treeview .nav-link {
    padding-left: 1.5rem !important;
  }
  
  .nav-treeview .nav-link.active {
    border-left: 3px solid #fff !important;
    padding-left: calc(1.5rem - 3px) !important;
  }
  
  /* Alternative: Add left border to active items instead of background */
  .nav-pills .nav-link.active,
  .nav-treeview .nav-link.active {
    border-left: 3px solid #fff;
    padding-left: 12px;
  }
  
  /* Enhanced blinking notification badge */
  @keyframes blink {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.3; transform: scale(1.2); }
    100% { opacity: 1; transform: scale(1); }
  }
  
  .badge-blink {
    animation: blink 1s infinite;
    font-size: 0.65rem;
    padding: 0.25em 0.5em;
    margin-left: 8px;
    background-color: #dc3545 !important;
    border-radius: 10px;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    box-shadow: 0 0 8px rgba(220, 53, 69, 0.8);
    z-index: 1000;
  }
  
  /* Ensure nav-link has position relative for absolute badge positioning */
  .nav-link {
    position: relative !important;
  }
  
  /* Alternative notification approach - floating badge */
  .calendar-notification {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 12px;
    height: 12px;
    background-color: #dc3545;
    border-radius: 50%;
    animation: pulse 2s infinite;
    z-index: 1001;
  }
  
  @keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
  }
  
  /* Sidebar auto-collapse styles */
  .main-sidebar {
    transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
  }
  
  body.sidebar-collapse .main-sidebar {
    margin-left: -250px;
  }
  
  body.sidebar-collapse .content-wrapper,
  body.sidebar-collapse .main-footer {
    margin-left: 0;
  }
  
  /* For mobile responsiveness */
  @media (max-width: 767.98px) {
    body.sidebar-collapse .main-sidebar {
      margin-left: -250px;
    }
  }
</style>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4 sidebar-no-expand">
  <!-- Brand Logo -->
  <a href="<?php echo base_url ?>admin" class="brand-link bg-primary text-sm">
    <img src="<?php echo validate_image($_settings->info('logo'))?>" alt="Store Logo" class="brand-image img-circle elevation-3 bg-black" style="width: 1.8rem;height: 1.8rem;max-height: unset">
    <span class="brand-text font-weight-light"><?php echo $_settings->info('short_name') ?></span>
  </a>
  
  <!-- Sidebar -->
  <div class="sidebar os-host os-theme-light os-host-overflow os-host-overflow-y os-host-resize-disabled os-host-transition os-host-scrollbar-horizontal-hidden">
    <div class="os-resize-observer-host observed">
      <div class="os-resize-observer" style="left: 0px; right: auto;"></div>
    </div>
    <div class="os-size-auto-observer observed" style="height: calc(100% + 1px); float: left;">
      <div class="os-resize-observer"></div>
    </div>
    <div class="os-content-glue" style="margin: 0px -8px; width: 249px; height: 646px;"></div>
    <div class="os-padding">
      <div class="os-viewport os-viewport-native-scrollbars-invisible" style="overflow-y: scroll;">
        <div class="os-content" style="padding: 0px 8px; height: 100%; width: 100%;">
          <!-- Sidebar user panel (optional) -->
          <div class="clearfix"></div>
          
          <!-- Sidebar Menu -->
          <nav class="mt-4">
            <ul class="nav nav-pills nav-sidebar flex-column text-sm nav-compact nav-flat nav-child-indent nav-collapse-hide-child" data-widget="treeview" role="menu" data-accordion="false">
              <li class="nav-item">
                <a href="./" class="nav-link nav-home <?php echo (!isset($_GET['page']) || $_GET['page'] == '') ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-tachometer-alt"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              
              <!-- Purchase Menu -->
              <li class="nav-item has-treeview <?php echo (strpos($page, 'purchase_order') !== false) ? 'menu-open' : ''; ?>">
                <a href="#" class="nav-link <?php echo (strpos($page, 'purchase_order') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-shopping-cart"></i>
                  <p>Purchase<i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=purchase_order" class="nav-link <?php echo $page == 'purchase_order' ? 'active' : ''; ?>">
                      <i class="fas fa-folder"></i>  
                      <p>Purchase List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=purchase_order/manage_po" class="nav-link <?php echo $page == 'purchase_order/manage_po' ? 'active' : ''; ?>">
                      <i class="fas fa-plus-circle"></i>
                      <p>Add Purchase</p>
                    </a>
                  </li>
                </ul>
              </li>
              
              <!-- Sales Menu -->
              <li class="nav-item has-treeview <?php echo (strpos($page, 'sales') !== false) ? 'menu-open' : ''; ?>">
                <a href="#" class="nav-link <?php echo (strpos($page, 'sales') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-file-invoice-dollar"></i>
                  <p>Sale<i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=sales" class="nav-link <?php echo $page == 'sales' ? 'active' : ''; ?>">
                      <i class="fas fa-folder"></i>
                      <p>Sales List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=sales/manage_sale" class="nav-link <?php echo $page == 'sales/manage_sale' ? 'active' : ''; ?>">
                      <i class="fas fa-plus-circle"></i> 
                      <p>Add Sale</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- Calendar -->
              <li class="nav-item has-treeview <?php echo (strpos($page, 'calendar') !== false) ? 'menu-open' : ''; ?>">
                <a href="#" class="nav-link <?php echo (strpos($page, 'calendar') !== false) ? 'active' : ''; ?>">
                  <i class="nav-icon fas fa-calendar"></i>
                  <p>Schedule<i class="right fas fa-angle-left"></i></p>
                  <?php if ($count > 0): ?>
                    <span class="badge badge-danger badge-blink"><?php echo $count; ?></span>
                  <?php endif; ?>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=calendar" class="nav-link <?php echo $page == 'calendar' ? 'active' : ''; ?>">
                      <i class="fas fa-list">  </i>
                     &nbsp; <p>  Task List</p>
                    </a>
                  </li>
                   <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=calendar/completed_task" class="nav-link <?php echo $page == 'calendar/completed_task' ? 'active' : ''; ?>">
                      <i class="fas fa-check">  </i>
                     &nbsp; <p>  Task Completed</p>
                    </a>
                  </li>
                  
                  <li class="nav-item">
                    <a href="<?php echo base_url ?>admin/?page=calendar/manage_task" class="nav-link <?php echo $page == 'calendar/manage_task' ? 'active' : ''; ?>">
                      <i class="fas fa-calendar"></i> 
                        &nbsp; <p>Set Schedule</p>
                    </a>
                  </li>
                </ul>
                <?php if ($count > 0): ?>
                  <div class="calendar-notification"></div>
                <?php endif; ?>
              </li>

              <!-- Customer Menu -->
<li class="nav-item">
  <a href="<?php echo base_url ?>admin/?page=customer/view" class="nav-link <?php echo $page == 'customer' ? 'active' : ''; ?>">
    <i class="nav-icon fas fa-eye"></i>
    <p>Customer</p>
  </a>
</li>
         <!-- View Stocks Menu -->
     
<!-- View Stocks Menu -->
<li class="nav-item">
  <a href="<?php echo base_url ?>admin/?page=stocks" class="nav-link <?php echo $page == 'stocks' ? 'active' : ''; ?>">
    <i class="nav-icon fas fa-eye"></i>
    <p>View Stocks</p>
  </a>
</li>


              <?php if($_settings->userdata('type') == 1): ?>
                <li class="nav-header">Settings</li>
                
                <!-- User List -->
                <li class="nav-item">
                  <a href="<?php echo base_url ?>admin/?page=user/list" class="nav-link <?php echo $page == 'user/list' ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-users"></i>
                    <p>User List</p>
                  </a>
                </li>
                
                <!-- Supplier -->
                <li class="nav-item">
                  <a href="<?php echo base_url ?>admin/?page=maintenance/supplier" class="nav-link <?php echo $page == 'maintenance/supplier' ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-truck-loading"></i>
                    <p>Supplier List</p>
                  </a>
                </li>

                <!-- Account -->
                <li class="nav-item">
                  <a href="<?php echo base_url ?>admin/?page=system_info" class="nav-link <?php echo $page == 'system_info' ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-cogs"></i>
                    <p>Account</p>
                  </a>
                </li>

              <?php endif; ?>
            </ul>
          </nav>
          <!-- /.sidebar-menu -->
        </div>
      </div>
    </div>
    <div class="os-scrollbar os-scrollbar-horizontal os-scrollbar-unusable os-scrollbar-auto-hidden">
      <div class="os-scrollbar-track">
        <div class="os-scrollbar-handle" style="width: 100%; transform: translate(0px, 0px);"></div>
      </div>
    </div>
    <div class="os-scrollbar os-scrollbar-vertical os-scrollbar-auto-hidden">
      <div class="os-scrollbar-track">
        <div class="os-scrollbar-handle" style="height: 55.017%; transform: translate(0px, 0px);"></div>
      </div>
    </div>
    <div class="os-scrollbar-corner"></div>
  </div>
  <!-- /.sidebar -->
</aside>

<script>
  $(document).ready(function(){
    // Get current page from URL
    var currentPage = '<?php echo isset($_GET['page']) ? $_GET['page'] : 'home' ?>';
    
    // Remove any existing active classes
    $('.nav-link').removeClass('active');
    $('.nav-item').removeClass('menu-open');
    
    // Highlight the current page link
    $('.nav-link').each(function() {
      var href = $(this).attr('href');
      if (href) {
        // Extract page parameter from URL
        var pageMatch = href.match(/page=([^&]*)/);
        if (pageMatch && pageMatch[1] === currentPage) {
          $(this).addClass('active');
          
          // If it's in a dropdown, open the dropdown
          var parentDropdown = $(this).closest('.has-treeview');
          if (parentDropdown.length > 0) {
            parentDropdown.addClass('menu-open');
            parentDropdown.find('> .nav-link').addClass('active');
          }
        }
      }
    });
    
    // Handle dashboard link (no page parameter)
    if (currentPage === 'home' || currentPage === '') {
      $('.nav-home').addClass('active');
    }
    
    // Handle click events for navigation links
    $('.nav-link').on('click', function(e) {
      // Don't process dropdown toggle links
      if ($(this).attr('href') === '#' || $(this).hasClass('dropdown-toggle')) {
        return;
      }
      
      // Remove active classes from all links
      $('.nav-link').removeClass('active');
      $('.nav-item').removeClass('menu-open');
      
      // Add active class to clicked link
      $(this).addClass('active');
      
      // If it's in a dropdown, open the dropdown
      var parentDropdown = $(this).closest('.has-treeview');
      if (parentDropdown.length > 0) {
        parentDropdown.addClass('menu-open');
        parentDropdown.find('> .nav-link').addClass('active');
      }
    });
    
    $('#receive-nav').click(function(){
      $('#uni_modal').on('shown.bs.modal',function(){
        $('#find-transaction [name="tracking_code"]').focus();
      });
      uni_modal("Enter Tracking Number","transaction/find_transaction.php");
    });
    
    // Debug: Check if badge is present
    var $badge = $('.badge-blink');
    var $notification = $('.calendar-notification');
    
    console.log('Task count from PHP: <?php echo $count; ?>');
    
    if ($badge.length > 0) {
      console.log('Badge found with text: ' + $badge.text());
      // Force reflow to restart animation
      $badge.css('animation', 'none');
      setTimeout(function() {
        $badge.css('animation', '');
      }, 10);
    } else {
      console.log('No badge found');
    }
    
    if ($notification.length > 0) {
      console.log('Notification dot found');
    } else {
      console.log('No notification dot found');
    }
    
    // Test animation by adding a temporary badge
    if (<?php echo $count; ?> > 0) {
      console.log('Should show notification for ' + <?php echo $count; ?> + ' tasks');
    }
    
    // Auto-collapse sidebar functionality
    var sidebarTimer;
    var isManuallyOpened = false;
    
    // Initialize sidebar state
    function initSidebarState() {
      // Only apply auto-collapse on large screens
      if ($(window).width() >= 992) {
        // Start with sidebar collapsed
        $('body').addClass('sidebar-collapse');
      }
    }
    
    // Initialize on page load
    initSidebarState();
    
    // Handle mouse enter - expand sidebar
    $('.main-sidebar').on('mouseenter', function() {
      clearTimeout(sidebarTimer);
      if (!isManuallyOpened) {
        $('body').removeClass('sidebar-collapse');
      }
    });
    
    // Handle mouse leave - collapse sidebar after delay
    $('.main-sidebar').on('mouseleave', function() {
      if (!isManuallyOpened) {
        sidebarTimer = setTimeout(function() {
          $('body').addClass('sidebar-collapse');
        }, 300);
      }
    });
    
    // Handle click on sidebar toggle button (if exists)
    $(document).on('click', '[data-widget="pushmenu"]', function(e) {
      e.preventDefault();
      isManuallyOpened = !isManuallyOpened;
      
      // If manually opened, clear any pending auto-collapse
      if (isManuallyOpened) {
        clearTimeout(sidebarTimer);
        $('body').removeClass('sidebar-collapse');
      }
    });
    
    // Handle window resize
    $(window).on('resize', function() {
      // Re-initialize sidebar state on resize
      initSidebarState();
      
      // Reset manual state on resize
      isManuallyOpened = false;
    });
  });
</script>
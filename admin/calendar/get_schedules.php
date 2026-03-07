<?php
// Include necessary files
require_once('../classes/Connection.php');

// Set header to return JSON
header('Content-Type: application/json');

try {
    // Initialize database connection
    $conn = new Connection();
    
    // Get all schedules
    $data = array();
    $qry = $conn->query("SELECT * FROM schedule_list ORDER BY start_date ASC");
    
    if($qry){
        while($row = $qry->fetch_assoc()){
            $data[] = $row;
        }
        echo json_encode(array('status'=>'success','data'=>$data));
    } else {
        echo json_encode(array('status'=>'failed','msg'=>'Database error: ' . $conn->error));
    }
} catch (Exception $e) {
    echo json_encode(array('status'=>'failed','msg'=>'Exception: ' . $e->getMessage()));
}
?>
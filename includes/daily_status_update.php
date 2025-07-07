<?php
    include('includes/dbh.php');

    $today = date('l');
    
    $conn->query("UPDATE tblstafflist SET status = 'Inactive'");
    
    $conn->query("
        UPDATE tblstafflist s
        JOIN tblstaff_schedule sch ON s.id = sch.staff_id
        JOIN tblusers u ON s.email = u.email
        SET s.status = 'Active'
        WHERE sch.day_of_week = '$today' AND u.is_disabled_by_admin = 0
    ");
?>
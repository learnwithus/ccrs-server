<?php 
    /*
       Retrieves an online course from its course ID
       Andrew Park - 20/07/2016
    */ 
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");
    
    $selected = mssql_select_db($db_name, $conn) 
        or die("Could not select CCRS db");
    
    if (isset($_GET["id"])) {
        $row = mssql_query("SELECT CourseID, URL, AccessPeriod FROM tblOnlineCourses WHERE CourseID = " . $_GET["id"]);
        $course = mssql_fetch_assoc($row);
        echo json_encode($course);
    } else {
        echo json_encode("No course ID supplied");
    }
?>


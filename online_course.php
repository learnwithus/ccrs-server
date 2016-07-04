<?php 
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $conn = mysql_connect($host, $username, $password)
        or die("Unable to connect to MySQL");
    
    $selected = mysql_select_db($db_name, $conn) 
        or die("Could not select CCRS db");
    
    if (isset($_GET["id"])) {
        $row = mysql_query("SELECT CourseID, URL, AccessPeriod FROM tblOnlineCourses WHERE CourseID = " . $_GET["id"]);
        $course = mysql_fetch_assoc($row);
        echo json_encode($course);
    } else {
        echo json_encode(array('result' => array()));
    }
?>


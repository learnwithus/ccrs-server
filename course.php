<?php
    header('Access-Control-Allow-Origin: *'); 
    require "config.php";
    $conn = mysql_connect($host, $username, $password)
        or die("Unable to connect to MySQL");
    
    $selected = mysql_select_db($db_name, $conn) 
        or die("Could not select CCRS db");
    
    if (isset($_GET["id"])) {
        $row = mysql_query("SELECT CourseID, Title, CourseType, Description, CourseLength, Fee FROM tblCourses WHERE CourseID = " . $_GET["id"]);
        $course = mysql_fetch_assoc($row);
        if ($course['CourseType'] == 1) {
            $course['CourseType'] = 'Online Course';
        } else {
            $course['CourseType'] = 'Classroom Course';
        }
        echo json_encode($course);
    } else {
        echo json_encode(array('result' => array()));
    }
?>


<?php
    /*
       Retrieves course details using the course's ID
       Andrew Park - 20/07/2016
    */
    header('Access-Control-Allow-Origin: *'); 
    require "config.php";
    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to MSSQL");
    
    $selected = mssql_select_db($db_name, $conn) 
        or die("Could not select CCRS db");
    
    if (isset($_GET["id"])) {
        $q = "SELECT CourseID, Title, CourseType, Description, CourseLength, Fee, Registration FROM tblCourses WHERE CourseID = " . $_GET["id"];
        $row = mssql_query($q);
        
        $course = mssql_fetch_assoc($row);
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


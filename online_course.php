<?php 
    header('Access-Control-Allow-Origin: *'); 
    $conn = mysql_connect('localhost', 'nomonke1_park', '123...Park')
        or die("Unable to connect to MySQL");
    
    $selected = mysql_select_db('nomonke1_ccrs', $conn) 
        or die("Could not select CCRS db");
    
    if (isset($_GET["id"])) {
        $row = mysql_query("SELECT CourseID, URL, AccessPeriod FROM tblOnlineCourses WHERE CourseID = " . $_GET["id"]);
        $course = mysql_fetch_assoc($row);
        echo json_encode($course);
    } else {
        echo json_encode(array('result' => array()));
    }
?>


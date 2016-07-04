<?php 
    header('Access-Control-Allow-Origin: *'); 
    $conn = mysql_connect('localhost', 'nomonke1_park', '123...Park')
        or die("Unable to connect to MySQL");
    
    $selected = mysql_select_db('nomonke1_ccrs', $conn) 
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


<?php
    /*
       Retrieves course ID, session ID pairs for all registered courses of a user, given the user ID
       Andrew Park - 20/07/2016
    */ 
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $list_courses = array();

    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($db_name, $conn)
        or die("Could not select CCRS db");

    if (isset($_GET["user"])) {
        $result = mssql_query("
            SELECT DISTINCT course.CourseID AS CourseID, session.SessionID AS SessionID 
            FROM tblCourses AS course, tblSessions AS session,
                 tblRegisterlist AS register
            WHERE register.UserID = " . $_GET["user"] . " AND
                  register.SessionID = session.SessionID AND
                  session.CourseID = course.CourseID

            UNION

            SELECT DISTINCT course.CourseID AS CourseID, register.SessionID AS SessionID
            FROM tblCourses AS course, tblOnlineCourses AS online,
                 tblRegisterlist AS register, tblOnlineCourseRegisterList AS online_register
            WHERE register.UserID = " . $_GET["user"] . " AND register.RegisterID = online_register.RegisterID AND 
                  online_register.CourseId = course.CourseID AND online_register.CourseId = online.CourseID
            ");

        while ($row = mssql_fetch_assoc($result)) {
            array_push($list_courses, $row);
        }

        echo json_encode($list_courses);
    } else {
        echo "No user supplied";
    }
?>


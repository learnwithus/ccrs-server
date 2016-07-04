<?php
    function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $list_courses = array();

    $conn = mysql_connect($host, $username, $password)
        or die("Unable to connect to MySQL");

    $selected = mysql_select_db($db_name, $conn)
        or die("Could not select CCRS db");

    if (isset($_GET["user"])) {
        mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $conn);

        $result = mysql_query("
            SELECT DISTINCT course.CourseID AS CourseID, session.SessionID AS SessionID 
            FROM tblCourses AS course, tblSessions AS session,
                 tblRegisterlist AS register
            WHERE register.UserID = " . $_GET["user"] . " AND
                  register.SessionID = session.SessionID AND
                  session.CourseID = course.CourseID

            UNION

            SELECT DISTINCT course.CourseID AS CourseID, register.SessionID AS SessionID
            FROM tblCourses AS course, tblOnlineCourses AS online,
                 tblRegisterlist AS register
            WHERE register.UserID = " . $_GET["user"] . " AND
                  online.CourseID = course.CourseID
            ");

        while ($row = mysql_fetch_assoc($result)) {
            array_push($list_courses, $row);
        }

        echo json_encode(utf8ize($list_courses));
    } else {
        echo "No user supplied";
    }
?>


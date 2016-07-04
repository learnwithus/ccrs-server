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

    if (!isset($_GET["Organization"]) && !isset($_GET["CourseType"])) {
        mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $conn);

        $result = mysql_query("SELECT course.CourseID, course.Title, course.CourseType, course.Status, org.Organization
            FROM tblCourses AS course, tblOrganizations AS org
            WHERE course.Status = 1 AND course.OrgId = org.OrgId AND course.Title LIKE '%" . $_GET["title"] . "%'");

        while ($row = mysql_fetch_assoc($result)) {
            if ($row['CourseType'] == 1) {
                $row['CourseType'] = 'Online Course';
            } else {
                $row['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $row);
        }

        echo json_encode(utf8ize($list_courses));
    } else if (isset($_GET["Organization"]) && !isset($_GET["CourseType"])) {
        $list_courses = array();
        $orgs = $_GET["Organization"];
        $query = "SELECT course.CourseID, course.Title, course.CourseType, course.Status, org.Organization 
            FROM tblCourses AS course, tblOrganizations AS org 
            WHERE course.Status = 1 AND course.OrgId = org.OrgId 
            AND course.Title LIKE '%" . $_GET["title"] . "%'" . " AND (";
        foreach ($orgs as $org) {
            $query .= "org.Organization = " . "\"" . $org . "\"" . " OR "; 
        }
        $query = substr($query, 0, strlen($query) - 4);
        $query .= ")";
        
        $result = mysql_query($query);
        while ($course = mysql_fetch_assoc($result)) {
            if ($course['CourseType'] == 1) {
                $course['CourseType'] = 'Online Course';
            } else {
                $course['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $course);
        }
        echo json_encode(utf8ize($list_courses));
    } else if (!isset($_GET["Organization"]) && isset($_GET["CourseType"])) {
        $types = $_GET["CourseType"];
        $query = "SELECT course.CourseID, course.Title, course.CourseType, course.Status, org.Organization 
            FROM tblCourses AS course, tblOrganizations AS org 
            WHERE course.Status = 1 AND course.OrgId = org.OrgId 
            AND course.Title LIKE '%" . $_GET["title"] . "%'" . " AND ("; 
        foreach ($types as $type) {
            $query .= "course.CourseType = " . $type . " OR "; 
        }
        $query = substr($query, 0, strlen($query) - 4);
        $query .= ")";
        $result = mysql_query($query);
        while ($course = mysql_fetch_assoc($result)) {
            if ($course['CourseType'] == 1) {
                $course['CourseType'] = 'Online Course';
            } else {
                $course['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $course);
        }
        echo json_encode(utf8ize($list_courses));
    } else if (isset($_GET["Organization"]) && isset($_GET["CourseType"])) { 
        $orgs = $_GET["Organization"];
        $types = $_GET["CourseType"];
        $query = "SELECT course.CourseID, course.Title, course.CourseType, course.Status, org.Organization 
            FROM tblCourses AS course, tblOrganizations AS org 
            WHERE course.Status = 1 AND course.OrgId = org.OrgId 
            AND course.Title LIKE '%" . $_GET["title"] . "%'" . " AND (";
        foreach ($orgs as $org) {
            $query .= "org.Organization = " . "\"" . $org . "\"" . " OR "; 
        }
        $query = substr($query, 0, strlen($query) - 4);
        $query .= ")" . " AND (";

        foreach ($types as $type) {
            $query .= "course.CourseType = " . $type . " OR "; 
        }
        $query = substr($query, 0, strlen($query) - 4);
        $query .= ")";

        $result = mysql_query($query);
        while ($course = mysql_fetch_assoc($result)) {
            if ($course['CourseType'] == 1) {
                $course['CourseType'] = 'Online Course';
            } else {
                $course['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $course);
        }
        echo json_encode(utf8ize($list_courses));
    } else {
        echo "No title supplied";
    }
?>


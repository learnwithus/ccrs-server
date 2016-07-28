<?php
    /*
       Retrieves a list of courses given the title (required), organization (VCH, PHC, FH, VIHA)(optional), and type (online, classroom)(optional)
       Andrew Park - 20/07/2016
    */
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $list_courses = array();

    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($db_name, $conn)
        or die("Could not select CCRS db");

    if (!isset($_GET["Organization"]) && !isset($_GET["CourseType"])) { // No optional params
        $result = mssql_query("SELECT course.CourseID, course.Title, course.CourseType, course.Status, org.Organization
            FROM tblCourses AS course, tblOrganizations AS org
            WHERE course.Status = 1 AND course.OrgId = org.OrgId AND course.Title LIKE '%" . $_GET["title"] . "%'");

        while ($row = mssql_fetch_assoc($result)) {
            if ($row['CourseType'] == 1) {
                $row['CourseType'] = 'Online Course';
            } else {
                $row['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $row);
        }

        echo json_encode($list_courses);
    } else if (isset($_GET["Organization"]) && !isset($_GET["CourseType"])) { // Filter by organization
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
        
        $result = mssql_query($query);
        while ($course = mssql_fetch_assoc($result)) {
            if ($course['CourseType'] == 1) {
                $course['CourseType'] = 'Online Course';
            } else {
                $course['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $course);
        }
        echo json_encode($list_courses);
    } else if (!isset($_GET["Organization"]) && isset($_GET["CourseType"])) { // Filter by type
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
        $result = mssql_query($query);
        while ($course = mssql_fetch_assoc($result)) {
            if ($course['CourseType'] == 1) {
                $course['CourseType'] = 'Online Course';
            } else {
                $course['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $course);
        }
        echo json_encode($list_courses);
    } else if (isset($_GET["Organization"]) && isset($_GET["CourseType"])) { // Filter by organization and type
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

        $result = mssql_query($query);
        while ($course = mssql_fetch_assoc($result)) {
            if ($course['CourseType'] == 1) {
                $course['CourseType'] = 'Online Course';
            } else {
                $course['CourseType'] = 'Classroom Course';
            }
            array_push($list_courses, $course);
        }
        echo json_encode($list_courses);
    } else { // No title provided (required)
        echo "No title supplied";
    }
?>


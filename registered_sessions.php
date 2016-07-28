<?php
    /*
       Retrieves registered online courses for a user using the user ID
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
        $result = mssql_query("EXEC CCRSQA.dbo.GetRegisteredSessions @UserId = " . $_GET["user"]);
        
        while ($row = mssql_fetch_assoc($result)) {
            array_push($list_courses, $row);
        }

        echo json_encode($list_courses);
    } else {
        echo "No user supplied";
    }
?>


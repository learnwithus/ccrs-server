<?php
	/*
        Retrieves CCRS user ID from Moodle user ID
        Andrew Park - 20/07/2016
    */
    header('Access-Control-Allow-Origin: *');
    require "config.php";

    $conn = mssql_connect($host, $moodle_username, $moodle_password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($moodle_db_name, $conn)
        or die("Could not select Moodle db");

    if (isset($_GET["user"])) {
    	$q = "SELECT idnumber FROM mdl_user WHERE id = " . $_GET["user"];
        $result = mssql_query($q);
        $row = mssql_fetch_assoc($result);
        echo json_encode($row);
    } else {
        echo "No user supplied";
    }
?>


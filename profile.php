<?php
    /*
       Retrieves a CCRS user profile from the user ID
       Andrew Park - 20/07/2016
    */ 
    header('Access-Control-Allow-Origin: *'); 
    require "config.php";
    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to MSSQL");
    
    $selected = mssql_select_db($db_name, $conn) 
        or die("Could not select CCRS db");
    
    if (isset($_GET["id"])) {
    	/*
        $q = "
        SELECT u.Firstname AS Firstname, u.Lastname AS Lastname, u.Email AS Email, 
        	   uf.JobTitle AS JobTitle, uf.Department AS Department, uf.JobStatus AS JobStatus,
        	   org.Organization 
        FROM tblUsers AS u, tblUserFacility AS uf, tblUserGroups AS ug, tblOrganizations AS org
        WHERE uf.UserID = u.UserID AND ug.UserID = u.UserID AND ug.OrgId = org.OrgId AND u.UserID = " . $_GET["id"];
		*/
        $q = "
        SELECT u.Firstname AS Firstname, u.Lastname AS Lastname, u.Email AS Email,
        	   uf.JobTitle AS JobTitle, uf.Department AS Department, uf.JobStatus AS JobStatus 
        FROM tblUsers AS u, tblUserFacility AS uf, tblUserGroups AS ug, tblOrganizations AS org
        WHERE uf.UserID = u.UserID AND ug.UserID = u.UserID AND u.UserID = " . $_GET["id"];
        
        $row = mssql_query($q);
        $user = mssql_fetch_assoc($row);
        
        echo json_encode($user);
    } else {
        echo json_encode("No user ID supplied");
    }
?>


<?php
    /*
       Registers a student for an online or classroom course.
       Requires user ID, session ID (0 for online courses), user's group ID, and course ID
       Andrew Park - 20/07/2016
    */ 
    // Allow cross-origin requests
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        //header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        exit(0);
    }
    require "config.php";

    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($db_name, $conn)
        or die("Could not select CCRS db");
    $postdata = json_decode(file_get_contents("php://input"), true);
    
    if (isset($postdata["user"]) && isset($postdata["session"]) && isset($postdata["course"])) {
        $q = "
            DECLARE @now DateTime
            SET @now = GETDATE()
        
            EXEC CCRSQA.dbo.RegisterStudent @CourseID = " . $postdata["course"] . ", @StartDate = @now, @SessionID = " . $postdata["session"] . ", @UserID = " . $postdata["user"] . ", @UpdatedBy = " . $postdata["user"] . ", 
                                            @ModifyDate = @now, @Status = 0, @strStatus = 'Confirmed', @GroupID = 1, @RegisterID = NULL, @Fee = 0.00, @UserCostCentre = NULL 
        ";
        $result = mssql_query($q);

        echo $q;
    } else {
        echo "No data supplied: " . $postdata["user"] . " " . $postdata["session"] . " " . $postdata["course"];
    }
?>


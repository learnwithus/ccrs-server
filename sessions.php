<?php
    /*
        Retrieves available classroom sessions for a course, given the course ID
        Client must determine whether the session is full by evaluating NumEnrolled == MaxCapacity
        Andrew Park - 20/07/2016
    */
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $list_sessions = array();

    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($db_name, $conn)
        or die("Could not select CCRS db");

    if (isset($_GET["id"])) {
        $q = "
            DECLARE @sid INTEGER
            DECLARE @num INTEGER
            DECLARE @now DateTime
            SET @now = GETDATE()

            SELECT SessionID, StartDate, Location, MaxCapacity INTO #Tmp
            FROM tblSessions
            WHERE StartDate > @now AND Status = 1 AND CourseID = " . $_GET["id"] . 

            "
            ALTER TABLE #Tmp ADD NumEnrolled INTEGER
            
            DECLARE C CURSOR
                LOCAL STATIC READ_ONLY FORWARD_ONLY
            FOR
                SELECT SessionID FROM tblSessions WHERE StartDate > @now AND Status = 1 AND CourseID = " . $_GET["id"] .
            "
            OPEN C
            FETCH NEXT FROM C INTO @sid 
            WHILE @@FETCH_STATUS = 0 BEGIN
                SET @num = (SELECT COUNT(*) FROM tblRegisterlist WHERE SessionID = @sid)
                UPDATE #Tmp SET NumEnrolled = @num WHERE SessionID = @sid 
                FETCH NEXT FROM C INTO @sid
            END
            CLOSE C

            SELECT * FROM #Tmp
            ";

        $result = mssql_query($q);

        while ($row = mssql_fetch_assoc($result)) {
            array_push($list_sessions, $row);
        }

        echo json_encode($list_sessions);
    } else {
        echo "No course ID supplied";
    }
?>


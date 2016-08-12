<?php
    /*
       Registers a student for an online or classroom course.
       Requires user ID, session ID (0 for online courses), and course ID
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
    require 'phpmailer/PHPMailerAutoload.php';

    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($db_name, $conn)
        or die("Could not select CCRS db");
    $postdata = json_decode(file_get_contents("php://input"), true);
    
    if (isset($postdata["user"]) && isset($postdata["session"]) && isset($postdata["course"])) {
        $q = "
            DECLARE @now DateTime
            SET @now = GETDATE()
        
            EXEC CCRS.dbo.RegisterStudent @CourseID = " . $postdata["course"] . ", @StartDate = @now, @SessionID = " . $postdata["session"] . ", @UserID = " . $postdata["user"] . ", @UpdatedBy = " . $postdata["user"] . ", 
                                            @ModifyDate = @now, @Status = 0, @strStatus = 'Confirmed', @GroupID = 1, @RegisterID = NULL, @Fee = 0.00, @UserCostCentre = NULL 
        ";
        $result = mssql_query($q);

        $q = "
            SELECT u.FirstName, u.LastName, u.Email, s.StartDate, s.Location, c.Title, c.CourseManagerID
            FROM tblUsers AS u, tblSessions AS s, tblCourses AS c
            WHERE c.CourseID = " . $postdata["course"] . " AND s.SessionID = " . $postdata["session"] . " AND u.UserID = " . $postdata["user"];
        $result = mssql_query($q);
        $course_info = mssql_fetch_assoc($result);

        $q = "
            SELECT u.FirstName, u.LastName, u.Email 
            FROM tblUsers AS u, tblCourseManagers AS cm
            WHERE cm.CourseManagerID = " . $course_info["CourseManagerID"] . " AND cm.UserID = u.UserID";
        $result = mssql_query($q);
        $manager_info = mssql_fetch_assoc($result);

        $mailer = new PHPMailer();
    
        $mailer->isSMTP();
        $mailer->Host = "207.34.128.233";
        
        $mailer->setFrom("learnwithus@vch.ca", "learnwithus@vch.ca");
        $mailer->Subject = 'CCRS - Confirmation';

        $username = $course_info['FirstName'] . ' ' . $course_info['LastName'];
        $datetime = explode(" ", $course_info['StartDate']);
        $date = $datetime[0] . ' ' . $datetime[1] . ', ' . $datetime[2];
        $fulltime = explode(":", $datetime[3]);
        $time = $fulltime[0] . ':' . $fulltime[1] . substr($fulltime[3], 3);
        $title = $course_info['Title'];
        $location = $course_info['Location'];
        $manager = $manager_info['FirstName'] . ' ' . $manager_info['LastName'];
        $manager_email = $manager_info['Email'];
/*
        $mailer->Body = 
    'Dear ' . $username . ',

Your registration is confirmed for the following course: 
    <table>
        <tr>
            <td>Course Title:</td>
            <td>' . $title . '</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td>' . $date . '</td>
        </tr>
        <tr>
            <td>Time:</td>
            <td>' . $time . '</td>
        </tr>
        <tr>
            <td>Location:</td>
            <td>' . $location . '</td>
        </tr>
        <tr>
            <td>Course Manager:</td>
            <td>' . $manager . '</td>
        </tr>
        <tr>
            <td>Email:</td>
            <td>' . $manager_email . '</td>
        </tr>
    </table>

You can reschedule or cancel course registration yourself up to 48 hours before the course start by logging into http://ccrs.vch.ca.
Please contact the course manager if it is less than 48 hours before the course start date. 
    ';
*/
    $mailer->Body = 
    "Dear " . $username . ",\n

Your registration is confirmed for the following course:\n

Course Title: " . $title . "\n
Date: " . $date . "\n
Time: " . $time . "\n
Location: " . $location . "\n
Course Manager: " . $manager . "\n
Email: " . $manager_email . "\n

You can reschedule or cancel course registration yourself up to 48 hours before the course start by logging into http://ccrs.vch.ca.
Please contact the course manager if it is less than 48 hours before the course start date. 
    ";

    $mailer->addAddress($course_info['Email'], "User");
    
    if (!$mailer->Send())
        echo $mailer->ErrorInfo;
    else
        echo "Sent";

    } else {
        echo "No data supplied: " . $postdata["user"] . " " . $postdata["session"] . " " . $postdata["course"];
    }
?>


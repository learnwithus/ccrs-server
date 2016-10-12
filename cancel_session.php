<?php
    /*
       Cancels a classroom session given the RegisterID and UserID
       Andrew Park - 07/09/2016
    */
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
    }
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

    if (isset($postdata["user"]) && isset($postdata["register"]) && isset($postdata["session"]) && isset($postdata["course"])) {
        
        mssql_query("EXEC CCRS.dbo.CancelRegistration @RegisterID = " . $postdata["register"] . ", @UpdatedBy = " . $postdata["user"]);

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
        
        $username = $course_info['FirstName'] . ' ' . $course_info['LastName'];
        $email = $course_info['Email'];
        $read_datetime = explode(" " ,$course_info['StartDate']);
        $read_date = $read_datetime[0] . ' ' . $read_datetime[1] . ', ' . $read_datetime[2];
        $fulltime = explode(":", $read_datetime[3]);
        $read_time = $fulltime[0] . ':' . $fulltime[1] . substr($fulltime[3], 3);
        $title = $course_info['Title'];
        $location = $course_info['Location'];
        $manager = $manager_info['FirstName'] . ' ' . $manager_info['LastName'];
        $manager_email = $manager_info['Email'];

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = "207.34.128.233";
        $mail->setFrom("learnwithus@vch.ca", "learnwithus@vch.ca");
        $mail->addAddress($email,'User');
        $mail->Subject = "CCRS - Cancellation";

        $mail->Body = 
    "Dear " . $username . ",\n
Your registration has been cancelled for the following course:\n
Course Title: " . $title . "
Date: " . $read_date . "
Time: " . $read_time . "
Location: " . $location . "\n
Course Manager: " . $manager . "
Email: " . $manager_email . "\n";

        //send the message, check for errors
        if (!$mail->send()) {
            echo $mail->ErrorInfo . $course_info["StartDate"] . ", " . $manager_info["FirstName"] . ", " . $postdata["register"] . ", " . $postdata["user"];
        } else {
            echo "Successfully unregistered and email sent";
        }
        
    } else {
        echo "No RegisterID, UserID, CourseID, or SessionID supplied";
    }
?>


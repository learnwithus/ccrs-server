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

        $username = $course_info['FirstName'] . ' ' . $course_info['LastName'];
        $email = $course_info['Email'];
        $read_datetime = explode(" ", $course_info['StartDate']);
        $read_date = $read_datetime[0] . ' ' . $read_datetime[1] . ', ' . $read_datetime[2];
        $fulltime = explode(":", $read_datetime[3]);
        $read_time = $fulltime[0] . ':' . $fulltime[1] . substr($fulltime[3], 3);
        $title = $course_info['Title'];
        $location = $course_info['Location'];
        $manager = $manager_info['FirstName'] . ' ' . $manager_info['LastName'];
        $manager_email = $manager_info['Email'];

        $datetime = strtotime($course_info['StartDate']);
        $date = date('Ymd', $datetime);
        $time = date('His', $datetime);
        $end_datetime = strtotime($course_info['EndDate']);
        $end_date = date('Ymd', $end_datetime);
        $end_time = date('His', $end_datetime);
		$event_id = $course_info['CourseID'];
		$sequence = 0;
		$status = 'CONFIRMED';

		$mail = new PHPMailer();
		$mail->isSMTP();
		$mail->Host = "207.34.128.233";
		$mail->setFrom("learnwithus@vch.ca", "learnwithus@vch.ca");
		$mail->addAddress($email,'User');
		$mail->Subject = "CCRS - Confirmation";
		$mail->addCustomHeader('MIME-version',"1.0"); 
	    $mail->addCustomHeader('Content-type',"text/calendar; name=event.ics; method=REQUEST; charset=UTF-8;"); 
	    $mail->addCustomHeader('Content-type',"text/html; charset=UTF-8"); 
	    $mail->addCustomHeader('Content-Transfer-Encoding',"7bit"); 
	    $mail->addCustomHeader('X-Mailer',"Microsoft Office Outlook 12.0"); 
	    $mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

		$ical = "BEGIN:VCALENDAR\r\n";
		$ical .= "VERSION:2.0\r\n";
		$ical .= "PRODID:-//YourCassavaLtd//EateriesDept//EN\r\n";
		$ical .= "METHOD:REQUEST\r\n";
		$ical .= "BEGIN:VEVENT\r\n";
		$ical .= "ORGANIZER;SENT-BY=\"MAILTO:learnwithus@vch.ca\":MAILTO:learnwithus@vch.ca\r\n";
		// $ical .= "ATTENDEE=".$email.";CN=;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=TRUE:mailto:learnwithus@vch.ca\r\n";
		$ical .= "UID:".strtoupper(md5($event_id))."\r\n";
		$ical .= "SEQUENCE:".$sequence."\r\n";
		$ical .= "STATUS:".$status."\r\n";
		$ical .= "DTSTAMPTZID=America/Los_Angeles:".date('Ymd').'T'.date('His')."\r\n";
		$ical .= "DTSTART:".$date."T".$time."\r\n";
		$ical .= "DTEND:".$end_date."T".$end_time."\r\n";
		$ical .= "LOCATION:".$location."\r\n";
		$ical .= "SUMMARY:".$course_info['Title']."\r\n";
		$ical .= "DESCRIPTION:".$course_info['Description']."\r\n";
		$ical .= "BEGIN:VALARM\r\n";
		$ical .= "TRIGGER:-PT15M\r\n";
		$ical .= "ACTION:DISPLAY\r\n";
		$ical .= "DESCRIPTION:Reminder\r\n";
		$ical .= "END:VALARM\r\n";
		$ical .= "END:VEVENT\r\n";
		$ical .= "END:VCALENDAR\r\n";

    $mail->Body = 
    "Dear " . $username . ",\n
Your registration is confirmed for the following course:\n
Course Title: " . $title . "
Date: " . $read_date . "
Time: " . $read_time . "
Location: " . $location . "\n
Course Manager: " . $manager . "
Email: " . $manager_email . "\n
To add your registered course to your outlook calendar, please double click the CCRS Calendar Entry attachment.
You can reschedule or cancel course registration yourself up to 48 hours before the course start by logging into http://ccrs.vch.ca.
Please contact the course manager if it is less than 48 hours before the course start date. 
    ";
		$mail->AddStringAttachment($ical, "CCRS_Calendar_Entry.ics", "7bit", "text/calendar; charset=utf-8; method=REQUEST");

		//send the message, check for errors
		if (!$mail->send()) {
			echo $mail->ErrorInfo;
		} else {
			echo "Message sent!";
		}
    } else {
        echo "No data supplied: " . $postdata["user"] . " " . $postdata["session"] . " " . $postdata["course"];
    }
?>


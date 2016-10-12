<?php
    /*
       Retrieves a CCRS user profile from the user ID
       Andrew Park - 20/07/2016
    */ 
    header('Access-Control-Allow-Origin: *'); 
    require "config.php";
    require 'phpmailer/PHPMailerAutoload.php';
    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to MSSQL");
    
    $selected = mssql_select_db($db_name, $conn) 
        or die("Could not select CCRS db");
    
    if (true) {
        $q = "
            SELECT u.FirstName, u.LastName, u.Email, s.StartDate, s.EndDate, s.Location, c.Title, c.CourseManagerID, c.Description, c.CourseID
            FROM tblUsers AS u, tblSessions AS s, tblCourses AS c
            WHERE c.CourseID = " . $_POST["course"] . " AND s.SessionID = " . $_POST["session"] . " AND u.UserID = " . $_POST["user"];
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
		$datetime = strtotime($course_info['StartDate']);
        $date = date('Ymd', $datetime);
        $time = date('His', $datetime);
        $end_datetime = strtotime($course_info['EndDate']);
        $end_date = date('Ymd', $end_datetime);
        $end_time = date('His', $end_datetime);
        $title = $course_info['Title'];
        $location = $course_info['Location'];
        $manager = $manager_info['FirstName'] . ' ' . $manager_info['LastName'];
        $manager_email = $manager_info['Email'];
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
Location: " . $location . "
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
        echo json_encode("No user ID supplied");
    }
?>



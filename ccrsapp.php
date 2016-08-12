<?php
    /*
        Automatically logs user into Moodle, given username and password
        Andrew Park - 20/07/2016
    */

    echo '
		<!DOCTYPE html>
		<html lang="en">
		<head>
		    <meta charset="utf-8">
		    <meta name="viewport" content="width=device-width, initial-scale=1">
		    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300" rel="stylesheet" type="text/css">
			<style>
		      html { 
		      	background-color: #11729f;
		      }

		      h1 {
		      	margin-top: 50px;
		      	font-size: 96px;
		      	line-height: 96px;
		      	font-family: "Times New Roman", "Georgia", Times, serif;
		      	font-weight: normal;
		      	margin-bottom: 0;
		      }

		      h3 {
		      	font-size: 36px;
		      	font-family: "Open Sans", sans-serif;
		      }

		      h1,
		      h3 {
		      	color: #fff;
		      	padding: 0 25px 0 25px; 
		      }
		      p {
		      	font-family: "Open Sans", sans-serif;
		      	text-align: left;
		      	color: #fff;
		      	font-size: 36px;
		      	padding: 0 25px 0 25px;
		      }
		    </style>
		</head>
    ';


    echo 
        '<form style="display: none;" action="../../login/ccrsapp_moodle.php" method="post" id="login">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" value="'; echo $_GET['username']; echo '"/>
          <label for="password">Password</label>
          <input type="password" name="password" id="password" value="'; echo $_GET['password']; echo '"/>
          <input type="text" name="vchcid" id="vchcid" value="'; echo $_GET['course_id']; echo '"/>
          <input type="submit" id="loginbtn" value="Log in" />
        </form>';

    echo '
    	    <h1>CCRS</h1>
    	    <h3>Course Catalogue Registration System</h3>
    	    <p>Starting course<br>Please wait...</p>
    	';

    if(isset($_GET['username']) && isset($_GET['password'])) {
        echo "
            <script type=\"text/javascript\">
                document.getElementById('login').submit();
            </script>";
    }
?>

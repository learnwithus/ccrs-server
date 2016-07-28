<?php
    /*
        Automatically logs user into Moodle, given username and password
        Andrew Park - 20/07/2016
    */
    echo "<style>
      html { 
      	background-color: #11729f;
      }

      img {
      	width: 100%;
      }
    </style>";
    
    echo 
        '<form style="display: none;" action="../../login/ccrsapp_moodle.php" method="post" id="login">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" value="'; echo $_GET['username']; echo '"/>
          <label for="password">Password</label>
          <input type="password" name="password" id="password" value="'; echo $_GET['password']; echo '"/>
          <input type="text" name="vchcid" id="vchcid" value="'; echo $_GET['course_id']; echo '"/>
          <input type="submit" id="loginbtn" value="Log in" />
        </form>';

    echo '<img src="loading.png"/>';

    if(isset($_GET['username']) && isset($_GET['password'])) {
        echo "
            <script type=\"text/javascript\">
                document.getElementById('login').submit();
            </script>";
    }
?>

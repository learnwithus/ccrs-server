<?php
    echo 
        '<form style="display: none;" action="https://webedpm.com/~nomonke1/park/moodle/login/ccrsapp.php" method="post" id="login">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" value="'; echo $_GET['username']; echo '"/>
          <label for="password">Password</label>
          <input type="password" name="password" id="password" value="'; echo $_GET['password']; echo '"/>
          <input type="text" name="vchcid" id="vchcid" value="'; echo $_GET['course_id']; echo '"/>
          <input type="submit" id="loginbtn" value="Log in" />
        </form>';

    echo '<h1 style="font-size: 64px;">Starting the course please wait...</h1>';

    if(isset($_GET['username']) && isset($_GET['password'])) {
        echo "
            <script type=\"text/javascript\">
                document.getElementById('login').submit();
            </script>";
    }
?>

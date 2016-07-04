<?php
    function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $list_sessions = array();
    $conn = mysql_connect($host, $username, $password)
        or die("Unable to connect to MySQL");

    $selected = mysql_select_db($db_name, $conn)
        or die("Could not select CCRS db");

    if (isset($_POST["user"]) && isset($_POST["session"])) {
        mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $conn);
        $last_pk = mysql_query(
            "SELECT RegisterID FROM tblRegisterlist
            ORDER BY column_name DESC
            LIMIT 1;");
        $last_pk = mysql_fetch_assoc($last_pk)["RegisterID"];
        $result = mysql_query(
            "
            INSERT INTO tblRegisterlist
            VALUES ($last_pk, $_POST["session"], GroupID, $_POST["user"], 0, NOW(), 0.00, NULL, NOW(), 0, NOW(), 0, NULL) 
            ");

        while ($row = mysql_fetch_assoc($result)) {
            array_push($list_sessions, $row);
        }

        echo json_encode(utf8ize($list_sessions));
    } else {
        echo "No title supplied";
    }
?>


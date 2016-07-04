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
    $list_sessions = array();

    $conn = mysql_connect('localhost', 'nomonke1_park', '123...Park')
        or die("Unable to connect to MySQL");

    $selected = mysql_select_db('nomonke1_ccrs', $conn)
        or die("Could not select CCRS db");

    if (isset($_GET["id"])) {
        mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $conn);

        $result = mysql_query("SELECT SessionID, StartDate, Location
            FROM tblSessions
            WHERE CourseID = " . $_GET["id"]);

        while ($row = mysql_fetch_assoc($result)) {
            array_push($list_sessions, $row);
        }

        echo json_encode(utf8ize($list_sessions));
    } else {
        echo "No title supplied";
    }
?>


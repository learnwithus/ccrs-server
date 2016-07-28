<?php
    /*
        Retrieves training history for a user, given user ID
        Andrew Park - 20/07/2016
    */
    header('Access-Control-Allow-Origin: *');
    require "config.php";
    $list_courses = array();

    $conn = mssql_connect($host, $username, $password)
        or die("Unable to connect to mssql");

    $selected = mssql_select_db($db_name, $conn)
        or die("Could not select CCRS db");

    if (isset($_GET["user"])) {
      $history = array(); 
      $options = 0;
      $user = $_GET['user'];
      if(isset($_GET['options']))
        $options = $_GET['options'];
        
      //query for temp table t
      $temp_t="CREATE TABLE #T (
              ID                INT IDENTITY(1,1) NOT NULL,
              CourseId          INT,
              Title             NVARCHAR(100),
              CompletedDate     DATETIME,
              Grade             NVARCHAR(20),
              Result                  NVARCHAR(50),
              CourseLength      float,
              SortingID         VARCHAR(20),
              PrintCertificate BIT,
              Parameters        VARCHAR(300)
        );";
      
      mssql_query($temp_t);
      
      //query for temp table t1
      $temp_t1="CREATE TABLE #T1 (
              ID                      INT IDENTITY(1,1) NOT NULL,
              CourseId          INT,
              SortingID         VARCHAR(20)
        );";
      
      mssql_query($temp_t1);
      
      //grab neccessary table information from ccrs tables
      $select_t="   SELECT      c.CourseId,
                    Title             = ltrim(c.Title),
                    CompletedDate     = b.EndDate,
                    Grade             = RTRIM(ISNULL(a.Grade, '')),
                    Result                  = d.sValue,
                    c.CourseLength,
                    PrintCertificate = CASE
                                                  WHEN c.RecordOfCompletion = 0 THEN 0
                                                  WHEN b.EndDate < '2011-01-01' THEN 0
                                                  WHEN Result IN ( 3, 6 ) THEN 1 ELSE 0 END,
                    Parameters        = CASE
                                                  WHEN c.RecordOfCompletion = 0 THEN ''
                                                  ELSE CONVERT(VARCHAR(12), $user) + '|' + 
                                                        CONVERT(VARCHAR(12), c.CourseId) +  '|' + 
                                                        CONVERT(VARCHAR(10), b.EndDate, 11 ) +  '|' + 
                                                        CASE WHEN ISNULL(a.Grade, '') = '' THEN d.sValue ELSE a.Grade END
                                                  END
                                            
        FROM  tblCourseGrading a     
        JOIN  tblSessions             b     ON a.SessionID= b.SessionID
        JOIN  tblCourses              c     ON b.CourseID = c.CourseID
        JOIN  tblOrganizations  e     ON c.OrgId = e.OrgId,
                    tblConstants            d
        WHERE a.UserID = $user
        AND         d.nType = 1
        AND         d.nKey = a.Result
        UNION
        SELECT      b.CourseId,
                    ltrim(b.Title),
                    CompletionDate,
                    Result,
                    '',
                    0.0,
                    PrintCertificate = CASE
                                                  WHEN b.RecordOfCompletion = 0 THEN 0
                                                  WHEN CompletionDate < '2011-01-01' THEN 0
                                                  ELSE [dbo].[udfPrintCertificate] ( b.CourseId, CompletionDate, Result ) END,
                    Parameters        = CASE
                                                  WHEN b.RecordOfCompletion = 0 THEN ''
                                                  ELSE CONVERT(VARCHAR(12), $user) + '|' + 
                                                        CONVERT(VARCHAR(12), b.CourseId) +  '|'  + 
                                                        CONVERT(VARCHAR(10), a.CompletionDate, 11 ) +  '|' + 
                                                        Result
                                                  END
        FROM  tblOnlineCourseResults  a
        JOIN  tblCourses              b     ON a.CourseId = b.CourseId
        JOIN  tblOrganizations  c     ON b.OrgId = c.OrgId
        WHERE a.UserId = $user
        ORDER BY    CompletedDate DESC";
          
      $rs = mssql_query($select_t);
      
      while($row = mssql_fetch_array($rs)){
        //insert into t temp table for later use
        $insert_t="INSERT INTO #T (CourseId, Title, CompletedDate, Grade, Result, CourseLength, SortingID, PrintCertificate, Parameters) VALUES (".$row['CourseId'].", \"".$row['Title']."\", '".$row['CompletedDate']."', '".$row['Grade']."', '".$row['Result']."', ".$row['CourseLength'].", '',".$row['PrintCertificate'].", '".$row['Parameters']."')";
        mssql_query($insert_t);
      }
      
      mssql_free_result($rs);   
      
      //get information for t1 table
      $select_t1 = "SELECT CourseId, CAST(MIN(ID) AS VARCHAR(20)) FROM #T GROUP BY CourseId";
      $rs_t1 = mssql_query($select_t1);
      
      while($row = mssql_fetch_array($rs_t1)){
          //insert into t1 table. SortingID is min of id group by course id
          $insert_t1 = "INSERT #T1 (CourseId, SortingID) VALUES (".$row['CourseId'].", ".$row['computed'].")";
          mssql_query($insert_t1);
      }
      
      mssql_free_result($rs_t1);    
      
      //update SortingID for table t 
      $update = "UPDATE a
        SET         SortingID = b.SortingID
        FROM  #T    a
        JOIN  #T1   b ON a.CourseId = b.CourseId";
      mssql_query($update);
      
      //mark any grades from the same course
      $update = "UPDATE      a
        SET         SortingID = a.SortingID + '.1'
        FROM  #T    a
        LEFT JOIN   #T1   b ON a.ID = CAST( b.SortingID AS INT ) WHERE b.ID IS NULL";
      mssql_query($update);
      
      $f_query = '';
      
      //get completed information ordered by date or title
      if($options==0){
          $f_query = "SELECT      CourseId,
                          Title,
                          CONVERT(VARCHAR(10), CompletedDate, 11) as CompletedDate,
                          Grade,
                          Result,
                          CourseLength = round(CourseLength, 2),
                          SortingId = 'R'+ SortingId,
                          SortOrder = CASE WHEN RIGHT(SortingId, 2) <> '.1' then SortingId + '.2' ELSE SortingId END,
                          PrintCertificate = case PrintCertificate when 1 then '' else 'DISPLAY: none' end,
                          Parameters
              FROM  #T
              ORDER BY    CompletedDate DESC";
      
      }else{
          $f_query = "SELECT      CourseId,
                          Title,
                          CONVERT(VARCHAR(10), CompletedDate, 11) as CompletedDate,
                          Grade,
                          Result,
                          CourseLength = round(CourseLength, 2),
                          SortingId = 'R'+ SortingId,
                          PrintCertificate = case PrintCertificate when 1 then '' else 'DISPLAY: none' end,
                          Parameters
              FROM  #T
              ORDER BY    Title ASC";
      
      }
      
      $final_rs = mssql_query($f_query);
      
      while($row = mssql_fetch_assoc($final_rs)){
        array_push($history,$row);
      } 
      
      mssql_free_result($final_rs); 
      
      $cert_pass = mssql_query("SELECT CourseID, PassingGrade FROM tblOnlineCourses");
      
      $passing_grade = array();
      
      while($row = mssql_fetch_array($cert_pass)){
        $passing_grade[$row["CourseID"]] = $row["PassingGrade"];
      } 
      
      mssql_free_result($cert_pass);    
                  
      mssql_close($db_handle);
    
      echo json_encode($history);
    } else {
        echo "No user supplied";
    }
?>


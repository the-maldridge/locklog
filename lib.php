<?php
function dblink($DBHOST, $DBUSER, $DBPASS, $DBNAME) {

  $DBCON = mysql_connect($DBHOST, $DBUSER, $DBPASS, $DBNAME);

  if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: " . mysqli_connect_error()';
    echo '<br />No use continuing without the database server...';
    die();
  } 
  
  if(!mysql_query("USE $DBNAME")) {
    die("did not select database " . mysql_error());
  }

  return $DBCON;
}

function addLockout($LOGTABLE, $DBCON, $PA_name, $bldg, $res_name, $res_id) {
  $SQL="INSERT INTO $LOGTABLE (PA_name, bldg, res_name, res_id) VALUES ('$PA_name', '$bldg', '$res_name', '$res_id')";
  if(!mysql_query($SQL, $DBCON)) {
    echo 'Failed to commit log entry, please use alternative log';
    die("Report this to the admin: " . mysql_error());
  }
}

function chkPast($HISTTABLE, $DBCON, $res_id) {
  $SQL="SELECT * FROM $HISTTABLE WHERE `res_id`=$res_id";

  if(!($result=mysql_query($SQL, $DBCON))) {
    die("A serious error has occured, report this: ".mysql_error());
  } else {
    if(mysql_num_rows($result)>0) {
      return true;
    } else {
      return false;
    }
  }
}

function addToHistory($HISTTABLE, $DBCON, $res_id) {
  $SQL = "INSERT INTO $HISTTABLE (res_id, total_max, local_max) VALUES ('$res_id', 1, 1)";

  if(!mysql_query($SQL, $DBCON)) {
    die("Could not add to history database: ".mysql_error());
  }
}

function updateHistory($HISTTABLE, $DBCON, $res_id) {
  $SQL="SELECT * FROM $HISTTABLE WHERE `res_id`=$res_id";

  if(!($result=mysql_query($SQL, $DBCON))) {
    die("A serious error has occured, report this: ".mysql_error());
  } else {
    $row=mysql_fetch_array($result);
    $tmax=$row["total_max"]+1;
    $lmax=$row["local_max"]+1;
    
    $SQL='UPDATE '.$HISTTABLE.' SET total_max='.$tmax.', local_max='.$lmax.' WHERE `index`='.$row["index"];
    mysql_query($SQL, $DBCON);

    return $lmax;
  }
}

function getConfig($path) {
  $file = fopen($path, 'r');
  if($file == false) {
    die("Failed to get config");
  }
  $json = fread($file, filesize($path));
  $data = json_decode($json, true);
  return $data;
}


function emailRLC($RLC, $RLC_email, $resident, $res_id, $SERVER, $PATH, $EMAILSUBJECT) {
  $message=$RLC.", you are recieving this automated notice because ".$resident." (".$res_id.") has exceeded the threshold for lockouts.";
  $message=$message."\nYou may click on the link below to reset this resident's lockout meeting counter, the global count will be preserved.";
  $resetURL="http://".$SERVER."/".$PATH."index.php?reset=".$res_id;
  $message=$message."\n".$resetURL;

  $message=wordwrap($message,70);

  //actually send the message
  if(!mail($RLC_email, $EMAILSUBJECT, $message, "From: noreply")) {
    echo "A mailer issue was encountered, please report this to the admin.";
  }

}
function getBuilding($config, $prompt) {
  if(!isset($_COOKIE["bldg"]) || $prompt) {
    echo '<td>Building</td><td><select id="bldgSelect" name="bldg">';
    echo '<option selected="selected" value=""></option>';
    foreach($config["buildings"] as $buildingKey => $buildingInfo) {
      echo '<option value="'.$buildingKey.'">'.$buildingInfo["disptext"].'</option>';
    }
    echo '</select></td>';
  } else {
    $bldg=$_COOKIE["bldg"];
    echo "<input name='bldg' type='hidden' value='$bldg'>";
  }
}

function resetCount($HISTTABLE, $DBCON, $res_id) {
    $SQL='UPDATE '.$HISTTABLE.' SET local_max=0 WHERE `res_id`='.$res_id;
    if(!mysql_query($SQL, $DBCON)) {
      die("Could not reset count, contact an administrator: ".mysql_error());
    } else {
      echo "Successful Reset";
    }
}  
?>
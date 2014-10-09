<?php
$SERVER="corespace.michaelwashere.tk";
$PATH="/lockout/";
$DBUSER="deskworker";
$DBPASS="foobar";
$DBHOST="localhost";
$DBNAME="locklog";
$LOGTABLE="lockouts";
$HISTTABLE="history";
$EMAILTHRESHOLD=4;

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
  } else {
    echo 'Commit Successful, redirecting to home...';
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

function csvToArray($fname) {
  if(!file_exists($fname) || !is_readable($fname)) {
    return false;
  }

  $data=null;
  if(($handle=fopen($fname, 'r')) !== false) {
    while(($row=fgetcsv($handle, 1000, ',')) !== false) {
      $data[$row[0]]["name"]=$row[1];
      $data[$row[0]]["email"]=$row[2];
    }
    fclose($handle);
  }
  return $data;
}

function getRLC($bldg) {
  $rlcs=csvToArray("rlcs.csv");
  return $rlcs[$bldg];
}

function emailRLC($RLC, $resident, $res_id, $SERVER, $PATH) {
  $message=$RLC["name"].", you are recieving this automated notice because ".$resident." (".$res_id.") has exceeded the threshold for lockouts.";
  $message=$message."\nYou may click on the link below to reset this resident's lockout meeting counter, the global count will be preserved.";
  $resetURL="http://".$SERVER."/".$PATH."index.php?reset=".$res_id;
  $message=$message."\n".$resetURL;
  echo $message;
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

<html>
<head>
<title>Lockout Log</title>
</head>
<body>

<?php

if(!empty($_GET["reset"])) {
  //quick override to reset a count
  $res_id=$_GET["reset"];
  $DBCON=dblink($DBHOST, $DBUSER, $DBPASS, $DBNAME);
  resetCount($HISTTABLE, $DBCON, $res_id);
  mysql_close($DBCON);
  die(); //die because we don't want to parse anything else
}

if(!empty($_POST["formState"])) {
  $formState=$_POST["formState"];
}

if(empty($formState) || $formState=="Reset") {
  //if the form is empty or reset, show the initial page
  echo '<form action="index.php" method="post">';
  echo '<table>';

  //name
  echo '<tr><td>PA Name</td><td><input name="PA_name" type="text"></td></tr>';

  //building
  echo '<tr><td>Building</td><td><select name="bldg">';
  $buildings=file("buildings.txt");
  foreach($buildings as $bldg => $buildingName) {
    $buildingName=trim($buildingName);
    echo '<option value="'.$buildingName.'">'.$buildingName.'</option>';
  }
  echo '</select></td></td>';

  //resident info
  echo '<tr><td>Resident Name</td><td><input name="res_name" type="text"></td></tr>';
  echo '<tr><td>Resident ID#</td><td><input name="res_id" type="text"></td></tr>';

  echo '<tr><td colspan="2"><center><input type="submit" name="formState" value="continue"></center></td></tr>';
  echo '</table>';
}

if(!empty($formState) && $formState=="continue") {
  //if things have been entered, confirm and continue
  $PA_name=$_POST["PA_name"];
  $bldg=$_POST["bldg"];
  $res_name=$_POST["res_name"];
  $res_id=$_POST["res_id"];

  echo 'Are you ' . $PA_name . ' performing a lockout for ' . $res_name . '?';

  //form to grab the values before sending them back
  echo '<form action="index.php" method="post">';
  echo '<input name="PA_name" type="hidden" value="'.$PA_name.'">';
  echo '<input name="bldg" type="hidden" value="'.$bldg.'">';
  echo '<input name="res_name" type="hidden" value="'.$res_name.'">';
  echo '<input name="res_id" type="hidden" value="'.$res_id.'">';
  echo '<input name="formState" type="hidden" value="submit">';
  echo '<input type="submit" value="Confirm">';
  echo '<input type="submit" name="formState" value="Reset">';
}

if(!empty($formState) && $formState=="submit") {
  //data has been verified, time to submit

  $PA_name=$_POST["PA_name"];
  $bldg=$_POST["bldg"];
  $res_name=$_POST["res_name"];
  $res_id=$_POST["res_id"];

  //link to the database
  $DBCON=dblink($DBHOST, $DBUSER, $DBPASS, $DBNAME);

  //add that a lockout has occured
  addLockout($LOGTABLE, $DBCON, $PA_name, $bldg, $res_name, $res_id);
  if(chkPast($HISTTABLE, $DBCON, $res_id)) {
    $lockoutnum=updateHistory($HISTTABLE, $DBCON, $res_id);
    if($lockoutnum>$EMAILTHRESHOLD) {
      if(($RLC=getRLC($bldg))==false) {
	echo "Could not load RLC information, please contact an admin.";
	echo "Additionally inform the RLC that this is the ".$lockoutnum." lockout for this resident";
      } else {
	echo "This is lockout #".$lockoutnum." for ".$res_name.", ".$RLC["name"]." will been emailed.";
	emailRLC($RLC,$res_name, $res_id, $SERVER, $PATH); 
      }
    }
  }

  mysql_close($DBCON);

  //echo '<meta http-equiv="refresh" content="3">';
}
?>
</body>
</html>
<?php
require("lib.php");
require("config.php");
?>

<html>
<head>
<title>Lockout Log</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div id="outer">
<div id="inner">
<div id="content" style="width:400px;">
<center>
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

$config = getConfig("buildings.json");

if(empty($formState) || $formState=="Reset") {
  //if the form is empty or reset, show the initial page
  echo "<form action='index.php' method='post'>";
  echo "<table>";

  //name
  echo "<tr><td>PA Name</td><td><input name='PA_name' type='text'></td></tr>";

  //building
  echo '<tr>';
  getBuilding($config, false);
  echo '</tr>';

  //resident info
  echo "<tr><td>Resident Name</td><td><input name='res_name' type='text'></td></tr>";
  echo "<tr><td>Resident ID#</td><td><input name='res_id' type='text'></td></tr>";

  echo "<tr><td colspan='2'><center><input type='submit' name='formState' value='continue'></center></td></tr>";
  echo "</table>";
}

if(!empty($formState) && $formState=="continue") {
  //if things have been entered, confirm and continue
  $PA_name=$_POST["PA_name"];
  $bldg=$_POST["bldg"];
  $res_name=$_POST["res_name"];
  $res_id=$_POST["res_id"];

  //set persistent building identifier
  setcookie("bldg", $bldg, time() + (86400 * 30), "/");

  echo "Are you $PA_name performing a lockout for $res_name?";

  //form to grab the values before sending them back
  echo "<form action='index.php' method='post'>";
  echo "<input name='PA_name' type='hidden' value='$PA_name'>";
  echo "<input name='bldg' type='hidden' value='$bldg'>";
  echo "<input name='res_name' type='hidden' value='$res_name'>";
  echo "<input name='res_id' type='hidden' value='$res_id'>";
  echo "<input name='formState' type='hidden' value='submit'>";
  echo "<input type='submit' value='Confirm'>";
  echo "<input type='submit' name='formState' value='Reset'>";
}

if(!empty($formState) && $formState=="submit") {
  //data has been verified, time to submit

  //link to the database
  $DBCON=dblink($DBHOST, $DBUSER, $DBPASS, $DBNAME);

  $PA_name=mysql_real_escape_string($_POST["PA_name"]);
  $bldg=mysql_real_escape_string($_POST["bldg"]);
  $res_name=mysql_real_escape_string($_POST["res_name"]);
  $res_id=mysql_real_escape_string($_POST["res_id"]);

  //add that a lockout has occured
  addLockout($LOGTABLE, $DBCON, $PA_name, $bldg, $res_name, $res_id);
  if(chkPast($HISTTABLE, $DBCON, $res_id)) {
    $lockoutnum=updateHistory($HISTTABLE, $DBCON, $res_id);
    if($lockoutnum>$EMAILTHRESHOLD) {
      if((($RLC=$config["buildings"][$bldg]["coord"]) && ($RLC_email=$config["buildings"][$bldg]["coordemail"]))==false) {
	echo "Could not load RLC information, please contact an admin.";
	echo "Additionally inform the RLC that this is the ".$lockoutnum." lockout for this resident";
      } else {
	echo "This is lockout #".$lockoutnum." for ".$res_name.", ".$RLC." will be emailed.";
	emailRLC($RLC, $RLC_email, $res_name, $res_id, $SERVER, $PATH, $EMAILSUBJECT); 
      }
    } else {
      echo "Lockout Recorded, redirecting to main page...";
    }
  } else {
    addToHistory($HISTTABLE, $DBCON, $res_id);
    echo "Lockout Recorded, redirecting to main page...";
  }

  mysql_close($DBCON);

  //  echo '<meta http-equiv="refresh" content="3">';
}
?>
</center>
</div>
</div>
</div>
<?php require("footer.php"); ?>
</body>
</html>
<?php
$LOGTABLE="lockouts";

function dblink() {
  $USERNAME="deskworker";
  $PASSWORD="foobar";
  $DBHOST="localhost";
  $DBNAME="locklog";

  $DBCON = mysql_connect($DBHOST, $USERNAME, $PASSWORD, $DBNAME);

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

function addLockout($PA_name, $bldg, $res_name, $res_id) {
  $SQL="INSERT INTO $LOGTABLE (PA_name, bldg, res_name, res_id) VALUES ('$PA_name', '$bldg', '$res_name', '$res_id')";
  if(!mysql_query($SQL, $DBCON)) {
    echo 'Failed to commit log entry, please use alternative log';
    die("Report this to the admin: " . mysql_error());
  } else {
    echo 'Commit Successful, redirecting to home...';
    echo '<meta http-equiv="refresh" content="3">';
  }
}

function chkPast($res_id) {
  $SQL="SELECT * FROM $HISTTABLE WHERE `res_id`=$res_id";
}
?>

<html>
<head>
<title>Lockout Log</title>
</head>
<body>

<?php
$formState=$_POST["formState"];

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
  $DBCON=dblink();

  //add that a lockout has occured
  addLockout($PA_name, $bldg, $res_name, $res_id);

  //check the lockouts table for this person
  if(chkPast($res_id))
  mysql_close($DBCON);
}
?>
</body>
</html>
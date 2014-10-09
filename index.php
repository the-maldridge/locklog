<?php
$USERNAME="deskworker";
$PASSWORD="foobar";
$DBHOST="localhost";
$DBNAME="locklog";
$DTNAME="lockouts";
?>

<html>
<head>
<title>Lockout Log</title>
</head>
<body>

<?php
$formState=$_POST["formState"];

if(empty($formState) || $formState=="Reset") {
  echo '<form action="index.php" method="post">';
  echo '<input name="PA_name" type="text">';
  echo '<input name="PA_id" type="text">';
  echo '<input name="res_name" type="text">';
  echo '<input name="res_id" type="text">';
  echo '<input type="submit" name="formState" value="continue">';
}

if(!empty($formState) && $formState=="continue") {
  $PA_name=$_POST["PA_name"];
  $PA_id=$_POST["PA_id"];
  $res_name=$_POST["res_name"];
  $res_id=$_POST["res_id"];

  echo 'Are you ' . $PA_name . ' performing a lockout for ' . $res_name . '?';

  echo '<form action="index.php" method="post">';
  echo '<input name="PA_name" type="hidden" value="'.$PA_name.'">';
  echo '<input name="PA_id" type="hidden" value="'.$PA_id.'">';
  echo '<input name="res_name" type="hidden" value="'.$res_name.'">';
  echo '<input name="res_id" type="hidden" value="'.$res_id.'">';
  echo '<input name="formState" type="hidden" value="submit">';
  echo '<input type="submit" value="Confirm">';
  echo '<input type="submit" name="formState" value="Reset">';
}

if(!empty($formState) && $formState=="submit") {
  $PA_name=$_POST["PA_name"];
  $PA_id=$_POST["PA_id"];
  $res_name=$_POST["res_name"];
  $res_id=$_POST["res_id"];

  $DBCON = mysql_connect($DBHOST, $USERNAME, $PASSWORD, $DBNAME);

  if (mysqli_connect_errno()) {
    echo 'Failed to connect to MySQL: " . mysqli_connect_error()';
    echo '<br />No use continuing without the database server...';
    die();
  } 
  
  if(!mysql_query("USE $DBNAME")) {
    die("did not select database " . mysql_error());
  }

  $SQL="INSERT INTO $DTNAME (PA_name, PA_id, res_name, res_id) VALUES ('$PA_name', '$PA_id', '$res_name', '$res_id')";
  if(!mysql_query($SQL, $DBCON)) {
    echo 'Failed to commit log entry, please use alternative log';
    die("Report this to the admin: " . mysql_error());
  } else {
    echo 'Commit Successful, redirecting to home...';
    echo '<meta http-equiv="refresh" content="3">';
  }
}
?>
</body>
</html>
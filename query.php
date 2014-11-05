<?php
require("lib.php");
require("config.php");
$config = getConfig("buildings.json");
?>

<html>
<head>
<title>LockLog Query</title>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
</head>
<body>

<?php
  if(!isset($_GET["run"])) {
?>
<table>
<tr><td colspan="2">Filter By:</td></tr>
  <tr>
  <td>Building:</td>
  <td><input type='checkbox' id='bldg' onclick='toggleCollapse("bldg", "bldgHTML")'></td>
  </tr>
  
  <tr>
  <td>Name:</td>
  <td><input type='checkbox' id='name' onclick='toggleCollapse("name", "nameHTML")'></td>
  </tr>
  
  <tr>
  <td>UTD ID:</td>
  <td><input type='checkbox' id='id' onclick='toggleCollapse("id", "idHTML")'></td>
  </tr>
  
  <tr>
  <td>PA:</td>
  <td><input type='checkbox' id='pa' onclick='toggleCollapse("pa", "paHTML")'></td>
  </tr>
  
  <tr>
  <td>After:</td>
  <td><input type='checkbox' id='dateAfter' onclick='toggleCollapse("dateAfter", "dateAfterHTML")'></td>
  </tr>
  
  <tr>
  <td>Before:</td>
  <td><input type='checkbox' id='dateBefore' onclick='toggleCollapse("dateBefore", "dateBeforeHTML")'></td>
  </tr>
  
  <table>
  <form method="GET" action="query.php"> 
  <tr id='bldgHTML' style='visibility: collapse;'>
  <?php getBuilding($config, true); ?>
  </tr>
  
  <tr id='nameHTML' style='visibility: collapse;'>
  <td>Name:</td>
  <td><input type='text' name='name'></input></td>
  </tr>
  
  <tr id='idHTML' style='visibility: collapse;'>
  <td>UTD ID:</td>
  <td><input type='text' name='id'></input></td>
  </tr>
  
  <tr id='paHTML' style='visibility: collapse;'>
  <td>PA Name:</td>
  <td><input type='text' name='pa'></input></td>
  </tr>
  
  <tr id='dateAfterHTML' style='visibility: collapse;'>
  <td>On or After:</td>
  <td><input type='text' id='datepicker1' name='dateAfter'></input>
  </td>
  </tr>
  
  <tr id='dateBeforeHTML' style='visibility: collapse;'>
  <td>On or Before:</td>
  <td><input type='text' id='datepicker2' name='dateBefore'></input></td>
  </tr>
  
  <tr>
  <td colspan='2'><input type='submit' name='run' value='Run Query'></td>
  </tr>

  </form>  
  </table>
<?php
  } else {
    $dbcon = dblink($DBHOST, $DBUSER, $DBPASS, $DBNAME);
    $SQL = "SELECT * FROM $LOGTABLE WHERE ";

    if(!empty($_GET["bldg"])) {
      $bldg=mysql_real_escape_string($_GET["bldg"]);
      $SQL = $SQL."bldg='$bldg' AND ";
    }
    if(!empty($_GET["name"])) {
      $name=mysql_real_escape_string($_GET["name"]);
      $SQL = $SQL."res_name='$name' AND ";
    }
    if(!empty($_GET["id"])) {
      $id=mysql_real_escape_string($_GET["id"]);
      $SQL = $SQL."res_id='$id' AND ";
    }
    if(!empty($_GET["dateAfter"])) {
      $dateAfter=mysql_real_escape_string($_GET["dateAfter"]);
      $SQL = $SQL."time>='$dateAfter' AND ";
    }
    if(!empty($_GET["dateBefore"])) {
      $dateBefore=mysql_real_escape_string($_GET["dateBefore"]);
      $SQL = $SQL."time<='$dateBefore' AND ";
    }
    $SQL = $SQL."1=1";
    echo $SQL;

    mysql_close($dbcon);
  }
?>
  </body>
  <script src='queryHelpers.js'></script>
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
  <script>
  $(function() {
      $("#datepicker1").datepicker({dateFormat:"yy-mm-dd"});
      $("#datepicker2").datepicker({dateFormat:"yy-mm-dd"});
    });
</script>
</html>

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
  <td><input type='checkbox' id='bldg' onclick='toggleCollapse("bldg", "bldgHTML", "bldgSelect", "")'></td>
  </tr>
  
  <tr>
  <td>Name:</td>
  <td><input type='checkbox' id='name' onclick='toggleCollapse("name", "nameHTML", "nameBox", "")'></td>
  </tr>
  
  <tr>
  <td>UTD ID:</td>
  <td><input type='checkbox' id='id' onclick='toggleCollapse("id", "idHTML", "idBox", "")'></td>
  </tr>
  
  <tr>
  <td>PA:</td>
  <td><input type='checkbox' id='pa' onclick='toggleCollapse("pa", "paHTML", "paBox", "")'></td>
  </tr>
  
  <tr>
  <td>After:</td>
  <td><input type='checkbox' id='dateAfter' onclick='toggleCollapse("dateAfter", "dateAfterHTML", "datepicker1", "")'></td>
  </tr>
  
  <tr>
  <td>Before:</td>
  <td><input type='checkbox' id='dateBefore' onclick='toggleCollapse("dateBefore", "dateBeforeHTML", "datepicker2", "")'></td>
  </tr>
</table>

  <form method="GET" action="query.php">   
  <table>
  <tr id='bldgHTML' style='visibility: collapse;'>
  <?php getBuilding($config, true); ?>
  </tr>
  
  <tr id='nameHTML' style='visibility: collapse;'>
  <td>Name:</td>
  <td><input type='text' id='nameBox' name='name'></input></td>
  </tr>
  
  <tr id='idHTML' style='visibility: collapse;'>
  <td>UTD ID:</td>
  <td><input type='text' id='idBox' name='id'></input></td>
  </tr>
  
  <tr id='paHTML' style='visibility: collapse;'>
  <td>PA Name:</td>
  <td><input type='text' id='paBox' name='pa'></input></td>
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

  </table>
  </form>  
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
  
    if(!($result=mysql_query($SQL, $dbcon))) {
      mysql_close($dbcon);
      die("A serious error occured while running $SQL, report this: ".mysql_error());
    } else {
      echo "<table cellpadding='4' border='0' style='text-align: center;'>";
      echo "<tr><th>Building</th><th>Resident</th><th>Resident ID</th><th>PA</th><th>Date/Time</th></tr>";
      $rowNum=0;
      while($arr = mysql_fetch_array($result)) {
	if($rowNum%2) {
	  echo "<tr style='background-color: #9FF781;'>";
	} else {
	  echo "<tr>";
	}
	echo "<td>".$arr['bldg']."</td>";
	echo "<td>".$arr['res_name']."</td>";
	echo "<td>".$arr['res_id']."</td>";
	echo "<td>".$arr['PA_name']."</td>";
	echo "<td>".$arr["time"]."</td></tr>";
	$rowNum++;
      }
      echo "</table>";
    }
    mysql_close($dbcon);
  }
?>
<script src='queryHelpers.js'></script>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script>
$(function() {
 $("#datepicker1").datepicker({dateFormat:"yy-mm-dd"});
 $("#datepicker2").datepicker({dateFormat:"yy-mm-dd"});

 var checkboxes = document.getElementsByTagName('input');

 for (var i=0; i<checkboxes.length; i++)  {
   if (checkboxes[i].type == 'checkbox')   {
     checkboxes[i].checked = false;
   }
 }
});

function toggleCollapse(checkbox, id, inner, offState) {
    if(document.getElementById(checkbox).checked) {
	document.getElementById(id).style.visibility="visible"
    } else {
	document.getElementById(id).style.visibility="collapse"
	document.getElementById(inner).value=offState
    }
}
</script>
</body>
</html>

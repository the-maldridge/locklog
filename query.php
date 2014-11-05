<?php
require("lib.php");

$config = getConfig("buildings.json");
?>

<html>
<head>
<title>LockLog Query</title>
</head>
<body>
<form method="POST" action="query.php">
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
<table>
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
</table>
</form>
</body>
<script src='queryHelpers.js'></script>
</html>

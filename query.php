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
<?php getBuilding($config, true); ?>
<tr>
  <p>Use Name: <input type='checkbox' name='useName' onClick=useName()></p>
<p id='name'></p>
</tr>
</table>
</form>
</body>
<script src="queryHelpers.js"></script>
</html>

<?php
//Stock variables
$module = "TALI_Personnel";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

echo "
	<div class=\"content PageFrame\">
		<h1><strong>Manage Personnel</strong></h1>
		<p>TBD</p>
	</div>
";
?>
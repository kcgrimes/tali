<?php
//Stock variables
$module = "TALI_Versions";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

echo "
<div class=\"content PageFrame\">
	<h1><strong>Website Software Versions</strong></h1>
	<p>
	TALI: ". $_SESSION['TALI_Version'] . "
	<br/>
";

echo 'PHP: ' . phpversion();
echo "
	<br/>
";
printf("MySQL: %s\n", mysqli_get_server_info($db_handle));

echo "
	</p>
	</div>
";
?>

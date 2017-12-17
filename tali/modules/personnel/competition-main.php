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
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Manage Personnel</h1>
				<p>TBD</p>
			</div>
		</div>
	</main>
";
?>
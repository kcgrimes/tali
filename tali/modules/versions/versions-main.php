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
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Website Software Versions</h1>
				<p>
				TALI: ".TALI_VERSION."
				<br/>
";

echo "PHP: ".phpversion();
echo "
				<br/>
";
printf("MySQL: %s\n", mysqli_get_server_info($db_handle));

echo "
				</p>
			</div>
		</div>
	</main>
";
?>
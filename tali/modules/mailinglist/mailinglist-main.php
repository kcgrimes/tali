<?php
//Stock variables
$module = "TALI_Mailing_List";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

if (!isset($_GET['sub'])) {
	//Fresh page
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Manage Mailing List</h1>
					<p>On this page you can manage the various mailing lists and send mail.</p>
					<a href=\"mailinglist.php?sub=manage\"><p>Manage Custom Mailing Lists</p></a>
				</div>
				
				<div class=\"tali-page-frame\">
					<h1>Send Mail</h1>
					<p>TBD</p>
				</div>
			</div>
		</main>
	";
}
else
{
	switch ($_GET['sub']) {
		case "manage": 
			require 'mailinglist/mailinglist-manage.php';
			break;
	}
}
?>
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
		<div class=\"content PageFrame\">
			<h1><strong>Manage Mailing List</strong></h1>
			<p>On this page you can manage the various mailing lists and send mail.</p>
			<a href=\"mailinglist.php?sub=manage\"><p>Manage Custom Mailing Lists</p></a>
		</div>
		
		<div class=\"content PageFrame\">
			<h1><strong>Send Mail</strong></h1>
			<p>TBD</p>
		</div>
	";
}
else
{
	switch ($_GET['sub']) {
		case "manage": 
			require 'MailingList/mailinglist-manage.php';
			break;
	}
}
?>

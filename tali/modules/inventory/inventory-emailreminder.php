<?php
//File executed periodically via cron job in order to detect upcoming inventory expiration dates and send an email accordingly
	//Execute via: cd /home/customer/www/tcsar.org/public_html; php -q tali/modules/inventory/inventory-emailreminder.php

//Can't just call the usual functions and variables in this environment, so first re-create them
//Set email settings
$config = parse_ini_file("/home/customer/www/config.ini");
define('TALI_SMTP_HOSTNAME', "tcsar.org");
define('TALI_SMTP_PORT', 465);
define('TALI_SMTP_SECURE', "ssl"); //"ssl" or "tls"
define('TALI_SMTP_USERNAME', $config['TALI_SMTP_username']);
define('TALI_SMTP_PASSWORD', $config['TALI_SMTP_password']);
define('TALI_SMTP_FROMADDRESS', "web@tcsar.org");
define('TALI_SMTP_FROMNAME', "TCSAR Website");

//Email function - see TALI_EMail in talihead for details
function TALI_EMail ($toArray, $subject, $msgBody) {
	require "/home/customer/www/tcsar.org/public_html/tali/includes/global/email/email.php";
}

//Connect to database
$TALI_Live_DB_Username = $config['TALI_Live_DB_Username'];
$TALI_Live_DB_Password = $config['TALI_Live_DB_Password'];
$TALI_Live_DB_dbName = "tcsartes_website";
$TALI_Live_DB_Server = "localhost";
define('TALI_DB_USERNAME', $TALI_Live_DB_Username);
define('TALI_DB_PASSWORD', $TALI_Live_DB_Password);
define('TALI_DB_DBNAME', $TALI_Live_DB_dbName);
define('TALI_DB_SERVER', $TALI_Live_DB_Server);

function TALI_dbConnect() {
	$db_handle = mysqli_connect(TALI_DB_SERVER, TALI_DB_USERNAME, TALI_DB_PASSWORD);
	$db_found = mysqli_select_db($db_handle, TALI_DB_DBNAME);
	if ($db_found) {
		//Database connection successful
		return $db_handle;
	}
	else
	{
		//Database connection failed
		return false;
	}
}

$db_handle = TALI_dbConnect(); 
//If connection fails, send notice
if (is_bool($db_handle)) {
	TALI_EMail ([["logistics@tcsar.org", "TCSAR Logistics"]], "Monthly Inventory Report ".date("Y-m-d")."", "Error Loading Page: Database connection failed.");
	exit();
}

//Create function for calculating years/months/days until date
function Inventory_Date_Diff($dateToCount,$canExpire) {
	$dateToday = new DateTime();
	//Output nothing if there is no date entered
	if ($dateToCount == "") {
		return "";
	}
	$dateToCount = new DateTime($dateToCount);
	$interval = $dateToday->diff($dateToCount);
	
	//Notice if expired
	if ($canExpire && ($dateToCount < $dateToday)) {
		$expired = "<font style=\"color:red;\">Expired for: </font>";
	}
	else
	{
		$expired = "";
	}
	
	//Add ending for dates that aren't meant to be expired
	if (!$canExpire && ($dateToCount < $dateToday)) {
		$ending = " ago";
	}
	else
	{
		$ending = "";
	}
	
	//Recursively count years/months/days to label accordingly
	$dateString = "";
	if (($interval->y) > 0) {
		//Include years, months, days
		$dateString = $interval->y." years, ".$interval->m." months, ".$interval->d." days";
	}
	else
	{
		if (($interval->m) > 0) {
			//Include months, days
			$dateString = $interval->m." months, ".$interval->d." days";
		}
		else
		{
			//Include days
			if (($interval->d) >= 0) {
				$dateString = $interval->d." days";
			}
		}
	}
	return $expired . $dateString . $ending;
}

//Create message body
//This is just copy/pasted from inventory-table.php, except the output is changed to be in a line added to a variable with hyperlinks adjusted and date stuff added and not outputting a table

$msgBody = "Attention TCSAR Logistics, 
<br/>Below is a report generated at ".date("Y-m-d H:i:s")." containing non-archived TCSAR inventory items with a lifespan set to expire in the next 60 days: <br/><br/>"; 

//Pull all items that are non-archived and have a set lifespan
$SQL = "SELECT * FROM inventory WHERE lifespan_date <> '' AND NOT status=2 ORDER BY lifespan_date ASC";
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);
while ($db_field = mysqli_fetch_assoc($result)) {
	//If no lifespan, go to next
	$itemDateLifespan=$db_field['lifespan_date'];
	if ($itemDateLifespan != "") {
		//
	}
	else
	{
		continue;
	}
	
	//If lifespan date is beyond 60 days, go to next
	$dateToday = new DateTime();
	$dateToCount = new DateTime($itemDateLifespan);
	$interval = $dateToday->diff($dateToCount);
	if (($interval->days) > 60) {
		continue;
	}
	
	$id=$db_field['id'];
	$name=$db_field['name'];
	$description=$db_field['description'];
	$checkedoutby=$db_field['checkedoutby'];
	$generalLocation=$db_field['location_general'];
	
	$generalLocationName = $generalLocation;
	//Grab the specific location's name if it was chosen
	if ($generalLocationName > 0) {
		$namegenSQL = "SELECT name FROM inventory_location_general WHERE id = $generalLocation";
		$namegenresult = mysqli_query($db_handle, $namegenSQL);
		$namegendb_field = mysqli_fetch_assoc($namegenresult);
		$generalLocationName = $namegendb_field['name'];
	}
	else
	{
		$generalLocationName = "N/A";
	}
	
	$specificLocation=$db_field['location_specific'];
	
	$specificLocationName = $specificLocation;
	//Grab the specific location's name if it is standardized
	if (is_numeric($specificLocationName)) {
		$nameSQL = "SELECT name FROM inventory_location_specific WHERE id = $specificLocation";
		$nameresult = mysqli_query($db_handle, $nameSQL);
		$namedb_field = mysqli_fetch_assoc($nameresult);
		$specificLocationName = $namedb_field['name'];
	}
	else
	{
		$specificLocationName = "N/A";
	}
	
	$lineContent = "
					$id - <a href=\"https://www.tcsar.org/tali/modules/inventory.php?id=$id\">$name</a> - $description - <a href=\"https://www.tcsar.org/tali/modules/inventory.php?generalLocation=$id\">$generalLocationName</a> - <a href=\"https://www.tcsar.org/tali/modules/inventory.php?specificLocation=$id\">$specificLocationName</a> - 
	";
	//If item isn't checked out, allow the option, otherwise put the name of the user who has it
	if ($checkedoutby == 0) {
		if (isset($_GET['archive'])) {
			//Item is archived, so can't checkout
			$lineContent = $lineContent . "
								Archived
			";
		}
		else
		{
			//Item not archived, can checkout
			$lineContent = $lineContent . "
								Stored
			"; 
		}
	}
	else
	{
		$whoSQL = "SELECT username FROM tali_admin_accounts WHERE id = $checkedoutby";
		$whoresult = mysqli_query($db_handle, $whoSQL);
		$whodb_field = mysqli_fetch_assoc($whoresult);
		$whousername=$whodb_field['username'];
		$lineContent = $lineContent . "
								<a href=\"https://www.tcsar.org/tali/modules/inventory.php?userid=$checkedoutby\">$whousername</a>
		";
	}
	
	//Add lifespan date
	$lineContent = $lineContent . " - ".date("m/d/Y", strtotime($itemDateLifespan))."";
	
	//Add time until expiration
	$lineContent = $lineContent . " - ".Inventory_Date_Diff($itemDateLifespan,true)."";
	
	//Add linebreak
	$lineContent = $lineContent . "
					<br/>
	";
	
	//Add total line to body
	$msgBody = $msgBody . $lineContent;
}

$msgBody = $msgBody . "<br/>End Report";

//Create and send email
TALI_EMail ([["logistics@tcsar.org", "TCSAR Logistics"]], "Monthly Inventory Report ".date("Y-m-d")."", $msgBody);
<?php	
session_start();

//Loaded in head of all back-end (TALI admin) files

//Return TALI folder URI by taking path to current file, starting from position 0, and subtracting (length of active file's path - length of TALI path) from the end
	//bug - Use of inHead allows for multi-project presence (where the web root isn't really the website root)
define('TALI_URI_INHEAD', substr($_SERVER['PHP_SELF'], 0, - (strlen($_SERVER['SCRIPT_FILENAME']) - strlen(realpath(__DIR__ . '/../..')))));

echo "
	<head>
		<title>TALI Website Admin</title>
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
		<link rel=\"stylesheet\" href=\"".TALI_URI_INHEAD."/includes/global/talistyles.css?v=".filemtime("".realpath(__DIR__)."/talistyles.css")."\" type=\"text/css\" />
		<link rel=\"shortcut icon\" href=\"".TALI_URI_INHEAD."/images/favicon.ico\"/>
		<link rel=\"icon\" href=\"".TALI_URI_INHEAD."/images/favicon.ico\"/>
	</head>
";

/*
Function - TALI_bodyContent
Uniform way of making each page - header, main, footer
Select 1 - Directory to body content of page
*/
function TALI_bodyContent($bodyContentDir) {
	//Body Start
	echo "	<body>";
	//Header
	require realpath(__DIR__)."/header.php";
	//Main - $bodyContentDir is directory to unique file for page's Main
	require $bodyContentDir;
	//Footer
	require realpath(__DIR__)."/footer.php";
	//Body End
	echo "	</body>";
}

/*
Function - TALI_sessionCheck
Used to check login status of the user and verify permissions
when accessing modules within the TALI admin panel.
Select 1 - Module name (string) to verify permissions against
Select 2 - Active database reference handle
*/
function TALI_sessionCheck($module, $db_handle) {
	//Check if the user is logged in
	if ((!isset($_SESSION['login']) || ($_SESSION['login'] != TRUE))) {
		//Not logged in, so redirect to login.php
		header ("Location: ".TALI_URI_INHEAD."/login.php");
		exit();
	}
	
	//Verify if user has permission to access this particular module, unless it is TALI Index
	if ((isset($_SESSION['level'])) && ($module != 'TALI_Index')) {		
		//Obtain permission level for specific module
		$SQL = "SELECT permission FROM tali_modules WHERE module = '$module'";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		
		//Check if level is permitted in module (permitted levels are stored as an array-as-string)
		//Note - Checking for false
		if (!in_array($_SESSION['level'],explode(",", $db_field['permission']))) {
			//User does not have permission, so redirect to landing page
			//bug - for 3rd ID to allow all access to Drill Reports
				//Make dyanmic by having setting in tali_init?
			if ((isset($_GET['sub'])) && ($_GET['sub'] == "drillreports")) {
				//nothing, so it proceeds
			}
			else
			{
				header ("Location: ".TALI_URI_INHEAD."/index.php?denied=$module");
				exit();
			}
		}
	}
}

/*
Function - TALI_Create_History_Report
Used to efficiently make a statement that is then inserted into the DB history, 
in addition to collecting other relevant tracking information.
e.g. 2015-08-27 20:16:44 - News Entry ID# 99, Title of my Story, created by email@mail.org
Select 1 - Action (string) to be read, e.g. 'created'
Select 2 - Module Name of involved Module (string) e.g. 'TALI_News'
Select 3 - MySQLi connection return
Select 4 - Name of DB Table that is encountering change (string) e.g. 'tali_news'
Select 5 - Column name containing ID of subject (string) e.g. 'rank_id'
Select 6 - ID of item (integer)
Select 7 - Name/designation of item's ID (string) e.g. 'News Entry ID#'
Select 8 - Name of column containing the display name of the item (string) e.g. 'title'
*/
function TALI_Create_History_Report($action_str, $module, $db_handle, $db_Table, $db_Table_id, $item_id, $id_type_str, $db_item_col) {
	$SQL = "SELECT * FROM $db_Table WHERE $db_Table_id=$item_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);

	$time = date('Y-m-d H:i:s');
	
	$item_name = $db_field[$db_item_col];
	$username = $_SESSION['username'];
	$username_id = $_SESSION['username_id'];
	
	$insertHistory = "$time - $id_type_str $item_id, $item_name, $action_str by $username";
	$insertHistory_sql = TALI_quote_smart($insertHistory, $db_handle);
	
	$SQL = "INSERT INTO tali_master_history (time, username_id, module, item_id, event) VALUES (CURRENT_TIMESTAMP, $username_id, '$module', $item_id, $insertHistory_sql)";
	$result = mysqli_query($db_handle, $SQL);
	
	return $insertHistory;
}

//Initialize Team Administration/Logistics Interface (TALI)
//bug - A bit inefficient, but I don't see another way to do this so automatically
$cnt = 0;
//Initially, have dir backed out to includes, so first loop will be in tali root
$upwardStr = "/../";
//Define function for recursive searching of directories
function TALI_globForInit($upwardStr, $pattern, $flags = 0) {
	$dirs = glob($pattern, $flags);
	foreach ($dirs as $dir) {
		$initArray = (glob("".$dir."/tali_init.php"));
		if (empty($initArray)) {
			if (!empty(glob("".$dir."/*", GLOB_ONLYDIR))) {
				$initArray = (TALI_globForInit($upwardStr, "".$dir."/*", GLOB_ONLYDIR));
			}
		}
		if (!empty($initArray)) {
			break;
		}
	}
	return $initArray;
}
//Loop upward, starting at the tali root, to find tali_init.php
//10 loops is just an arbitrary max
//Once the file is found, the loop will break out
while ((empty($initArray)) && ($cnt < 10)) {
	//Go up a directory
	$upwardStr = $upwardStr . "../";
	//Search directory for tali_init.php
	$initArray = (glob("".realpath(__DIR__ . $upwardStr)."/tali_init.php"));
	//If not found, search directories recursively for it
	if (empty($initArray)) {
		$initArray = (TALI_globForInit($upwardStr, "".realpath(__DIR__ . $upwardStr)."/*", GLOB_ONLYDIR));
	}
	$cnt++;
}
if (!empty($initArray)) {
	//Execute (simpler path is actually defined later)
	require $initArray[0];
}
else
{
	//File could not be located after max loops
	exit("Error Loading Page: tali_init.php not found.");
}
?>
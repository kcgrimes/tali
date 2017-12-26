<?php	
session_start();

//Loaded in head of all back-end (TALI admin) files
//that execute tali_init.php in their <head>.

//bug - What is this doing? Can it be accomplished by just executing tali_init.php up here?
define('TALI_ROOT_URL', substr($_SERVER['PHP_SELF'], 0, - (strlen($_SERVER['SCRIPT_FILENAME']) - strlen(realpath(__DIR__ . '/../..')))));
define('TALI_ROOT_DIR', realpath(__DIR__ . '/../..'));

echo "
	<head>
		<title>TALI Website Admin</title>
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
		<link rel=\"stylesheet\" href=\"".TALI_ROOT_URL."/includes/global/talistyles.css?v=".filemtime("".TALI_ROOT_DIR."/includes/global/talistyles.css")."\" type=\"text/css\" />
		<link rel=\"shortcut icon\" href=\"".TALI_ROOT_URL."/images/favicon.ico\"/>
		<link rel=\"icon\" href=\"".TALI_ROOT_URL."/images/favicon.ico\"/>
	</head>
";

//Uniform way of making each page - header, main, footer
function TALI_bodyContent ($bodyContentDir) {
	//Body Start
	echo "	<body>";
	//Header
	require "includes/global/header.php";
	//Main - $bodyContentDir is directory to unique file for page's Main
	require $bodyContentDir;
	//Footer
	require "includes/global/footer.php";
	//Body End
	echo "	</body>";
}

//Uniform way of making each page - header, main, footer
function TALI_bodyContent_Module ($bodyContentDir) {
	//Body Start
	echo "	<body>";
	//Header
	require "../includes/global/header.php";
	//Main - $bodyContentDir is directory to unique file for page's Main
	require $bodyContentDir;
	//Footer
	require "../includes/global/footer.php";
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
	if (!(isset($_SESSION['login']) && $_SESSION['login'] != '')) {
		//Not logged in, so redirect to login.php
		header ("Location: ".TALI_ROOT_URL."/login.php");
		exit();
	}
	
	//Verify if user has permission to access this particular module, unless it is TALI Index
	if ((isset($_SESSION['level'])) && ($module != 'TALI_Index')) {
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
				header ("Location: ".TALI_ROOT_URL."/index.php?denied=$module");
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
//bug - make this dynamic some day
if (file_exists("../tali_init.php")) {
	//Accessing from Index
	require "../tali_init.php";
}
else
{
	//Accessing from modules
	require "../../tali_init.php";
}
?>
<?php
//Loaded in head of all back-end (TALI admin) and front-end (TALI output) files
//that execute tali_init.php in their <head>.

echo "
	<link href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/includes/talistyles_front.css\" rel=\"stylesheet\" type=\"text/css\" />
	<script src=\"https://code.jquery.com/jquery-3.2.1.min.js\" integrity=\"sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=\" crossorigin=\"anonymous\"></script>
"; 


//markitup functions
//function BBCode2Html($text) - Parses BB Code to HTML via "markitup"
require "".$_SESSION['TALI_ROOT_DIR']."/modules/markitup/markitup.bbcode-parser.php";

//Function to execute "markitup" editing
function markItUp_editing () {
	echo '
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script type="text/javascript" src="markitup/jquery.markitup.js"></script>
		<script type="text/javascript" src="markitup/sets/bbcode/set.js"></script>
		<link rel="stylesheet" type="text/css" href="markitup/skins/markitup/style.css" />
		<link rel="stylesheet" type="text/css" href="markitup/sets/bbcode/style.css" />
		<script type="text/javascript" >
			$(document).ready(function() {
				$("textarea").markItUp(mySettings);
			});
		</script>
	';
}

/*
Function - TALI_fetchDBPage
Used to query the database for the content associated with a specific page.
Select 1 - String - Name of page for identification in the database
*/
function TALI_fetchDBPage ($pageTitle) {
	//Connect to database
	$db_handle = TALI_dbConnect(); 
	if (is_bool($db_handle)) {
		exit("Error Loading Page: Database connection failed.");
	}
	
	//Query database using the given page title
	$SQL = "SELECT * FROM tali_pages WHERE title = '$pageTitle'";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	
	//Manage time of last update
	$time=$db_field['time'];
	$time=strtotime($time);
	//Convert $time to more useful strings
	$time=date('l\, F jS\, Y \a\t H:i', $time);
	
	//Manage body
	$body=$db_field['body'];
	//Convert $body text back into real characters
	$body=htmlspecialchars_decode($body);
	//Output body text, changing markup from BBCode to HTML via markitup
	echo BBCode2Html($body);
}

/*
Function - TALI_quote_smart
Used to remove "magic slashes" that used to be automatically added for SQL protection
in older PHP versions (poor practice), and to correctly escape strings as appropriate.
Select 1 - String - Value needing to be made safe for SQL
Select 2 - String - Returned value, safe for SQL
*/
function TALI_quote_smart ($value, $handle) {
   if (get_magic_quotes_gpc()) {
	   $value = stripslashes($value);
   }

   if (!is_numeric($value)) {
	   $value = "'" . mysqli_real_escape_string($handle,$value) . "'";
   }
   return $value;
}

/*
Function - TALI_dbConnect
Connect to appropriate database using information defined in tali_init.php
Return Select 1 - Connection handle
Return Select 2 - Boolean - Connection status
*/
function TALI_dbConnect () {
	switch ($_SESSION['TALI_Platform']) {
		case "wamp":
			//FOR DEV USE WITH WAMP
			$DB_Username = $_SESSION['TALI_WAMP_DB_Username'];
			$DB_Password = $_SESSION['TALI_WAMP_DB_Password'];
			$DB_dbName = $_SESSION['TALI_WAMP_DB_dbName'];
			$DB_Server = $_SESSION['TALI_WAMP_DB_Server'];
			break;
		case "dev":
			//FOR DEV USE ON SERVER
			$DB_Username = $_SESSION['TALI_Dev_DB_Username'];
			$DB_Password = $_SESSION['TALI_Dev_DB_Password'];
			$DB_dbName = $_SESSION['TALI_Dev_DB_dbName'];
			$DB_Server = $_SESSION['TALI_Dev_DB_Server'];
			break;
		case "live":
			//FOR REAL PUBLISH USE
			$DB_Username = $_SESSION['TALI_Live_DB_Username'];
			$DB_Password = $_SESSION['TALI_Live_DB_Password'];
			$DB_dbName = $_SESSION['TALI_Live_DB_dbName'];
			$DB_Server = $_SESSION['TALI_Live_DB_Server'];
			break;
	}
	$db_handle = mysqli_connect($DB_Server, $DB_Username, $DB_Password);
	$db_found = mysqli_select_db($db_handle, $DB_dbName);
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

/*
Function - TALI_FTP_Connect
Used to connect to the FTP server for file manipulation.
Select 1 - String - Name of input used to select file (not the actual file name itself)
Select 2 - String - Post-root upload directory with leading and 
	trailing slash "/" (e.g. '/talisupplement/homeslider/')
*/
function TALI_FTP_Connect() {
	//Baseline connection = false
	$connection_success = FALSE;
	//Collect FTP login data

	//Check platform status in order to select appropriate FTP data
	//Puts login data into array
	switch ($_SESSION['TALI_Platform']) {
		case "wamp":
			//FOR DEV USE WITH WAMP
			$ftp_server = $_SESSION['TALI_WAMP_FTP_URL'];
			$ftp_user_name = $_SESSION['TALI_WAMP_FTP_Username'];
			$ftp_user_pass = $_SESSION['TALI_WAMP_FTP_Password'];
			break;
		case "dev":
			//FOR DEV USE ON SERVER
			$ftp_server = $_SESSION['TALI_Dev_FTP_URL'];
			$ftp_user_name = $_SESSION['TALI_Dev_FTP_Username'];
			$ftp_user_pass = $_SESSION['TALI_Dev_FTP_Password'];
			break;
		case "live":
			//FOR REAL PUBLISH USE
			$ftp_server = $_SESSION['TALI_Live_FTP_URL'];
			$ftp_user_name = $_SESSION['TALI_Live_FTP_Username'];
			$ftp_user_pass = $_SESSION['TALI_Live_FTP_Password'];
			break;
	}
			
	//Attempt to connect to FTP
	$conn_id = ftp_connect($ftp_server);
	ftp_pasv($conn_id, true); 
	//Attempt to login to FTP
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

	//Check FTP connection (Did you connect? Did you login?)
	//bug - check connection and login independently?
	if ((!$conn_id) OR (!$login_result)) {
		//$connection_success remains false
		$displayMsg = "</br>Failed to connect to the FTP server.";
	}
	else 
	{
		$displayMsg = "</br>Successfully connected to the FTP server.";
		$connection_success = TRUE;
	}
	
	$returnArray[1] = $displayMsg;
	$returnArray[2] = $conn_id;
	$returnArray[3] = $connection_success;
	return $returnArray;
}

/*
Function - TALI_FTP_Upload
Used to connect to the FTP server and upload a single file.
Select 1 - String - Name of input used to select file (not the actual file name itself)
Select 2 - String - Post-root upload directory with leading and trailing slash "/" (e.g. '/talisupplement/homeslider/')
*/
function TALI_FTP_Upload($file_input, $upload_directory) {
	//Attempt to connect to FTP
	$ftpConnect_Return = TALI_FTP_Connect();
	//Gather message and connection variables
	$displayMsg = $ftpConnect_Return[1];
	$conn_id = $ftpConnect_Return[2];
	$connection_success = $ftpConnect_Return[3];
	
	$upload_success = FALSE;
	
	if ($connection_success) {
		//Initiate upload
		//Check platform status in order to select appropriate root
			//Note - Can't "upload" to WAMP
		//bug - Default to dev instead of Live for wamp?
		if ($_SESSION['TALI_Platform'] == "dev") {
			ftp_chdir($conn_id, ''.$_SESSION['TALI_FTP_Dev_Root'].''.$upload_directory.'');
		}
		else
		{
			ftp_chdir($conn_id, ''.$_SESSION['TALI_FTP_Live_Root'].''.$upload_directory.'');
		}
		$destination_file_name = $_FILES[$file_input]['name'];
		$upload = ftp_put($conn_id, $destination_file_name, $_FILES[$file_input]['tmp_name'], FTP_BINARY); 
		
		//Check upload status
		if (!$upload) { 
			$displayMsg = $displayMsg."</br>The file upload has failed. Try again.<br/>";
		}
		else
		{
			$displayMsg = $displayMsg."</br>The file $destination_file_name has been successfully uploaded to the FTP server.<br/>";
			$upload_success = TRUE;
		}
		
		//Close FTP connection
		ftp_close($conn_id);
	}
	
	$returnArray[1] = $displayMsg;
	$returnArray[2] = $upload_success;
	return $returnArray;
}

/*
Function - TALI_FTP_Delete
Used to connect to the FTP server and delete a single file.
//Bug - what is select 1 technically?
Select 1 - String - File name, including extension, to be deleted from the Select 2 directory
Select 2 - String - Post-root directory with leading and trailing slash "/" from which file is to be deleted (e.g. '/talisupplement/homeslider/')
*/
function TALI_FTP_Delete($source_file, $delete_directory) {
	//Attempt to connect to FTP
	$ftpConnect_Return = TALI_FTP_Connect();
	//Gather message and connection variables
	$displayMsg = $ftpConnect_Return[1];
	$conn_id = $ftpConnect_Return[2];
	$connection_success = $ftpConnect_Return[3];
	
	$delete_success = FALSE;
	
	if ($connection_success) {
		//Initiate delete
		//Check platform status in order to select appropriate root
			//Note - Can't "upload" to WAMP
		//bug - Default to dev instead of Live for wamp?
		if ($_SESSION['TALI_Platform'] == "dev") {
			ftp_chdir($conn_id, ''.$_SESSION['TALI_FTP_Dev_Root'].''.$delete_directory.'');
		}
		else
		{
			ftp_chdir($conn_id, ''.$_SESSION['TALI_FTP_Live_Root'].''.$delete_directory.'');
		}
		$delete = ftp_delete($conn_id, $source_file); 

		//Check delete status
		if (!$delete) { 
			$displayMsg = $displayMsg."</br>Failure to delete file. Try again.<br/>";
		} 
		else 
		{
			$displayMsg = $displayMsg."</br>The file $source_file has been successfully deleted from the FTP server.<br/>";
			$delete_success = TRUE;
		}
		
		//Close FTP connection
		ftp_close($conn_id);
	}
	
	$returnArray[1] = $displayMsg;
	$returnArray[2] = $delete_success;
	return $returnArray;
}
?>

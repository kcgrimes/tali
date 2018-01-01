<?php
//Loaded in or right after head of all back-end (TALI admin) and front-end (TALI output)
//files that execute tali_init.php in their <head>.

echo "
	<link rel=\"stylesheet\" href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_TALISupplement_URI']."/includes/talistyles_front.css?v=".filemtime("".$_SESSION['TALI_TALISupplement_ABS_PATH']."/includes/talistyles_front.css")."\" type=\"text/css\"/>
	<script src=\"https://code.jquery.com/jquery-3.2.1.min.js\" integrity=\"sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=\" crossorigin=\"anonymous\"></script>
"; 

/* Function - markitup - BBCode2Html
Source: http://markitup.jaysalvat.com/home/
Note - Requires jquery
Parses BB Code to HTML via "markitup" parser
Select 1 - String - Text marked up with BB Code to be converted to HTML
*/ 
require "".$_SESSION['TALI_ABS_PATH']."/modules/markitup/markitup.bbcode-parser.php";

/* Function - markitup
Source: http://markitup.jaysalvat.com/home/
Note - Requires jquery
Execution of "markitup" editing which provides a textarea with BB Code toolbar
Select 1 - Empty
*/ 
function markItUp_editing() {
	echo '
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
function TALI_fetchDBPage($pageTitle) {
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
Function - TALI_Module_HomeSlider
Used to display HomeSlider images queried from the database.
Select 1 - Number of images to display
*/
function TALI_Module_HomeSlider($imageNumber) {
	require "".$_SESSION['TALI_ABS_PATH']."/modules/homeslider/homeslider-front.php";
}

/*
Function - TALI_Module_News_Recent
Used to display recent news entries queried from the database.
Select 1 - Number of entries to display
*/
function TALI_Module_News_Recent($articleNumber) {
	require "".$_SESSION['TALI_ABS_PATH']."/modules/news/news-recent-front.php";
}

/*
Function - TALI_Module_News
Used to display news entries and an archive of these entries queried from the database.
Select 1 - Number of entries to display
*/
function TALI_Module_News($articleNumber) {
	require "".$_SESSION['TALI_ABS_PATH']."/modules/news/news-front.php";
}

/*
Function - TALI_Module_Roster
Used to display roster data queried from the database.
Select 1 - Empty
*/
function TALI_Module_Roster() {
	require "".$_SESSION['TALI_ABS_PATH']."/modules/personnel/roster/roster-front.php";
}

/*
Function - TALI_EMail
Used to send an EMail using configured settings.
Select 1 - String - Intended email target
Select 2 - String - Display name of intended email target
Select 3 - String - Subject line of email
Select 4 - String - Body text of email
*/
//bug - How does this work with multiple "to" addresses?
//bug - What other variables can be made more available for expanded use?
function TALI_EMail ($toEmail, $toName, $subject, $msgBody) {
	require "includes/global/email/email.php";
}

/*
Function - TALI_quote_smart
Used to remove "magic slashes" that used to be automatically added for SQL protection
in older PHP versions (poor practice), and to correctly escape strings as appropriate.
Select 1 - String - Value needing to be made safe for SQL
Select 2 - String - Returned value, safe for SQL
*/
function TALI_quote_smart($value, $handle) {
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
function TALI_dbConnect() {
	$db_handle = mysqli_connect($_SESSION['TALI_DB_Server'], $_SESSION['TALI_DB_Username'], $_SESSION['TALI_DB_Password']);
	$db_found = mysqli_select_db($db_handle, $_SESSION['TALI_DB_dbName']);
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
			
	//Attempt to connect to FTP
	$conn_id = ftp_connect($_SESSION['TALI_FTP_URL']);
	ftp_pasv($conn_id, true); 
	//Attempt to login to FTP
	$login_result = ftp_login($conn_id, $_SESSION['TALI_FTP_Username'], $_SESSION['TALI_FTP_Password']); 

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
		ftp_chdir($conn_id, ''.$_SESSION['TALI_FTP_Root'].''.$upload_directory.'');
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
		ftp_chdir($conn_id, ''.$_SESSION['TALI_Live_FTP_Root'].''.$delete_directory.'');
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
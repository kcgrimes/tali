<?php
	/*
	--TALI Settings File--
	This file houses paths to necessary files/folders, values of variables, 
	and numerous other definitions that are vital to the proper integration
	and operation of TALI on a website. 
	*/
	
	/* DO NOT EDIT THE BELOW SECTION */
	
	//Makes sure session_started if not already done by non-TALI head
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	
	//Declare TALI version
	$_SESSION['TALI_Version'] = "0.9";
		
	/* FEEL FREE TO EDIT RESPONSIBILY BELOW */
	
	//Set platform
	//"wamp" - WAMP development
	//"dev" - online development
	//"live" - published website
	$_SESSION['TALI_Platform'] = "wamp";
	//
	
	//Is "tali_init.php" in the "web root" directory (www, public_html, etc.)?
	$tali_init_in_root = true; //Default = true
	//If set to false, you MUST define the absolute directory of the root below,
	//replacing the getcwd() command with a string as directed
	
	$absolute_root_directory = "".getcwd()."/"; //Default = getcwd()
	//If tali_init_in_root is false, you must re-define this as a string of the absolute root
	//Ex: "/home/username/public_html/CMS" (no slash (/) on the right end)
	//To find out the root directory, uncomment and execute the below code:
	
	//echo getcwd();
	
	//...and use the resulting string as the definition (remember to wrap in quotations " ")
	
	//Domain URL for absolute paths
	//Must have https:// or similar protocol
	//Do not include www., but do include any pertinent directory (such as m. or dev.)
	//A slash (/) must be at the end
	//WAMP Domain
	$WAMP_Domain_URL = "https://localhost/home/"; //"https://localhost/home/"
	
	//Dev Domain
	$Dev_Domain_URL = "https://dev.domain.com/"; //"https://dev.domain.com/"
	
	//Live Domain
	$Live_Domain_URL = "https://domain.com/"; //"https://domain.com/"
	
	//Define the name of the website or associated organization, as this will be
	//displayed in a few places involved with TALI
	$_SESSION['TALI_Organization_Name'] = "Website"; //Default - Website
	
	//Below are the definitions of various critical paths; incorrect inputs will result
	//in critical and obvious failure upon execution of any TALI components
	//"TALI" folder location relative to the web root
	$_SESSION['TALI_ROOT_URL'] = "TALI"; //Default = "TALI", indicating it is in the web root
	//If folder is not in web root, add pathing to last part of string, ex: "CMS/TALI"
	
	//"tali_init.php" file location relative to the web root
	$_SESSION['tali_init_URL'] = "tali_init.php"; //Default = "tali_init.php", indicating it is in the web root
	//If file not in web root, add pathing, ex: "CMS/tali_init.php"
	
	//Location of file containing sensitive information
	//This file is usually a *.ini file which may or may not be in the web root
	//If not in use, add // to comment out (avoid leaving sensitive information in plain text in web files)
		//First checking what platform is in use, then will parse (translate & read) the indicated
		//file, returning the data as an array to be referenced later
	if ($_SESSION['TALI_Platform'] == "wamp") {
		//If using wamp, config.ini is in "web root"
		$config = parse_ini_file("config.ini");
	}
	else
	{
		//In .dev and live platforms, config.ini is the same and not in the web root
		$config = parse_ini_file("/home/config.ini");
	}
	
	//FTP Directory of Live site
	$_SESSION['TALI_FTP_Live_Root'] = "/www"; //Default = "/www", maybe need "/public_html" instead
	
	//FTP Directory of Dev site
	$_SESSION['TALI_FTP_Dev_Root'] = "/dev"; //Alternative FTP directory; If not using dev site, ignore
	
	//MySQL Database Login Information
	//Note - If using a different localhost than WAMP, still leave WAMP in the variable name
	//If not using Wamp (or any other locally hosted database for testing) or a Dev
	//site, just leave them blank and use Live for the published website.
	//WAMP Database Login
	$_SESSION['TALI_WAMP_DB_Username'] = $config['TALI_WAMP_DB_Username'];
	$_SESSION['TALI_WAMP_DB_Password'] = $config['TALI_WAMP_DB_Password'];
	$_SESSION['TALI_WAMP_DB_dbName'] = "dbName";
	$_SESSION['TALI_WAMP_DB_Server'] = "localhost";
	
	//Dev Database Login
	$_SESSION['TALI_Dev_DB_Username'] = $config['TALI_Dev_DB_Username'];
	$_SESSION['TALI_Dev_DB_Password'] = $config['TALI_Dev_DB_Password'];
	$_SESSION['TALI_Dev_DB_dbName'] = "dbName";
	$_SESSION['TALI_Dev_DB_Server'] = "localhost";
	
	//Live Database Login
	$_SESSION['TALI_Live_DB_Username'] = $config['TALI_Live_DB_Username'];
	$_SESSION['TALI_Live_DB_Password'] = $config['TALI_Live_DB_Password'];
	$_SESSION['TALI_Live_DB_dbName'] = "dbName";
	$_SESSION['TALI_Live_DB_Server'] = "localhost";
	
	//FTP Login Information
	//Note - If using a different localhost than WAMP, still leave WAMP in the variable name
	//If not using Wamp (or any other local host for testing) or a Dev
	//site, just leave them blank and use Live for the published website.
	//WAMP FTP Login
	$_SESSION['TALI_WAMP_FTP_URL'] = "ftp.domain.com";
	$_SESSION['TALI_WAMP_FTP_Username'] = $config['TALI_WAMP_FTP_Username'];
	$_SESSION['TALI_WAMP_FTP_Password'] = $config['TALI_WAMP_FTP_Password'];
	
	//Dev FTP Login
	$_SESSION['TALI_Dev_FTP_URL'] = "ftp.domain.com";
	$_SESSION['TALI_Dev_FTP_Username'] = $config['TALI_Dev_FTP_Username'];
	$_SESSION['TALI_Dev_FTP_Password'] = $config['TALI_Dev_FTP_Password'];
	
	//Live FTP Login
	$_SESSION['TALI_Live_FTP_URL'] = "ftp.domain.com";
	$_SESSION['TALI_Live_FTP_Username'] = $config['TALI_Live_FTP_Username'];
	$_SESSION['TALI_Live_FTP_Password'] = $config['TALI_Live_FTP_Password'];
	
	//E-Mail SMTP Login Information
	//Uses PHPMailer (https://github.com/PHPMailer/PHPMailer) to send e-mails
	//through local or remote SMTP server as defined
	$_SESSION['TALI_SMTP_hostname'] = "mail.domain.com";
	$_SESSION['TALI_SMTP_port'] = 465;
	$_SESSION['TALI_SMTP_secure'] = "ssl"; //"ssl" or "tls"
	$_SESSION['TALI_SMTP_username'] = $config['TALI_SMTP_username'];
	$_SESSION['TALI_SMTP_password'] = $config['TALI_SMTP_password'];
	$_SESSION['TALI_SMTP_fromAddress'] = "email@domain.com";
	$_SESSION['TALI_SMTP_fromName'] = "From Me";
	
	//TALI HomeSlider images folder location from web root
	$_SESSION['TALI_HomeSlider_Images_Directory'] = "/TALI/TALISupplement/HomeSlider/";
	
	//TALI Awards images folder location from web root
	$_SESSION['TALI_Awards_Images_Directory'] = "/TALI/TALISupplement/Personnel/Awards/";
	
	//TALI Ranks images folder location from web root
	$_SESSION['TALI_Ranks_Images_Directory'] = "/TALI/TALISupplement/Personnel/Ranks/";
	
	//Web URL to cPanel
	$_SESSION['TALI_Index_cPanel_Link'] = "https://cpanel.hostname.com/";
	
	/* DO NOT EDIT THE BELOW SECTION */
	
	//Defining Domain URL based on selected platform
	switch ($_SESSION['TALI_Platform']) {
		case "wamp":
			$_SESSION['TALI_Domain_URL'] = $WAMP_Domain_URL;
		break;
		case "dev":
			$_SESSION['TALI_Domain_URL'] = $Dev_Domain_URL;
		break;
		case "live":
			$_SESSION['TALI_Domain_URL'] = $Live_Domain_URL;
		break;
	}
	
	//Correct absolute directory if session started within TALI directory
	//bug - kind of dirty, eh?
	if ($tali_init_in_root == true) {
		$absolute_root_directory = str_replace("\TALI", "", $absolute_root_directory);
		$absolute_root_directory = str_replace("/TALI", "", $absolute_root_directory);
		$absolute_root_directory = str_replace("\Modules", "", $absolute_root_directory);
		$absolute_root_directory = str_replace("/Modules", "", $absolute_root_directory);
	}
		
	//Defines TALI directory by combining the absolute root of the website with the URL root of the folder as defined above
	$_SESSION['TALI_ROOT_DIR'] = "".$absolute_root_directory."".$_SESSION['TALI_ROOT_URL'].""; 
		
	//Defines TALISupplement directory by combining the absolute root of the website with the URL root of the TALI folder
	$_SESSION['TALISupplement_ROOT_DIR'] = "".$absolute_root_directory."".$_SESSION['TALI_ROOT_URL']."/TALISupplement"; 
	$_SESSION['TALISupplement_ROOT_URL'] = "".$_SESSION['TALI_ROOT_URL']."/TALISupplement";
	
	//Defines tali_init.php directory by combining the absolute root of the website with the URL root of the file as defined above
	$_SESSION['tali_init_DIR'] = "".$absolute_root_directory."".$_SESSION['tali_init_URL'].""; 
	
	//Enter TALI
	require "".$_SESSION['TALISupplement_ROOT_DIR']."/Includes/talihead.php";
	
	/* */
?>
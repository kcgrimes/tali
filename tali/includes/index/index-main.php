<?php
//Stock variables
$error = "";
$module = "TALI_Index";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

TALI_sessionCheck($module, $db_handle);
if (isset($_GET['denied'])) {
	$denied = $_GET['denied'];
	$error = "You do not have permission to access the $denied module!";
}

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Team Administration/Logistics Interface (TALI)</h1>
				<p>Welcome to TALI, a php-based interface system designed to create and maintain organized, team-oriented web content. Use the icons below to access the TALI modules and navigate through the interface. At any time you can click the banner up top to return to this index page.</p>
			</div>
			
			<div class=\"tali-page-frame\">
				<h1>TALI Modules</h1>
				<p style=\"text-align:center;color:red\">$error</p>
";

//Prepare array of all modules that can be viewed by this
//session's permission level
$SQL = "SELECT * FROM tali_modules";
$result = mysqli_query($db_handle, $SQL);
$module_array = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	//Check if level is permitted in module (permitted levels are stored as an array-as-string)
	if (in_array($_SESSION['level'],explode(",", $db_field['permission']))) {
		//Permission granted
		$module_array[] = $db_field['module'];
	}
}
		
echo "
				<div class=\"tali-responsive-row\">
					<a href=\"login.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-LogOut.png\" alt=\"Log Out Icon\" name=\"Log Out Icon\">
						<p>Log Out</p>
					</a>
";
if (in_array('TALI_Admin_Accounts',$module_array,true)) {
	echo "
					<a href=\"modules/adminaccounts.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-ManageAdminAccounts.png\" alt=\"Manage Admin Accounts Icon\" name=\"Manage Admin Accounts Icon\">
						<p>Manage Admin Accounts</p>
					</a>
	";
}

if (in_array('TALI_Admin_Permissions',$module_array,true)) {
	echo "
					<a href=\"modules/adminpermissions.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-ManageAdminPermissions.png\" alt=\"Manage Admin Permissions Icon\" name=\"Manage Admin Permissions Icon\">
						<p>Manage Admin Permissions</p>
					</a>
	";
}

echo "
					<a target=\"_blank\" href=\"".$_SESSION['TALI_Index_cPanel_Link']."\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-AccesscPanel.png\" alt=\"Access cPanel Icon\" name=\"Access cPanel Icon\">
						<p>Access cPanel</p>
					</a>
					<a href=\"".$_SESSION['TALI_Domain_URL']."\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-Home.png\" alt=\"Return to Homepage Icon\" name=\"Return to Homepage Icon\">
						<p>Return To Homepage</p>
					</a>
				</div>
				
				<div class=\"tali-responsive-row\">
";
if (in_array('TALI_Pages',$module_array,true)) {
	echo "
					<a href=\"modules/pages.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-Pages.png\" alt=\"Pages & Content Icon\" name=\"Pages & Content Icon\">
						<p>Manage Pages & Content</p>
					</a>
	";
}
if (in_array('TALI_News',$module_array,true)) {
	echo "
					<a href=\"modules/news.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-News.png\" alt=\"News Icon\" name=\"News Icon\">
						<p>Manage News Entries</p>
					</a>
	";
}
if (in_array('TALI_Home_Slider',$module_array,true)) {
	echo "
					<a href=\"modules/homeslider.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-HomeSlider.png\" alt=\"Home Slider Icon\" name=\"Home Slider Icon\">
						<p>Manage Home Slider</p>
					</a>
	";
}
if (in_array('TALI_Master_History',$module_array,true)) {
	echo "
					<a href=\"modules/masterhistory.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-MasterHistory.png\" alt=\"Master History Report Icon\" name=\"Master History Report Icon\">
						<p>Master History Report</p>
					</a>
	";
}
if (in_array('TALI_Versions',$module_array,true)) {
	echo "
					<a href=\"modules/versions.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-Versions.png\" alt=\"Software Versions Icon\" name=\"Software Versions Icon\">
						<p>Software Versions</p>
					</a>
	";
}

echo "
	
				</div>
			
				<div class=\"tali-responsive-row\">
";
if (in_array('TALI_Personnel',$module_array,true)) {
	echo "
					<a href=\"modules/personnel.php\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-Personnel.png\" alt=\"Personnel Icon\" name=\"Personnel Icon\">
						<p>Manage Personnel</p>
					</a>
	";
}

//bug - for 3rd ID so all have drill report access
if (in_array('TALI_Personnel',$module_array,true)) {
	echo "
					<a href=\"modules/personnel.php?sub=drillreports\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Personnel-DrillReports.png\" alt=\"Drill Reports Icon\" name=\"Drill Reports Icon\">
						<p>Manage Drill Reports (Shortcut)</p>
					</a>
	";
}

if (in_array('TALI_Mailing_List',$module_array,true)) {
	echo "
					<a href=\"modules/mailinglist.php?\" class=\"tali-responsive-icon\">
						<img src=\"images/icons/Index-MailingList.png\" alt=\"Mailing List Icon\" name=\"Mailing List Icon\">
						<p>Manage Mailing List</p>
					</a>
	";
}
echo "
				</div>
			</div>
		</div>
	</main>
";
?>
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ((isset($_POST['newpassword'])) && ($_POST['newpassword'] != "")) {
		$newpassword = $_POST['newpassword'];
		$newpassword_sql = htmlspecialchars($newpassword);
		$newpassword_sql = TALI_quote_smart($newpassword_sql, $db_handle);
		
		$username = $_SESSION['TALI_Username'];
		$username_sql = htmlspecialchars($username);
		$username_sql = TALI_quote_smart($username_sql, $db_handle);
		
		$SQL = "UPDATE tali_admin_accounts SET password=md5($newpassword_sql) WHERE username=$username_sql";
		$result = mysqli_query($db_handle, $SQL);
		
		header ("Location: index.php");
		exit();
	}
}
else
{
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Account Management</h1>
					<p>On this page you can manage your personal account settings.</p>
				</div>
				
				<div class=\"tali-page-frame\">
					<h1>Change Password</h1>
					<p>Your Account Name: ".$_SESSION['TALI_Username']."</p>
					<p>New Password: <input type=\"password\" class=\"tali_account_passwordinput\"  name=\"newpassword\" form=\"tali_account_form\" value=\"\"></p>
					<form method=\"POST\" id=\"tali_account_form\" action=\"account.php\">
						<p><input type=\"Submit\" Name=\"btnPassword\" value=\"Submit New Password\"></p>
					</form>
	";
			
	
	echo "
					</div>
				</div>
			</div>
		</main>
	";
}
?>
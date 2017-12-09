<?php
//Empty defines
$uname = "";
$pword = "";
$displayMessage = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

//Password reset link clicked
if (isset($_GET['token'])) {
	$token = $_GET['token'];
	$token_sql = htmlspecialchars($token);
	$token_sql = TALI_quote_smart($token_sql, $db_handle);
	$SQL = "SELECT * FROM tali_admin_accounts WHERE password_reset_token=$token_sql";
	$result = mysqli_query($db_handle, $SQL);
	$num_rows = mysqli_num_rows($result);
	if ($num_rows > 0) {
		//Token is valid
		//Simulate login and find account
		$_SESSION['login'] = TRUE;
		$db_field = mysqli_fetch_assoc($result);
		$safe_uname = $db_field['username'];	
		$safe_uname = htmlspecialchars($safe_uname);
		$_SESSION['username'] = $safe_uname;
		$_SESSION['username_id'] = $db_field['id'];	
		$_SESSION['level'] = $db_field['level'];
		
		//Remove the token
		$SQL = "UPDATE tali_admin_accounts SET password_reset_token = '' WHERE password_reset_token = $token_sql";
		$result = mysqli_query($db_handle, $SQL);
		
		//Head to account.php to change password
		header ("Location: account.php");
		exit();
	}
	//Token wasn't valid
	$displayMessage = "That token is not valid!";
}

//A button was clicked
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['btnSubmit'])) {
		//Submit button clicked
		$uname = $_POST['username'];
		$pword = $_POST['password'];
		$uname = htmlspecialchars($uname);
		$pword = htmlspecialchars($pword);
		$uname = TALI_quote_smart($uname, $db_handle);
		$pword = TALI_quote_smart($pword, $db_handle);
		
		//Using BINARY makes it case-sensitive
		$SQL = "SELECT * FROM tali_admin_accounts WHERE BINARY (username = $uname OR email = $uname) AND BINARY password = md5($pword)";
		$result = mysqli_query($db_handle, $SQL);
		$num_rows = mysqli_num_rows($result);
		
		if ($num_rows > 0) {
			//Login successful
			$_SESSION['login'] = TRUE;
			$db_field = mysqli_fetch_assoc($result);
			$safe_uname = $db_field['username'];	
			$safe_uname = htmlspecialchars($safe_uname);
			$_SESSION['username'] = $safe_uname;
			$_SESSION['username_id'] = $db_field['id'];	
			$_SESSION['level'] = $db_field['level'];
			//Redirect to index.php, where access will now be granted
			header ("Location: index.php");
			exit();
		}
		else 
		{
			//Login failed
			$_SESSION['login'] = FALSE;
			$displayMessage = "Invalid username and/or password.
			<br/>
			<br/>
			<input type=\"Submit\" Name=\"btnReset\" value=\"Reset Password\">
			";
			//Upon loading page output, username will remain as-entered but
			//password will be blank
			$uname = $_POST['username'];
			$pword = "";
		}
	}
	else
	{
		//Reset Password button clicked
		$uname = $_POST['username'];
		$uname = htmlspecialchars($uname);
		$uname = TALI_quote_smart($uname, $db_handle);
		//Verify username/e-mail is in database somewhere
		$SQL = "SELECT * FROM tali_admin_accounts WHERE BINARY (username = $uname OR email = $uname)";
		$result = mysqli_query($db_handle, $SQL);
		$num_rows = mysqli_num_rows($result);
		
		if ($num_rows > 0) {
			//Username/e-mail exists, so follow through
			$db_field = mysqli_fetch_assoc($result);
			$email = $db_field['email'];
			$username = $db_field['username'];
			//Generate token
			$characters = "abcdefghijklmnopqrstuvwxyz0123456789";
			$token = "";
			$max = strlen($characters) - 1;
			for ($i = 0; $i < 10; $i++) {
				$token .= $characters[mt_rand(0, $max)];
			}
			
			//Create URL for password reset
			$reset_url = $_SESSION['TALI_Domain_URL'] . $_SESSION['TALI_ROOT_URL'] . "/login.php?token=$token";
			
			//Add password reset token to admin account
			$token_sql = htmlspecialchars($token);
			$token_sql = TALI_quote_smart($token_sql, $db_handle);
			$SQL = "UPDATE tali_admin_accounts SET password_reset_token=$token_sql WHERE BINARY (username = $uname OR email = $uname)";
			$result = mysqli_query($db_handle, $SQL);
			
			//E-mail the token
			$toEmail = $email;
			$toName = $username;
			$subject = "".$_SESSION['TALI_Organization_Name']." TALI Password Reset";
			$msgBody = "In order to reset your ".$_SESSION['TALI_Organization_Name']." TALI password, please click the following link. This link can only be used once and should not be shared.
			
			$reset_url
			";
			require "Includes/Global/EMail/email.php";
		}
		
		//Display confirmation message, whether or not e-mail was actually sent
		$_SESSION['login'] = FALSE;
		$displayMessage = "An e-mail has been sent to the address associated with the entered username.
		<br/>
		<br/>
		<input type=\"Submit\" Name=\"btnReset\" value=\"Reset Password\">
		";
		$uname = $_POST['username'];
		$pword = "";
	}
}
else
{
	if (!((isset($_SESSION['login'])) && ($_SESSION['login']))) {
		$displayMessage .= "
		You must login to your privileged account to access TALI.
		<br/>
		<br/>
		<input type=\"Submit\" Name=\"btnReset\" value=\"Reset Password\">
		";
	}
	else
	{
		session_destroy();
		session_unset();
		$displayMessage = "You have been logged out of TALI.";
	}
}

echo "
	<div class=\"content PageFrame\">
		<h1><strong>Team Administration/Logistics Interface (TALI)</strong></h1>
		<form name=\"form1\" method=\"POST\" action=\"login.php\">

			&nbsp;&nbsp;&nbsp;Username: <input type=\"text\" name=\"username\" class=\"logtex1\" value=\"$uname\" maxlength=\"40\">
			<br/>
			&nbsp;&nbsp;&nbsp;Password: <input type=\"password\" name=\"password\" class=\"logtex2\" value=\"$pword\" maxlength=\"40\">
			
			<p align = center>
			<input type=\"Submit\" Name=\"btnSubmit\" value=\"Login\">
			</p>
			<p style=\"color:red\">$displayMessage</p>
		</form>
	</div>
";
?>
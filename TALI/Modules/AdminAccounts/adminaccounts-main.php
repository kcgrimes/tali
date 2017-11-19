<?php	
//Stock variables
$newlevel = "";
$newusername = "";
$newemail = "";
$newpersonnel_id = "";
$delid = "";
$module = "TALI_Admin_Accounts";
$errorMessage = "";
$successMessage = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Check if delete
	if (isset($_POST['delid'])) {
		$delid = $_POST['delid'];
		if ($delid == "") {
			$errorMessage = "ERROR: You didn't enter an ID to delete!";
			goto POSTErrored;
		}
		//Check if ID exists to be deleted
		$SQL = "SELECT username FROM tali_admin_accounts WHERE id=$delid";
		$result = mysqli_query($db_handle, $SQL);
		$num_rows = mysqli_num_rows($result);
		if ($num_rows < 1) {
			$errorMessage = "ERROR: The ID you have chosen to delete does not exist!";
			goto POSTErrored;
		}
		$db_field = mysqli_fetch_assoc($result);
		$delusername=$db_field['username'];	
		
		TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_admin_accounts', 'id', $delid, 'Admin Account ID#', 'username');
		
		$delSQL = "DELETE FROM tali_admin_accounts WHERE id = $delid";
		$delresult = mysqli_query($db_handle, $delSQL);
	
		$successMessage = "Admin Account $delusername deleted!";

		$delid = "";
	}
	
	//Check if new
	if ((isset($_POST['newlevel'])) && (isset($_POST['newusername'])) && (isset($_POST['newemail']))) {
		$newlevel = $_POST['newlevel'];
		//Round value to prevent silliness aka decimals
		$newlevel = round($newlevel);
		$newusername = $_POST['newusername'];
		$newemail = $_POST['newemail'];
		$newpersonnel_id = $_POST['newpersonnel_id'];
		if ($newusername != "") {
			$SQL = "SELECT level FROM tali_admin_permissions";
			$result = mysqli_query($db_handle, $SQL);
			$maxlevel = mysqli_num_rows($result);
			//Force correction of invalid entry to be the lowest available permission level
			if (($newlevel < 1) OR (($newlevel - $maxlevel) > 0)) {
				$newlevel = $maxlevel;
			}
			
			$newlevel_sql = TALI_quote_smart($newlevel, $db_handle);
			
			$newusername_sql = htmlspecialchars($newusername);
			$newusername_sql = TALI_quote_smart($newusername_sql, $db_handle);
			
			//Check if username exists
			$SQL = "SELECT id FROM tali_admin_accounts WHERE username=$newusername_sql";
			$result = mysqli_query($db_handle, $SQL);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$errorMessage = "ERROR: The username you have entered already exists.";
				goto POSTErrored;
			}
			
			//Generate random password
			$characters = "abcdefghijklmnopqrstuvwxyz0123456789";
			$newpassword = "";
			$max = strlen($characters) - 1;
			for ($i = 0; $i < 10; $i++) {
				$newpassword .= $characters[mt_rand(0, $max)];
			}
			$newpassword_sql = htmlspecialchars($newpassword);
			$newpassword_sql = TALI_quote_smart($newpassword_sql, $db_handle);
			
			$newemail_sql = htmlspecialchars($newemail);
			$newemail_sql = TALI_quote_smart($newemail_sql, $db_handle);
			
			//Check if email exists
			$SQL = "SELECT id FROM tali_admin_accounts WHERE email=$newemail_sql";
			$result = mysqli_query($db_handle, $SQL);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$errorMessage = "ERROR: The e-mail you have entered already exists.";
				goto POSTErrored;
			}
			
			//Manage personnel id association
			$newpersonnel_id_sql = htmlspecialchars($newpersonnel_id);
			$newpersonnel_id_sql = TALI_quote_smart($newpersonnel_id_sql, $db_handle);
			if ($newpersonnel_id != "") {
				//Indicates intent to associate personnel id
				//Check if personnel_id already associated somewhere
				$SQL = "SELECT id FROM tali_admin_accounts WHERE personnel_id=$newpersonnel_id_sql";
				$result = mysqli_query($db_handle, $SQL);
				$num_rows = mysqli_num_rows($result);
				if ($num_rows > 0) {
					$errorMessage = "ERROR: The Personnel ID you have selected is already associated to an Admin Account!";
					goto POSTErrored;
				}
				//Check if personnel_id is used for any personnel
				$SQL = "SELECT personnel_id FROM tali_personnel_roster WHERE personnel_id=$newpersonnel_id_sql";
				$result = mysqli_query($db_handle, $SQL);
				$num_rows = mysqli_num_rows($result);
				if ($num_rows < 1) {
					$errorMessage = "ERROR: The Personnel ID you have selected does not exist!";
					goto POSTErrored;
				}
				//Update email of associated personnel file
				$SQL = "UPDATE tali_personnel_roster SET email=$newemail_sql WHERE personnel_id=$personnel_id";
				$result = mysqli_query($db_handle, $SQL);
			}
			else
			{
				//No personnel id to be associated, so set as empty
				$newpersonnel_id = "";
			}
			
			$SQL = "INSERT INTO tali_admin_accounts (level, username, password, email, personnel_id) VALUES ($newlevel_sql, $newusername_sql, md5($newpassword_sql), $newemail_sql, $newpersonnel_id_sql)";
			$result = mysqli_query($db_handle, $SQL);
			
			$SQL = "SELECT id FROM tali_admin_accounts ORDER BY id DESC LIMIT 1";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$newid=$db_field['id'];	
			
			TALI_Create_History_Report('created', $module, $db_handle, 'tali_admin_accounts', 'id', $newid, 'Admin Account ID#', 'username');
			
			$successMessage = "Admin Account $newusername created!";
			
			$newid = "";
			$newlevel = "";
			$newusername = "";
			$newpassword = "";
			$newemail = "";
			$newpersonnel_id = "";
		}
	}
	
	//Check if update
	if ((isset($_POST['tali_adminaccounts_updateaccount'])) && (isset($_POST['id']))) {
		//Verify ID
		if ($_POST['id'] == "") {
			$errorMessage = "ERROR: You must enter an Admin Account ID in order to modify an Admin Account!";
			goto POSTErrored;
		}
		$id=$_POST['id'];
		$SQL = "SELECT id FROM tali_admin_accounts WHERE id=$id";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$num_rows = mysqli_num_rows($result);
		if ($num_rows < 1) {
			$errorMessage = "ERROR: The Admin Account ID you have entered does not exist!";
			goto POSTErrored;
		}
		
		//Manage level
		if ((isset($_POST['level'])) && (($_POST['level'] != ""))) {
			$newlevel = $_POST['level'];
			//Round value to prevent silliness aka decimals
			$newlevel = round($newlevel);
			$SQL = "SELECT level FROM tali_admin_permissions";
			$result = mysqli_query($db_handle, $SQL);
			$maxlevel = mysqli_num_rows($result);
			//Force correction of invalid entry to be the lowest available permission level
			if (($newlevel < 0) OR (($newlevel - $maxlevel) > 0)) {
				$newlevel = $maxlevel;
			}
		}
		
		//Manage username
		if ((isset($_POST['username'])) && (($_POST['username'] != ""))) {
			$username = $_POST['username'];
			//Check if username already exists
			$newusername_sql = htmlspecialchars($username);
			$newusername_sql = TALI_quote_smart($newusername_sql, $db_handle);
			$SQL = "SELECT id FROM tali_admin_accounts WHERE username=$newusername_sql";
			$result = mysqli_query($db_handle, $SQL);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$errorMessage = "ERROR: The username you have selected already exists!";
				goto POSTErrored;
			}
		}
		
		//Manage email
		if ((isset($_POST['email'])) && ($_POST['email'] != "")) {
			$newemail = $_POST['email'];
			//Check if email already exists
			$newemail_sql = htmlspecialchars($newemail);
			$newemail_sql = TALI_quote_smart($newemail_sql, $db_handle);
			$SQL = "SELECT id FROM tali_admin_accounts WHERE email=$newemail_sql";
			$result = mysqli_query($db_handle, $SQL);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$errorMessage = "ERROR: The e-mail you have selected already exists!";
				goto POSTErrored;
			}
		}
		
		//Manage personnel_id
		if ((isset($_POST['personnel_id'])) && ($_POST['personnel_id'] != "")) {
			//Personnel id present, intended to associate admin account
			$newpersonnel_id = $_POST['personnel_id'];
			//Check if personnel_id already associated somewhere
			$newpersonnel_id_sql = htmlspecialchars($newpersonnel_id);
			$newpersonnel_id_sql = TALI_quote_smart($newpersonnel_id_sql, $db_handle);
			$SQL = "SELECT id FROM tali_admin_accounts WHERE personnel_id=$newpersonnel_id_sql";
			$result = mysqli_query($db_handle, $SQL);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$errorMessage = "ERROR: The Personnel ID you have selected is already associated to an Admin Account!";
				goto POSTErrored;
			}
			//Check if personnel_id is used for any personnel
			$SQL = "SELECT personnel_id FROM tali_personnel_roster WHERE personnel_id=$newpersonnel_id_sql";
			$result = mysqli_query($db_handle, $SQL);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows < 1) {
				$errorMessage = "ERROR: The Personnel ID you have selected does not exist!";
				goto POSTErrored;
			}
		}
						
		if ((isset($_POST['level'])) && (($_POST['level'] != ""))) {
			$newlevel_sql = TALI_quote_smart($newlevel, $db_handle);
			$SQL = "UPDATE tali_admin_accounts SET level=$newlevel_sql WHERE id=$id";
			$result = mysqli_query($db_handle, $SQL);
		}
		
		if ((isset($_POST['username'])) && (($_POST['username'] != ""))) {
			$username_sql = htmlspecialchars($username);
			$username_sql = TALI_quote_smart($username_sql, $db_handle);
			$SQL = "UPDATE tali_admin_accounts SET username=$username_sql WHERE id=$id";
			$result = mysqli_query($db_handle, $SQL);
		}
		
		if ((isset($_POST['email'])) && (($_POST['email'] != ""))) {
			$newemail_sql = htmlspecialchars($newemail);
			$newemail_sql = TALI_quote_smart($newemail_sql, $db_handle);
			$SQL = "UPDATE tali_admin_accounts SET email=$newemail_sql WHERE id=$id";
			$result = mysqli_query($db_handle, $SQL);
			
			//Also update new e-mail to associated personnel file, if applicable
			$SQL = "UPDATE tali_personnel_roster SET email=$newemail_sql WHERE personnel_id=$newpersonnel_id";
			$result = mysqli_query($db_handle, $SQL);
		}
		
		if ((isset($_POST['personnel_id'])) && (($_POST['personnel_id'] != ""))) {
			$SQL = "UPDATE tali_admin_accounts SET personnel_id=$newpersonnel_id_sql WHERE id=$id";
			$result = mysqli_query($db_handle, $SQL);
		}
			
		//History Report
		TALI_Create_History_Report('edited', $module, $db_handle, 'tali_admin_accounts', 'id', $id, 'Admin Account ID#', 'username');
		
		$successMessage = "Admin Account #$id updated!";
		
		$newlevel = "";
		$username = "";
		$newusername = "";
		$newemail = "";
		$newpersonnel_id = "";
	}
}

//GoTo from event where error occurred in POST
POSTErrored:

echo "
<div class=\"content PageFrame\">
	<h1><strong>Manage Admin Accounts</strong></h1>
";

if ($errorMessage != "") {
	echo "
		<p><font color=\"red\">$errorMessage</font></p>
	";
};

if ($successMessage != "") {
	echo "
		<p><font color=\"green\">$successMessage</font></p>
	";
};

echo "
	<form method=\"POST\" id=\"tali_adminaccounts_update_form\" action=\"adminaccounts.php\"></form>
	<table style=\"width:94%\" class=\"adminaccountstb\">
	<col width=\"5%\">
	<col width=\"5%\">
	<col width=\"40%\">
	<col width=\"40%\">
	<col width=\"10%\">
	<tr>
		<th>ID</th>
		<th>Lvl</th>
		<th>Username</th>
		<th>E-Mail</th>
		<th>Pers. ID</th>
	</tr>
";

$SQL = "SELECT * FROM tali_admin_accounts ORDER BY level ASC, username ASC";
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);
$id = "";
while ($db_field = mysqli_fetch_assoc($result)) {
	$id=$db_field['id'];
	$level=$db_field['level'];
	$username=$db_field['username'];
	$email=$db_field['email'];
	$personnel_id=$db_field['personnel_id'];
	echo "
		<tr>
			<td>$id</td>
			<td>$level</td>
			<td>$username</td>
			<td>$email</td>
			<td>$personnel_id</td>
		</tr>
	";
}
$id = "";
$level = "";
$username = "";
$email = "";
$personnel_id = "";
echo "
		<tr>
			<td>Select ID</td>
			<td>Select Lvl</td>
			<td>Enter new username:</td>
			<td>Enter new e-mail:</td>
			<td>Pers. ID:</td>
		</tr>
		<tr>
			<td><input type=\"integer\" class=\"bo\" name=\"id\" form=\"tali_adminaccounts_update_form\" maxlength=\"4\" value=\"$id\"></td>
			<td><input type=\"integer\" class=\"bo\" name=\"level\" form=\"tali_adminaccounts_update_form\" maxlength=\"2\" value=\"$level\"></td>
			<td><input type=\"text\" class=\"fo\" name=\"username\" form=\"tali_adminaccounts_update_form\" value=\"$username\"></td>
			<td><input type=\"text\" class=\"fo\"  name=\"email\" form=\"tali_adminaccounts_update_form\" value=\"$email\"></td>
			<td><input type=\"integer\" class=\"bo\" name=\"personnel_id\" form=\"tali_adminaccounts_update_form\" maxlength=\"4\" value=\"$personnel_id\"></td>
		</tr>
";

echo "
		</table>
		<table style=\"width:94%\" class=\"adminaccountstbbut\">
			<tr>
				<td><input type=\"submit\" class=\"bu\" Name=\"tali_adminaccounts_updateaccount\" form=\"tali_adminaccounts_update_form\" value=\"Update Account\"></td>
				<td style=\"text-align:right\">
					<form method=\"POST\" id=\"tali_adminaccounts_form_del\" action=\"adminaccounts.php\">
						Enter the ID of an account above to delete it:
						<input type=\"integer\" class=\"tb\" maxlength=\"4\" name=\"delid\" value=\"$delid\">
						<input type=\"Submit\" class=\"but\" Name=\"tali_adminaccounts_delaccount\" value=\"Delete Account\">
					</form>
				</td>
			</tr>
		</table>
	</div>
	<div class=\"content PageFrame\">
		<h1><strong>Add New Admin Accounts</strong></h1>
		<form method=\"POST\" id=\"tali_adminaccounts_form\" action=\"adminaccounts.php\"></form>
		<table style=\"width:94%\" class=\"adminaccountstb\">
		<col width=\"5%\">
		<col width=\"5%\">
		<col width=\"40%\">
		<col width=\"40%\">
		<col width=\"10%\">
		<tr>
			<th>ID</th>
			<th>Lvl</th>
			<th>Username</th>
			<th>E-Mail</th>
			<th>Pers. ID</th>
		</tr>
		<tr>
			<td>ID Auto-Defined</td>
			<td>Enter admin level:</td>
			<td>Enter a username:</td>
			<td>Enter an e-mail:</td>
			<td>Associated Personnel ID:</td>
		</tr>
		<tr>
			<td>
				
			</td>
			<td>
				<input type=\"integer\" class=\"bo\" name=\"newlevel\" form=\"tali_adminaccounts_form\" maxlength=\"4\" value=\"\">
			</td>
			<td>
				<input type=\"text\" class=\"fo\" name=\"newusername\" form=\"tali_adminaccounts_form\" value=\"\">
			</td>
			<td>
				<input type=\"text\" class=\"fo\"  name=\"newemail\" form=\"tali_adminaccounts_form\" value=\"\">
			</td>
			<td>
				<input type=\"integer\" class=\"bo\" name=\"newpersonnel_id\" form=\"tali_adminaccounts_form\" maxlength=\"4\" value=\"\">
			</td>
		</tr>
		</table>
		<td><input type=\"Submit\" class=\"bu\" Name=\"tali_adminaccounts_addaccount\" form=\"tali_adminaccounts_form\" value=\"Add New Account\"></td>
		<br/>
	</div>
";
?>

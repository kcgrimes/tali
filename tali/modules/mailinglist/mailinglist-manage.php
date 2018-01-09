<?php
//Stock variables
$module = "TALI_Mailing_List";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

//Return - Add/Edit Submit button clicked
if (isset($_POST['btnSubmit'])) {
	//Retrieve variables from post
	$name = $_POST['name'];
	$list = $_POST['list'];
	//Check if both fields were filled out
	if (($name != "") && ($list != "")) {
		//Both variables were filled in
		//Determine if submitting Add or Edit
		if (($_GET['action']) == "Add") {
			//Submit Add
			//Make the variables safe
			$name_sql = htmlspecialchars($name);
			$name_sql = TALI_quote_smart($name_sql, $db_handle);
			
			$list_sql = htmlspecialchars($list);
			$list_sql = TALI_quote_smart($list_sql, $db_handle);
			
			//Insert safe values into database
			$SQL = "INSERT INTO tali_mailing_list (name, list) VALUES ($name_sql, $list_sql)";
			$result = mysqli_query($db_handle, $SQL);
			
			//Collect new id for history report
			$SQL = "SELECT id FROM tali_mailing_list ORDER BY id DESC LIMIT 1";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$id = $db_field['id'];	
			
			TALI_Create_History_Report('created', $module, $db_handle, 'tali_mailing_list', 'id', $id, 'Mailing List ID#', 'name');
			
			$successMessage = "Mailing List $name created!";
		}
		else
		{
			//Submit Edit
			$id = $_POST['id'];
			//Make the variables safe
			$name_sql = htmlspecialchars($name);
			$name_sql = TALI_quote_smart($name_sql, $db_handle);
			
			$list_sql = htmlspecialchars($list);
			$list_sql = TALI_quote_smart($list_sql, $db_handle);
			
			//Update databse entry
			$SQL = "UPDATE tali_mailing_list SET name=$name_sql, list=$list_sql WHERE id=$id"; 
			$result = mysqli_query($db_handle, $SQL);
			
			TALI_Create_History_Report('edited', $module, $db_handle, 'tali_mailing_list', 'id', $id, 'Mailing List ID#', 'name');		
		
			$successMessage = "Mailing List $name updated!";
		}
		//Reload for fresh page
		header ("Location: mailinglist.php?sub=manage");
		exit();
	}
	
	$errorMessage = "Error: Both name and list fields must be filled out.";
}

//Return - Delete button clicked
if ((isset($_GET['action'])) && (($_GET['action']) == "Delete")) {
	//Submit Delete
	$id = $_GET['id'];
	
	TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_mailing_list', 'id', $id, 'Mailing List ID#', 'name');
	
	//Delete entry from database
	$delSQL = "DELETE FROM tali_mailing_list WHERE id=$id";
	$delresult = mysqli_query($db_handle, $delSQL);
	
	//Reload for fresh page
	header ("Location: mailinglist.php?sub=manage");
	exit();
}

//Page content

//Define action variable for header and button
$action = "Add";
if (isset($_GET['action'])) {
	$action = $_GET['action'];
}

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Manage Custom Mailing Lists</h1>
				<table id=\"mailinglistTable_manage\" class=\"tali-personnel-table\">
					<col width=\"70%\">
					<col width=\"15%\">
					<col width=\"15%\">
					
					<tr>
						<th>List Name</th>
						<th>Edit</th>
						<th>Delete</th>
					</tr>
";

$SQL = "SELECT * FROM tali_mailing_list";
$result = mysqli_query($db_handle, $SQL);
while ($db_field = mysqli_fetch_assoc($result)) {
	$mailinglist_id = $db_field['id'];
	$list_name = $db_field['name'];
			
	echo "
					<tr>
						<td style=\"text-align:left;\">$list_name</td>
						<td style=\"text-align:center;\">
							<a href=\"mailinglist.php?sub=manage&action=Edit&id=$mailinglist_id\">
								<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
							</a>
						</td>
						<td style=\"text-align:center;\">
							<a href=\"mailinglist.php?sub=manage&action=Delete&id=$mailinglist_id\" onclick=\"return confirm('Are you sure you want to delete this custom mailing list?');\">
								<img src=\"../images/icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
							</a>
						</td>
					</tr>
	";
};

echo "
				</table>
			</div>
			
			<div class=\"tali-page-frame\">
				<h1>$action Custom Mailing Lists</h1>
";

//If action is Edit, define the variables so they will be displayed
if ($action == "Edit") {
	$id = $_GET['id'];
	$SQL = "SELECT name,list FROM tali_mailing_list WHERE id=$id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	$name = $db_field['name'];
	$list = $db_field['list'];
}
else
{
	//id not defined yet
	$id = "";
	if ((isset($_GET['action'])) && ($_GET['action'] == "Add")) {
		//Failed attempt to add, so don't blank the variables
	}
	else
	{
		//Default action is Add, so variables empty
		$name = "";
		$list = "";
	}
}

echo "
				<p>Mailing List Name:</p>
				<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"name\" form=\"form_mailinglist\" value=\"$name\">
				
				<p>Mailing List Addresses (separated by comma \",\"):</p>
				<p><textarea rows=\"6\" cols=\"75\" name=\"list\" form=\"form_mailinglist\">$list</textarea></p>
				
				<input type=\"hidden\" name=\"id\" form=\"form_mailinglist\" value=\"$id\">
				<form method=\"POST\" id=\"form_mailinglist\" action=\"mailinglist.php?sub=manage&action=$action\">
					<p>
					<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\"/>
					</p>
				</form>
			</div>
		</div>
	</main>
";
?>
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
	if (($_GET['action']) == "Add") {
		//Add
		
	}
	else
	{
		//Edit
		
	}
}

//Return - Delete button clicked
if ((isset($_GET['action'])) && (($_GET['action']) == "Delete")) {
	//Delete
	
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
	$name = $db_field['name'];
			
	echo "
					<tr>
						<td style=\"text-align:left;\">$name</td>
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

//Default action is Add, so variables empty
$id = "";
$name = "";
$list = "";

//If action is Edit, define the variables so they will be displayed
if ($action == "Edit") {
	$id = $_GET['id'];
	$SQL = "SELECT * FROM tali_mailing_list WHERE id=$id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	$name = $db_field['name'];
	$list = $db_field['list'];
}

echo "
				<p>Mailing List Name:</p>
				<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"name\" form=\"form_mailinglist\" value=\"$name\">
				
				<p>Mailing List Addresses (separated by comma \",\"):</p>
				<p><textarea rows=\"6\" cols=\"75\" name=\"list\" form=\"form_mailinglist\">$list</textarea></p>
				
				<form method=\"POST\" id=\"form_mailinglist\" action=\"mailinglist.php?sub=manage&action=$action&id=$id\">
					<p>
					<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\"/>
					</p>
				</form>
			</div>
		</div>
	</main>
";
?>
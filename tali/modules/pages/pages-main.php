<?php
//Stock variables
$newtitle = "";
$displayMessage = "";
$newdisplayMessage = "";
$editdeldisplayMessage = "";
$module = "TALI_Pages";
			
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

//MarkitUp
markItUp_editing ();
	
if (!isset($_POST['tali_page_edit'])) {
	if (!isset($_GET["id"])) {
		if (($_SERVER['REQUEST_METHOD'] == 'POST') && (!isset($_POST['btnCancel']))) {
			//Check if new
			if (isset($_POST['tali_pages_newpagetitle'])) {
				$newtitle = $_POST['newentrytitle'];
				if ($newtitle != "") {
					$newtitle = htmlspecialchars($newtitle);
					$newtitle = TALI_quote_smart($newtitle, $db_handle);
										
					$SQL = "INSERT INTO tali_pages (title, time) VALUES ($newtitle, CURRENT_TIMESTAMP)";
					$result = mysqli_query($db_handle, $SQL);
					
					$SQL = "SELECT id FROM tali_pages ORDER BY id DESC LIMIT 1";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$newid=$db_field['id'];	
					
					$newHistory = TALI_Create_History_Report('created', $module, $db_handle, 'tali_pages', 'id', $newid, 'Page ID#', 'title');
					
					//Local history report
					$newHistory = TALI_quote_smart($newHistory, $db_handle);
					$SQL = "UPDATE tali_pages SET history=$newHistory WHERE id=$newid"; 
					$result = mysqli_query($db_handle, $SQL);
					
					$newtitle = "";
				}
				else
				{
					$newdisplayMessage = "You must enter text in the title fields in order to create a new page!";
				}
			}
			//Check if delete
			if (isset($_POST['tali_pages_delpage'])) {
				$delid = $_POST['selectitle'];
				
				$newHistory = TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_pages', 'id', $delid, 'Page ID#', 'title');
				
				$delSQL = "DELETE FROM tali_pages WHERE ID = $delid";
				$delresult = mysqli_query($db_handle, $delSQL);
				header ("Location: pages.php");
				exit();
			}
			//Check if edit
			if (isset($_POST['tali_pages_editpage'])) {
				$id = $_POST['selectitle'];
				header ("Location: pages.php?id=$id");
				exit();
			}
		}
	
		echo "
			<main>
				<div class=\"tali-container\">
					<div class=\"tali-page-frame\">
						<h1>Manage Pages</h1>
						<p>This page allows you to create, edit, and delete pages.</p>
					</div>
					
					<div class=\"tali-page-frame\">
						<h1>Create Page</h1>
						<p>To create a new page, enter the desired page title below and click Create New Page.</p>
						<form method=\"POST\" id=\"tali_newpage_form\" action=\"pages.php\">
							<p>
							<input type=\"text\" name=\"newentrytitle\" value=\"$newtitle\">
							<font color=\"red\">$newdisplayMessage</font>
							<br/>
							<br/>
							<input type=\"Submit\" name=\"tali_pages_newpagetitle\" value=\"Create New Page\">
							</p>
						</form>
					</div>
					
					<div class=\"tali-page-frame\">
						<h1>Edit/Delete Page</h1>
						<p>Select the desired page from the dropdown menu below and click Edit to update it or Delete to remove it from the database.</p>
						<form method=\"POST\" id=\"tali_editpage_form\" action=\"pages.php\">
							<p>
							<select name=\"selectitle\">
								<option value=\"empty\"></option>
		";
		
		$SQL = "SELECT * FROM tali_pages ORDER BY title ASC";
		$result = mysqli_query($db_handle, $SQL);
		while ($db_field = mysqli_fetch_assoc($result)) {
			$id=$db_field['id'];
			$title=$db_field['title'];
			echo "
								<option value=\"$id\">$title</option>
			";
		}
						
		echo "
							</select>
							<br/>
							<font color=\"red\">$editdeldisplayMessage</font>
							<br/>
							<input type=\"Submit\" name=\"tali_pages_editpage\" value=\"Edit Page\">
							<input type=\"Submit\" name=\"tali_pages_delpage\" onclick=\"return confirm('Are you sure you want to delete the selected page?');\" value=\"Delete Page\">
							</p>
						</form>
					</div>
				</div>
			</main>
		";
	}
	else
	{
		$id = $_GET["id"];
		$editSQL = "SELECT * FROM tali_pages WHERE id = $id";
		$result = mysqli_query($db_handle, $editSQL);
		if (is_bool($result)) {
			header ("Location: pages.php");
			exit();
		}
		$db_field = mysqli_fetch_assoc($result);
		$time=$db_field['time'];
		$title=$db_field['title'];
		$body=$db_field['body'];
		//bug - look into formatting/css of markitup textarea box
		echo "
			<main>
				<div class=\"tali-container\">
					<div class=\"tali-page-frame\">
						<h1>Editing Page: $title</h1>
						<form method=\"POST\" id=\"tali_editpage_form\" action=\"pages.php\">
							<p>
							<input type=\"hidden\" name=\"editpageid\" value=\"$id\">
							<strong>Title</strong>
							<br/>
							<input type=\"text\" name=\"editpagetitle\"  value=\"$title\">
							</p>
							<p>
							<strong>Body</strong>
							<br/>
							<textarea class=\"tali-pages-input-body\" id=\"html\" name=\"editpagebody\">$body</textarea>
							</p>
							<p>
							<font color=\"red\">$displayMessage</font>
							</p>
							<p>
							<input type=\"Submit\" name=\"tali_page_edit\" value=\"Submit\">
							<input type=\"submit\" name=\"btnCancel\" value=\"Cancel\"/>
							</p>
						</form>
					</div>
		";
		
		//Display History Report
		$historySQL = "SELECT history FROM tali_pages WHERE id = $id";
					
		$historyresult = mysqli_query($db_handle, $historySQL);
		
		$db_field = mysqli_fetch_assoc($historyresult);
		$history=$db_field['history'];
		
		echo "
					<div class=\"tali-page-frame\">
						<h1>History Report</h1>
						<p>$history</p>
					</div>
				</div>
			</main>
		";
	}
}
else
{
	$editid = $_POST['editpageid'];
	$edittitle = $_POST['editpagetitle'];
	$editbody = $_POST['editpagebody'];
	$edittitle_sql = htmlspecialchars($edittitle);
	$edittitle_sql = TALI_quote_smart($edittitle_sql, $db_handle);
	
	$editbody_sql = TALI_quote_smart($editbody, $db_handle);
	$editbody_sql = htmlspecialchars($editbody_sql);
		
	$SQL = "UPDATE tali_pages SET title=$edittitle_sql, body=$editbody_sql, time=CURRENT_TIMESTAMP WHERE id=$editid"; 
	$result = mysqli_query($db_handle, $SQL);
	
	$newHistory = TALI_Create_History_Report('edited', $module, $db_handle, 'tali_pages', 'id', $editid, 'Page ID#', 'title');
					
	//Local history report
	$SQL = "SELECT history FROM tali_pages WHERE id=$editid";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	$history=$db_field['history'];
	$newHistory = "$history <br/> $newHistory";
	$newHistory = TALI_quote_smart($newHistory, $db_handle);
	$SQL = "UPDATE tali_pages SET history=$newHistory WHERE id=$editid"; 
	$result = mysqli_query($db_handle, $SQL);
	
	$editid = "";
	$edittitle = "";
	$editbody = "";
	header ("Location: pages.php");
	exit();
}
?>
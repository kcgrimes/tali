<?php
//Stock variables
$newid = "";
$newauthor = ""; 
$newtitle = "";
$newbody = "";
$displayMessage = "";
$headingTitle = "Add News Entry";
$updateSpecButton = "";
$action = "news.php?action=new";
$module = "TALI_News";

//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
		
TALI_sessionCheck($module, $db_handle);

//MarkitUp
markItUp_editing ();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Check if cancel
	if (isset($_POST['btnCancel'])) {
		header ("Location: news.php");
		exit();
	}
	
	//Check if delete
	if ((isset($_POST['btnDelete'])) && (isset($_GET['id']))) {
		$delid = $_GET['id'];
		$delid = htmlspecialchars($delid);
		$delid = TALI_quote_smart($delid, $db_handle);
		
		TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_news', 'ID', $delid, 'News Entry ID#', 'title');
		
		$delSQL = "DELETE FROM tali_news WHERE ID = $delid";
		$delresult = mysqli_query($db_handle, $delSQL);
		header ("Location: news.php");
		exit();
	}

	//Check if new or update
	if ((isset($_GET['action'])) && (isset($_POST['newentryauthor'])) && (isset($_POST['newentrytitle'])) && (isset($_POST['newentrybody']))) {
		$newauthor = $_POST['newentryauthor'];
		$newtitle = $_POST['newentrytitle'];
		$newbody = $_POST['newentrybody'];
		//news.php?action=new - New entry
		if (($_GET['action']) == "new") {
			if (($newauthor != "") && ($newtitle != "") && ($newbody != "")) {
				$newauthor_sql = htmlspecialchars($newauthor);
				$newauthor_sql = TALI_quote_smart($newauthor_sql, $db_handle);
				$newtitle_sql = htmlspecialchars($newtitle);
				$newtitle_sql = TALI_quote_smart($newtitle_sql, $db_handle);
				
				$newbody_sql = htmlspecialchars($newbody);
				$newbody_sql = TALI_quote_smart($newbody_sql, $db_handle);
				
				$SQL = "INSERT INTO tali_news (author, time, title, body) VALUES ($newauthor_sql, CURRENT_TIMESTAMP, $newtitle_sql, $newbody_sql)";
				$result = mysqli_query($db_handle, $SQL);
				
				$SQL = "SELECT id FROM tali_news ORDER BY id DESC LIMIT 1";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$newid=$db_field['id'];
				
				$newHistory = TALI_Create_History_Report('created', $module, $db_handle, 'tali_news', 'id', $newid, 'News Entry ID#', 'title');
				
				//Local history report
				$newHistory = TALI_quote_smart($newHistory, $db_handle);
				$SQL = "UPDATE tali_news SET history=$newHistory WHERE id=$newid"; 
				$result = mysqli_query($db_handle, $SQL);
				
				$newid = "";
				$newauthor = "";
				$newtitle = "";
				$newbody = "";
				header ("Location: news.php");
				exit();
			}
			else
			{
				$displayMessage = "You must enter text in the author, title, and body fields in order to add an entry!";
			}
		}
		else
		//news.php?action=edit&id=$id - Update entry
		{
			if (($newauthor != "") && ($newtitle != "") && ($newbody != "")) {
				$newid = $_POST['newentryid'];
				$newauthor = htmlspecialchars($newauthor);
				$newauthor = TALI_quote_smart($newauthor, $db_handle);
				$newtitle = htmlspecialchars($newtitle);
				$newtitle = TALI_quote_smart($newtitle, $db_handle);
				
				$newbody = htmlspecialchars($newbody);
				$newbody = TALI_quote_smart($newbody, $db_handle);

				$SQL = "UPDATE tali_news SET author=$newauthor, title=$newtitle, body=$newbody WHERE ID=$newid"; 

				$result = mysqli_query($db_handle, $SQL);
				
				$newHistory = TALI_Create_History_Report('edited', $module, $db_handle, 'tali_news', 'id', $newid, 'News Entry ID#', 'title');
				
				//Local history report
				$SQL = "SELECT * FROM tali_news WHERE ID=$newid";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$history=$db_field['history'];
				$newHistory = "$history <br/> $newHistory";
				$newHistory = TALI_quote_smart($newHistory, $db_handle);
				$SQL = "UPDATE tali_news SET history=$newHistory WHERE id=$newid"; 
				$result = mysqli_query($db_handle, $SQL);
				
				$newid = "";
				$newauthor = "";
				$newtitle = "";
				$newbody = "";
				header ("Location: news.php");
				exit();
			}
			else
			{
				$headingTitle = "Update News Entry";
				$updateSpecButton = "<input type=\"submit\" name=\"btnDelete\" onclick=\"return confirm('Are you sure you want to delete this news entry?');\" value=\"Delete Entry\"/>
				<input type=\"submit\" name=\"btnCancel\" value=\"Cancel Update\"/>";
				$displayMessage = "You must enter text in the author, title, and body fields in order to update an entry!";
			}
		}
	}
	
	//news.php?action=edit&id=$id - Define variables to fill Update form
	if (($_GET['action']) == "edit") {
		$headingTitle = "Update News Entry"; 
		$newid = $_GET['id'];
		if ((isset($_POST['newentryauthor'])) && (isset($_POST['newentrytitle'])) && (isset($_POST['newentrybody']))) {
			$newauthor = $_POST['newentryauthor'];
			$newtitle = $_POST['newentrytitle'];
			$newbody = $_POST['newentrybody'];
		}
		else
		{
			$newauthor = $_POST['newsauthor'];
			$newtitle = $_POST['newstitle'];
			$newbody = $_POST['newsbody'];
		}
		$updateSpecButton = "<input type=\"submit\" name=\"btnDelete\" onclick=\"return confirm('Are you sure you want to delete this news entry?');\" value=\"Delete Entry\"/>
		<input type=\"submit\" name=\"btnCancel\" value=\"Cancel Update\"/>";
		$action = "news.php?action=edit&id=$newid";
	}
}

//bug - look into formatting/styling of the markitup, and use of width vs cols/rows, etc.
//Primary form for New and Update
echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>$headingTitle</h1>
				<form method=\"POST\" id=\"tali_newsentry_form\" action=\"$action\">
					<p>
					<input type=\"hidden\" name=\"newentryid\" value=\"$newid\">
					<strong>Author</strong>
					<br/>
					<input type=\"text\" name=\"newentryauthor\"  value=\"$newauthor\">
					<br/>
					<strong>Title</strong>
					<br/>
					<input type=\"text\" class=\"tali-news-entry_list-input-title\" name=\"newentrytitle\"  value=\"$newtitle\">
					<br/>
					<strong>Body</strong>
					<br/>
					<textarea cols=\"80\" rows=\"20\" class=\"tali-news-entry_list-input-body\" id=\"html\" name=\"newentrybody\">$newbody</textarea>
					</p>
					<p>
					<font color=\"red\">$displayMessage</font>
					</p>
					<p>
					<input type=\"Submit\" name=\"tali_news_newentry\" value=\"$headingTitle\">
					$updateSpecButton
					</p>
				</form>
			</div>
";
//
		
//news.php?action=edit&id=$id - Display History Report
if ((isset($_GET['action'])) && (($_GET['action']) == "edit")) {
	$histid = $_GET['id'];
	$historySQL = "SELECT history FROM tali_news WHERE id = $histid";
				
	$historyresult = mysqli_query($db_handle, $historySQL);
	
	$db_field = mysqli_fetch_assoc($historyresult);
	$history=$db_field['history'];
	
	echo "
			<div class=\"tali-page-frame\">
				<h1>History Report</h1>
				<p>$history</p>
			</div>
	";
}
else
//news.php - Displays all entries
{
	echo "
			<div class=\"tali-page-frame\">
				<h1>Manage News Entries</h1>
	";

	$SQL = "SELECT * FROM tali_news ORDER BY time DESC";
	$result = mysqli_query($db_handle, $SQL);
	$id = "";
	while ($db_field = mysqli_fetch_assoc($result)) {
		$id=$db_field['id'];
		$time=$db_field['time'];
		$author=$db_field['author'];
		$title=$db_field['title'];
		$body=$db_field['body'];
		echo "
				<form method=\"POST\" id=\"tali_newslist_form\" action=\"news.php?action=edit&id=$id\">
					<table class=\"tali-news-entry_list-table\">
						<col width=\"6%\">
						<col width=\"47%\">
						<col width=\"47%\">
						<tr>
							<th>ID</th>
							<th>Date/Time</th>
							<th>Author</th>
						</tr>
						<tr>
							<td><input type=\"text\" class=\"tali-news-entry_list-table-id\" name=\"newsid\"  value=\"$id\" readonly=\"readonly\"></td>
							<td><input type=\"text\" class=\"tali-news-entry_list-table-time\" name=\"newstime\"  value=\"$time\" readonly=\"readonly\"></td>
							<td><input type=\"text\" class=\"tali-news-entry_list-table-author\" name=\"newsauthor\"  value=\"$author\" readonly=\"readonly\"></td>
						<tr>
					</table>
					<p>
					<strong>Title</strong>
					<br/>
					<input type=\"text\" class=\"tali-news-entry_list-input-title\" name=\"newstitle\" value=\"$title\" readonly=\"readonly\">
					</p>
					<p>
					<strong>Body</strong>
					<br/>
					<textarea readonly class=\"tali-news-entry_list-input-body\" name=\"newsbody\">$body</textarea>
					</p>
					<p>
					<input type=\"Submit\" Name=\"tali_news_editentry\" value=\"Edit Entry\">
					</p>
				</form>
				<br/>
		";
	}
	echo "
			</div>
		</div>
	</main>
	";
}
?>
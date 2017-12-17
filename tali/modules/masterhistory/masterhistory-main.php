<?php
//Stock variables
$module = "TALI_Master_History";
$rowperpage_id = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"managehistory tali-page-frame\">
				<h1>Master History Report</h1>
";

$countSQL = "SELECT id FROM tali_master_history ORDER BY id DESC";
$countresult = mysqli_query($db_handle, $countSQL);
$num_rows = mysqli_num_rows($countresult);

$page = 1;
$rowsperpage = 100;
if (isset($_GET['page'])) {
	$page = $_GET['page'];
}

if (isset($_GET['rows'])) {
	$rowsperpage = $_GET['rows'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$rowsperpage = $_POST['rowperpage_id'];
}

$maxpages = ceil(($num_rows) / ($rowsperpage));

$startRow = (($page - 1) * $rowsperpage);

$rowsSQL = "SELECT * FROM tali_master_history ORDER BY id DESC LIMIT $startRow, $rowsperpage";
$rowsresult = mysqli_query($db_handle, $rowsSQL);
		
//JS to reload page when rowsperpage dropdown selection made
?>
<script>
	function change() {
		document.getElementById("rowperpage").submit();
	}
	</script>
<?php
		
echo "
				<table style=\"width:94%\" class=\"masterhistorytoptb\">
				<tr>
					<td>
";

if ($page > 1) {
	echo "
						<a href=\"masterhistory.php?page=".($page - 1)."&rows=".$rowsperpage."\">Previous</a>
	";
}
else
{
	echo "
						Previous
	";
}

$count = 0;
while ($count < $maxpages) {
	$count++;
	if ($page == $count) {
		echo "
						$count
		";
	}
	else
	{
		echo "
						<a href=\"masterhistory.php?page=".$count."&rows=".$rowsperpage."\">$count</a>
		";
	}
}

if (($page < $maxpages) AND ($maxpages > 1)) {
	echo "
						<a href=\"masterhistory.php?page=".($page + 1)."&rows=".$rowsperpage."\">Next</a>
	";
}
else
{
	echo "
						Next
	";
}

echo "
					</td>
					<td width=\"1px\" align=\"right\">
						<form action=\"masterhistory.php\" method=\"post\" id=\"rowperpage\" name=\"rowperpage\">					
							View: 
							<select class=\"tali_masterhistory_rowperpage_dropdown\" name=\"rowperpage_id\" form=\"rowperpage\" value=\"$rowperpage_id\" onchange=\"change()\">
";

$rowsperpage_array = ["100", "250", "500"];
forEach ($rowsperpage_array as $rowsperpagevalue) {
	if ($rowsperpage == $rowsperpagevalue) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
								<option value=".$rowsperpagevalue." ".$selected.">".$rowsperpagevalue."</option>
	";
}

echo "
							</select>
						</form>
					</td>
				</tr>
				</table>
"; 

echo "
				<table style=\"width:94%\" class=\"masterhistorytb\">
					<col width=\"5%\">
					<col width=\"10%\">
					<col width=\"5%\">
					<col width=\"10%\">
					<col width=\"5%\">
					<col width=\"65%\">
					<tr>
						<th>ID</th>
						<th>Time</th>
						<th>User ID</th>
						<th>Module</th>
						<th>Item ID</th>
						<th>Event</th>
					</tr>
";

while ($db_field = mysqli_fetch_assoc($rowsresult)) {
	$id=$db_field['id'];
	$time=$db_field['time'];
	$username_id=$db_field['username_id'];
	$module=$db_field['module'];
	$item_id=$db_field['item_id'];
	$event=$db_field['event'];
	echo "
					<tr>
						<td style=\"text-align:center;\">$id</td>
						<td style=\"text-align:center;\">$time</td>
						<td style=\"text-align:center;\">$username_id</td>
						<td style=\"text-align:center;\">$module</td>
						<td style=\"text-align:center;\">$item_id</td>
						<td>$event</td>
					</tr>
	";
}

echo "
				</table>
			</div>
		</div>
	</main>
";
?>
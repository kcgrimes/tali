<?php

//Manage view count/paging for history table
$rowperpage_id = "";
$countSQL = "SELECT id FROM inventory_master_history ORDER BY id DESC";
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

$rowsSQL = "SELECT * FROM inventory_master_history ORDER BY id DESC LIMIT $startRow, $rowsperpage";
$rowsresult = mysqli_query($db_handle, $rowsSQL);
		
//JS to reload page when rowsperpage dropdown selection made
?>
<script>
	function change() {
		document.getElementById("rowperpage").submit();
	}
	</script>
<?php

//bug - try to get this out of a table
echo "
			<div class=\"tali-page-frame\">
				<h1>Inventory History</h1>
				<table class=\"tali-master_history-top_table\">
				<tr>
					<td>
";

if ($page > 1) {
	echo "
						<a href=\"inventory.php?history=true&page=".($page - 1)."&rows=".$rowsperpage."\">Previous</a>
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
						<a href=\"inventory.php?history=true&page=".$count."&rows=".$rowsperpage."\">$count</a>
		";
	}
}

if (($page < $maxpages) AND ($maxpages > 1)) {
	echo "
						<a href=\"inventory.php?history=true&page=".($page + 1)."&rows=".$rowsperpage."\">Next</a>
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
					<td style=\"text-align:right\">
						<form action=\"inventory.php?history=true\" method=\"post\" id=\"rowperpage\" name=\"rowperpage\">					
							View: 
							<select class=\"tali-master_history-rows_per_page_dropdown\" name=\"rowperpage_id\" form=\"rowperpage\" value=\"$rowperpage_id\" onchange=\"change()\">
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

//Create history table
echo "		
				<table id=\"inventoryHistory\" class=\"inventoryBrowseTable\" style=\"font-size: 62.5%;\">
					<col width=\"5%\">
					<col width=\"12%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<col width=\"73%\">
					<tr>
						<th>ID</th>
						<th>Time</th>
						<th>User ID</th>
						<th>Item ID</th>
						<th>Event</th>
					</tr>
";

$SQL = "SELECT * FROM inventory_master_history ORDER BY id DESC";
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);

while ($db_field = mysqli_fetch_assoc($rowsresult)) {
	$id=$db_field['id'];
	$time=$db_field['time'];
	$username_id=$db_field['username_id'];
	$item_id=$db_field['item_id'];
	$event=$db_field['event'];
	
	//Treat item_id as item, package, or bundle id appropriately based on string
	if (strpos($event, "Item") !== false) {
		//Is item
		$treatId = "id";
	} 
	else if (strpos($event, "Package") !== false) {
		//Is package
		$treatId = "package";
	}
	else 
	{
		//Must be bundle
		$treatId = "specificLocation";
	}
	
	echo "
					<tr>
						<td style=\"text-align:center;\">$id</td>
						<td style=\"text-align:center;\">$time</td>
						<td style=\"text-align:center;\"><a href=\"inventory.php?userid=$username_id\">$username_id</a></td>
						<td style=\"text-align:center;\"><a href=\"inventory.php?$treatId=$item_id\">$item_id</a></td>
						<td style=\"text-align:left;\">$event</td>
					</tr>
	";
}
echo "
				</table>
			</div>
		</div>
	</main>
"; 
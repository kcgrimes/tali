<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
$SQL = "SELECT * FROM tali_personnel_awards_classes ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);

$arrayAwardsClasses = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$arrayAwardsClasses[] = ["awardclass_id" => $db_field["awardclass_id"], "name" => $db_field["name"]];
}

$SQL = "SELECT * FROM tali_personnel_awards ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);

$arrayAwards = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$arrayAwards[] = ["awardclass_id" => $db_field["awardclass_id"], "name" => $db_field["name"], "image" => $db_field["image"], "description" => $db_field["description"]];
}

echo "
	<div class=\"PageFrame\">
		<div class = \"PageFrameTitle\">
			<h1><strong>Awards</strong></h1>
		</div>
		<br/>
		<table class=\"tali_personnel_roster_front_links\">
			<col width=\"25%\">
			<col width=\"25%\">
			<col width=\"25%\">
			<col width=\"25%\">
			<tr>
				<th><a href=\"roster.php?action=active\">Active Members</a></th>
				<th><a href=\"roster.php?action=past\">Past Members</a></th>
				<th><a href=\"roster.php?action=awards\">Awards</a></th>
				<th><a href=\"roster.php?action=ranks\">Ranks</a></th>
			</tr>
		</table>
";

echo "
		<table class=\"tali_personnel_roster_front_designation\">
			<col width=\"20%\">
			<col width=\"30%\">
			<col width=\"50%\">
			<tr>
				<th>Image</th>
				<th>Award</th>
				<th>Description</th>
			</tr>
";
foreach ($arrayAwardsClasses as $awardClass) {
	$name = $awardClass['name'];
	echo "
			<tr>
				<th colspan=\"3\">$name</th>
			</tr>
	";
	foreach ($arrayAwards as $award) {
		if ($awardClass['awardclass_id'] == $award['awardclass_id']) {
			$image = $award['image'];
			$name = $award['name'];
			$description = $award['description'];
			echo "
			<tr>
				<td><img src=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/personnel/awards/$image\" alt=\"$name\"></img></td>
				<td>$name</td>
				<td>$description</td>
			</tr>
			";
		}
	}
}

echo "
		</table>
		<br/>
		<br/>
	</div>
";
?>
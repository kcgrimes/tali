<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
$SQL = "SELECT * FROM tali_personnel_ranks ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);

$arrayRanks = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$arrayRanks[] = ["name" => $db_field["name"], "abbreviation" => $db_field["abbreviation"], "image" => $db_field["image"]];
}

echo "
	<div class=\"PageFrame\">
		<div class = \"PageFrameTitle\">
			<h1><strong>Ranks</strong></h1>
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
			<col width=\"40%\">
			<col width=\"40%\">
			<col width=\"20%\">
			<tr>
				<th>Image</th>
				<th>Rank</th>
				<th>Abbreviation</th>
			</tr>
";
foreach ($arrayRanks as $rank) {
	$image = $rank['image'];
	$name = $rank['name'];
	$abbreviation = $rank['abbreviation'];
	echo "
			<tr>
				<td><img src=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/Personnel/Ranks/large/$image\" alt=\"$name\"></img></td>
				<td>$name</td>
				<td>$abbreviation</td>
			</tr>
	";
}

echo "
		</table>
		<br/>
		<br/>
	</div>
";
?>
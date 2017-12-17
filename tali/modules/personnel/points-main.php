<?php
//Stock variables
$module = "TALI_Personnel";
$displayMessage = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

if (isset($_POST['update_bu'])) {
	//Points updated
	$updateSQL = "SELECT personnel_id, points FROM tali_personnel_roster WHERE discharged=0";
	$updateresult = mysqli_query($db_handle, $updateSQL);
	
	$i = 0;
	while ($update_db_field = mysqli_fetch_assoc($updateresult)) {
		$personnel_id=$update_db_field['personnel_id'];
		$points=$update_db_field['points'];
		$new_points = $_POST["points_value_".$personnel_id.""];
		if ($points != $new_points) {
			$actionSQL = "UPDATE tali_personnel_roster SET points=$new_points WHERE personnel_id=$personnel_id";
			$actionresult = mysqli_query($db_handle, $actionSQL);
			$i = $i + 1;
		}
	}
	$engRecord = "records";
	if ($i == 1) {
		$engRecord = "record";
	}
	$engHas = "have";
	if ($i == 1) {
		$engHas = "has";
	}
	$displayMessage = "".$i." point ".$engRecord." ".$engHas." been updated!";
}

//Fresh page
echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Manage Points</h1>
				<p>This page allows for management of individual's points.</p>
";

if ($displayMessage != "") {
	echo "
				<p><font color=\"green\">$displayMessage</font></p>
	";
};
			
echo "
			</div>
";

echo "
			<div class=\"tali-page-frame\">
				<h1>Points</h1>
				
				<table class=\"tali_personnel_roster_table\">
					<col width=\"10%\">
					<col width=\"50%\">
					<col width=\"20%\">
					<col width=\"20%\">
					<tr>
						<th>Rank</th>
						<th>Name</th>
						<th>Points</th>
						<th>Out of</th>
					</tr>
";

$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
$result = mysqli_query($db_handle, $SQL);
	
while ($db_field = mysqli_fetch_assoc($result)) {
	$personnel_id=$db_field['personnel_id'];
	$rank_id=$db_field['rank_id'];
	$rankSQL = "SELECT abbreviation FROM tali_personnel_ranks WHERE rank_id=$rank_id";
	$rankresult = mysqli_query($db_handle, $rankSQL);
	$rank_db_field = mysqli_fetch_assoc($rankresult);
	$rank_abr=$rank_db_field['abbreviation'];
	$firstname=$db_field['firstname'];
	$lastname=$db_field['lastname'];
	$pointsSQL = "SELECT points FROM tali_personnel_roster WHERE personnel_id=$personnel_id";
	$pointsresult = mysqli_query($db_handle, $pointsSQL);
	$points_db_field = mysqli_fetch_assoc($pointsresult);
	$points=$points_db_field['points'];
	
	echo "
					<tr>
						<td style=\"text-align:center;\">$rank_abr</td>
						<td style=\"text-align:center;\">$firstname $lastname</td>
						<td style=\"text-align:center;\"><input type=\"text\" class=\"tali_personnel_points_update\" name=\"points_value_".$personnel_id."\" form=\"update_points\" maxlength=\"3\"value=\"$points\"></td>
						<td style=\"text-align:center;\">100</td>
					</tr>
	";
}

echo "
				</table>
			
				<form method=\"POST\" id=\"update_points\" action=\"personnel.php?sub=points\">
					<input type=\"submit\" name=\"update_bu\" class=\"tali-submit_button\" value=\"Update Points Record\"/>
				</form>
			</div>
		</div>
	</main>
";
?>
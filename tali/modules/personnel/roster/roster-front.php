<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
//Defaults to opening Active Member roster page
$action = "active";
if (isset($_GET['action'])) {
	$action = $_GET['action'];
}
	
switch ($action) {
	case "profile":
		require "".TALI_ABS_PATH."/modules/personnel/roster/roster-profile.php";
	break;
	case "awards":
		require "".TALI_ABS_PATH."/modules/personnel/roster/roster-awards.php";
	break;
	case "ranks":
		require "".TALI_ABS_PATH."/modules/personnel/roster/roster-ranks.php";
	break;
	case "active":
		echo "
			<div class=\"tali-personnel-roster-front-page-frame\">
				<div class = \"tali-personnel-roster-front-page-frame-title\">
					<h1>Active Duty Roster</h1>
				</div>
				<br/>
				<table class=\"tali-personnel-roster-front-links\">
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
					
		//Select all designations
		$SQL = "SELECT * FROM tali_personnel_designations WHERE inactive=0 ORDER BY weight DESC";
		$result = mysqli_query($db_handle, $SQL);
		
		$arrayDesignation = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$arrayDesignation[] = ["designation_id" => $db_field["designation_id"], "name" => $db_field["name"], "weight" => $db_field['weight'], "reportsto_designation_id" => $db_field['reportsto_designation_id']];
		}
		
		$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
		$result = mysqli_query($db_handle, $SQL);
		
		$arrayPersonnel = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$status_id=$db_field['status_id'];
			$statusSQL = "SELECT * FROM tali_personnel_statuses WHERE status_id=$status_id";
			$statusresult = mysqli_query($db_handle, $statusSQL);
			$status_db_field = mysqli_fetch_assoc($statusresult);
			$status=$status_db_field['name'];
			
			$role_id=$db_field['role_id'];
			$roleSQL = "SELECT * FROM tali_personnel_roles WHERE role_id=$role_id";
			$roleresult = mysqli_query($db_handle, $roleSQL);
			$role_db_field = mysqli_fetch_assoc($roleresult);
			$role=$role_db_field['name'];
			
			$arrayPersonnel[] = ["personnel_id" => $db_field["personnel_id"], "rank_abr" => $db_field["abbreviation"], "rank" => $db_field["name"], "lastname" => $db_field["lastname"], "firstname" => $db_field["firstname"], "designation_id" => $db_field["designation_id"], "role" => $role, "nickname" => $db_field["nickname"], "status" => $status, "image" => $db_field["image"], "discharged" => $db_field["discharged"]];
		}
		
		function personnelFill ($arrayPersonnel, $designation_id) {
			foreach ($arrayPersonnel as $personnel) {
				if (($personnel['designation_id'] == $designation_id) && ($personnel['discharged'] == 0)) {
					$image = $personnel['image'];
					echo "
					<tr>
						<td><img src=\"".TALI_DOMAIN_URL."".TALI_RANKS_IMAGES_DIRECTORY."$image\" alt=\"Rank\"></img></td>
						<td><a href=\"roster.php?action=profile&personnel_id=".$personnel['personnel_id']."\">" . $personnel['rank'] . " " . $personnel['firstname'] . " " . $personnel['lastname'] . "</a></td>
						<td>" . $personnel['role'] . "</td>
						<td>" . $personnel['nickname'] . "</td>
						<td>" . $personnel['status'] . "</td>
					</tr>
					";
				}
			}
		}
		
		function designationsFill ($arrayDesignation, $designation, $arrayPersonnel) {
			foreach ($arrayDesignation as $designation_reportto) {
				if ($designation['designation_id'] == $designation_reportto['reportsto_designation_id']) {
					echo "
					<tr>
						<th colspan=\"5\">" . $designation_reportto['name'] . "</th>
					</tr>
					";
					
					personnelFill ($arrayPersonnel, $designation_reportto['designation_id']);
					
					designationsFill ($arrayDesignation, $designation_reportto, $arrayPersonnel);
				}
			}
		}
		
		echo "
				<table class=\"tali-personnel-roster-front-designation\">
					<col width=\"10%\">
					<col width=\"45%\">
					<col width=\"15%\">
					<col width=\"15%\">
					<col width=\"15%\">
					<tr>
						<th>Rank</th>
						<th>Name</th>
						<th>Role</th>
						<th>Username</th>
						<th>Status</th>
					</tr>
		";
		
		//Show organization name (ie, "top" designation) first
		echo "
					<tr>
						<th colspan=\"5\">".TALI_ORGANIZATION_NAME."</th>
					</tr>
		";
		
		foreach ($arrayDesignation as $designation) {
			if ($designation['reportsto_designation_id'] == 0) {
				echo "
					<tr>
						<th colspan=\"5\">" . $designation['name'] . "</th>
					</tr>
				";
				
				personnelFill ($arrayPersonnel, $designation['designation_id']);
				
				designationsFill ($arrayDesignation, $designation, $arrayPersonnel);
			}
		}
		
		echo "
				</table>
				<br/>
				<br/>
			</div>
		";
	break;
	case "past":
		echo "
			<div class=\"tali-personnel-roster-front-page-frame\">
				<div class = \"tali-personnel-roster-front-page-frame-title\">
					<h1>Past Member Roster</h1>
				</div>
				<br/>
				<table class=\"tali-personnel-roster-front-links\">
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
		
		$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=1 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
		$result = mysqli_query($db_handle, $SQL);
		
		$arrayPersonnel = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$status_id=$db_field['status_id'];
			$statusSQL = "SELECT * FROM tali_personnel_statuses WHERE status_id=$status_id";
			$statusresult = mysqli_query($db_handle, $statusSQL);
			$status_db_field = mysqli_fetch_assoc($statusresult);
			$status=$status_db_field['name'];
			
			$role_id=$db_field['role_id'];
			$roleSQL = "SELECT * FROM tali_personnel_roles WHERE role_id=$role_id";
			$roleresult = mysqli_query($db_handle, $roleSQL);
			$role_db_field = mysqli_fetch_assoc($roleresult);
			$role=$role_db_field['name'];
			
			$arrayPersonnel[] = ["personnel_id" => $db_field["personnel_id"], "rank_abr" => $db_field["abbreviation"], "rank" => $db_field["name"], "lastname" => $db_field["lastname"], "firstname" => $db_field["firstname"], "role" => $role, "nickname" => $db_field["nickname"], "status" => $status, "image" => $db_field["image"], "discharged" => $db_field["discharged"]];
		}
		
		echo "
				<table class=\"tali-personnel-roster-front-designation\">
					<col width=\"10%\">
					<col width=\"45%\">
					<col width=\"15%\">
					<col width=\"15%\">
					<col width=\"15%\">
					<tr>
						<th>Rank</th>
						<th>Name</th>
						<th>Role</th>
						<th>Username</th>
						<th>Status</th>
					</tr>
		";
		
		foreach ($arrayPersonnel as $personnel) {
			if ($personnel['discharged'] == 1) {
				$image = $personnel['image'];
				echo "
					<tr>
						<td><img src=\"".TALI_RANKS_IMAGES_DIRECTORY."$image\" alt=\"Rank\"></img></td>
						<td><a href=\"roster.php?action=profile&personnel_id=".$personnel['personnel_id']."\">" . $personnel['rank'] . " " . $personnel['firstname'] . " " . $personnel['lastname'] . "</a></td>
						<td>" . $personnel['role'] . "</td>
						<td>" . $personnel['nickname'] . "</td>
						<td>" . $personnel['status'] . "</td>
					</tr>
				";
			}
		}
		
		echo "
				</table>
				<br/>
				<br/>
			</div>
		";
	break;
}
?>
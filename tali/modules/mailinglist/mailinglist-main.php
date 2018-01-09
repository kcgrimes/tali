<?php
//Stock variables
$module = "TALI_Mailing_List";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

if (isset($_GET['sub'])) {
	//Subpage selected
	switch ($_GET['sub']) {
		case "manage": 
			//Manage Custom Mailing List
			require 'mailinglist/mailinglist-manage.php';
			break;
	}
}
else
{
	//Fresh page
	
	$to = "";
	$subject = "";
	$body = "";
	$errorMessage = "";
	$successMessage = "";
	
	//Check if POST or not
	if (isset($_POST['btnSubmit'])) {
		//Attempting to send message
		//Retrieve variables from post
		$to = $_POST['to'];
		$subject = $_POST['subject'];
		$body = $_POST['body'];
		//Check if all fields were filled out
		if ((($to != "") || ((isset($_POST['mailinglistcheckbox'])) && ($_POST['mailinglistcheckbox'] != ""))) && ($subject != "") && ($body != "")) {
			//All variables were filled in
			//Start blank toArray
			$toArray = [];
			//Get addresses from checked mailing lists, if any
			if ((isset($_POST['mailinglistcheckbox'])) && ($_POST['mailinglistcheckbox'] != "")) {
				//A mailing list was checked
				$toListArray = $_POST['mailinglistcheckbox'];
				//Gather contacts for selected groups
				foreach ($toListArray as $toList) {
					//Turn list string into array
					$toList = explode(",", $toList);
					$basis = $toList[0];
					$entity = $toList[1];
					//Gather data depending on appropriate table
					echo "1";
					switch ($basis) {
						case "self":
							echo "2";
							//self - ["self","self"]
							$toArray[] = [TALI_SMTP_FROMADDRESS, TALI_SMTP_FROMNAME];
						break;
						case "custom":
							//custom - ["custom",id]
							//bug - in future, search various tables to try to get name to go with the email
							//Get list from database
							$SQL = "SELECT list FROM tali_mailing_list WHERE id=$entity";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$list = $db_field['list'];
							//Turn list string into array
							$list = explode(",", $list);
							//Put the list in the right format inside toArray
							////Put each email address in its own array with duplicate
							foreach ($list as $indiv) {
								$toArray[] = [$indiv, $indiv];
							}
						break;
						case "full":
							//full - ["full","active"/"past"]
							//Differ action depending on status
							//bug - maybe this series of code could be less duplicated between the below? or just a function
							switch ($entity) {
								case "active":
									//Non-discharged members
									//Obtain email and name data from roster and add it to toArray in correct format
									$SQL = "SELECT * FROM tali_personnel_roster WHERE discharged=0";
									$result = mysqli_query($db_handle, $SQL);	
									while ($db_field = mysqli_fetch_assoc($result)) {
										$rank_id=$db_field['rank_id'];
										$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
										$rankresult = mysqli_query($db_handle, $rankSQL);
										$rank_db_field = mysqli_fetch_assoc($rankresult);
										$rank_abr=$rank_db_field['abbreviation'];
										$firstname=$db_field['firstname'];
										$lastname=$db_field['lastname'];
										$email=$db_field['email'];
										
										$toArray[] = [$email, "$rank_abr $firstname $lastname"];
									}
								break;
								case "past":
									//Discharged members
									//Obtain email and name data from roster and add it to toArray in correct format
									$SQL = "SELECT * FROM tali_personnel_roster WHERE discharged=1";
									$result = mysqli_query($db_handle, $SQL);	
									while ($db_field = mysqli_fetch_assoc($result)) {
										$rank_id=$db_field['rank_id'];
										$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
										$rankresult = mysqli_query($db_handle, $rankSQL);
										$rank_db_field = mysqli_fetch_assoc($rankresult);
										$rank_abr=$rank_db_field['abbreviation'];
										$firstname=$db_field['firstname'];
										$lastname=$db_field['lastname'];
										$email=$db_field['email'];
										
										$toArray[] = [$email, "$rank_abr $firstname $lastname"];
									}
								break;
							}
						break;
						case "rank":
							//rank - ["rank",id]
							//Obtain email and name data from roster based on rank_id and add it to toArray in correct format
							$SQL = "SELECT * FROM tali_personnel_roster WHERE discharged=0 AND rank_id=$entity";
							$result = mysqli_query($db_handle, $SQL);	
							while ($db_field = mysqli_fetch_assoc($result)) {
								$rank_id=$db_field['rank_id'];
								$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
								$rankresult = mysqli_query($db_handle, $rankSQL);
								$rank_db_field = mysqli_fetch_assoc($rankresult);
								$rank_abr=$rank_db_field['abbreviation'];
								$firstname=$db_field['firstname'];
								$lastname=$db_field['lastname'];
								$email=$db_field['email'];
								
								$toArray[] = [$email, "$rank_abr $firstname $lastname"];
							}
						break;
						case "designation":
							//designation - ["designation",id]
							//Obtain email and name data from roster based on designation_id and add it to toArray in correct format
							$SQL = "SELECT * FROM tali_personnel_roster WHERE discharged=0 AND designation_id=$entity";
							$result = mysqli_query($db_handle, $SQL);	
							while ($db_field = mysqli_fetch_assoc($result)) {
								$rank_id=$db_field['rank_id'];
								$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
								$rankresult = mysqli_query($db_handle, $rankSQL);
								$rank_db_field = mysqli_fetch_assoc($rankresult);
								$rank_abr=$rank_db_field['abbreviation'];
								$firstname=$db_field['firstname'];
								$lastname=$db_field['lastname'];
								$email=$db_field['email'];
								
								$toArray[] = [$email, "$rank_abr $firstname $lastname"];
							}
						break;
					}
				}
			}
			//Delete duplicates
				//bug - is this necessary? Need to test PHPMail first. Below are 2 failed attempts
				//$toArray = array_unique($toArray, SORT_REGULAR);
				//$toArray = array_map("unserialize", array_unique(array_map("serialize", $toArray)));
			
			//If used, add manually entered addresses to the array
			if ($to != "") {
				//Convert string to array
				$toIndivArray = explode(",", $to);
				//Delete duplicates
					//bug - is this necessary? Need to test PHPMail first. Below is failed attempt
					//$toIndivArray = array_unique($toIndivArray);
				//bug - in future, search various tables to try to get name to go with the email
				//Put the list in the right format inside toArray
				//by putting each email address in its own array with duplicate
				foreach ($toIndivArray as $indiv) {
					$toArray[] = [$indiv, $indiv];
				}
			}
			
			//Check and make sure toArray contains valid email
			//If email is invalid, remove it and add it to a different array
			$invalidToArray = [];
			$key = 0;
			foreach ($toArray as $indiv) {
				//Check if email address is valid
				if (!filter_var($indiv[0], FILTER_VALIDATE_EMAIL)) {
					//Email address invalid
					//Add to invalid email array
					$invalidToArray[] = [$indiv[0],$indiv[1]];
					//Remove invalid email from To array
					unset($toArray[$key]);
				}
				$key++;
			}
			TALI_EMail ($toArray, $subject, BBCode2Html($body));
			
			//Prepare success message
			if (empty($invalidToArray)) {
				//No invalid emails, all sent
				$successMessage = "Message sent!";
			}
			else
			{
				//Invalid email was present
				$successMessage = "Message sent to all indicated e-mail addresses except for:";
				//List each invalid email
				foreach ($invalidToArray as $indiv) {
					$successMessage = $successMessage . "<br/>$indiv[0] as $indiv[1]";
				}
			}
			
			//Reload for fresh page
			//bug - can't reload, or else successmessage is lost...
			//header ("Location: mailinglist.php");
			//exit();
		}
		else
		{
			$errorMessage = "ERROR: To, Subject, and Body must be filled in!";
		}
	}
	
	//MarkitUp
	markItUp_editing ();
	
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Manage Mailing List</h1>
					<p>On this page you can manage the various mailing lists and send mail.</p>
					<a href=\"mailinglist.php?sub=manage\"><p>Manage Custom Mailing Lists</p></a>
				</div>
				
				<div class=\"tali-page-frame\">
					<h1>Send Mail</h1>
	";
	
	if ($errorMessage != "") {
	echo "
					<p><font color=\"red\">$errorMessage</font></p>
		";
	};

	if ($successMessage != "") {
		echo "
					<p><font color=\"green\">$successMessage</font></p>
		";
	};
	
	echo "
					<p>To Mailing List:</p>
	";
	//Display checkbox for self
	echo "
					<div style=\"display:inline-block\">
						<p><input type=\"checkbox\" name=\"mailinglistcheckbox[]\" form=\"form_mailinglist\" value=\"self,self\"/>
						".TALI_SMTP_FROMADDRESS."</p>
					</div>
					<br/>
	";
	//Display checkbox and name for each existing custom mailing list
	$SQL = "SELECT id,name FROM tali_mailing_list";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$id = $db_field['id'];	
		$name = $db_field['name'];
		echo "
					<div style=\"display:inline-block\">
						<p><input type=\"checkbox\" name=\"mailinglistcheckbox[]\" form=\"form_mailinglist\" value=\"custom,$id\"/>
						$name</p>
					</div>
		";
	}
	echo "			<br/>";
	//Display checkbox for current and past members
	echo "
					<div style=\"display:inline-block\">
						<p><input type=\"checkbox\" name=\"mailinglistcheckbox[]\" form=\"form_mailinglist\" value=\"full,active\"/>
						Current Members</p>
					</div>
					<div style=\"display:inline-block\">
						<p><input type=\"checkbox\" name=\"mailinglistcheckbox[]\" form=\"form_mailinglist\" value=\"full,past\"/>
						Discharged Members</p>
					</div>
	";
	echo "			<br/>";
	
	//Display checkbox and name for each rank ordered highest to lowest
	$SQL = "SELECT rank_id,abbreviation FROM tali_personnel_ranks ORDER BY weight DESC";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$id = $db_field['rank_id'];	
		$name = $db_field['abbreviation'];
		echo "
					<div style=\"display:inline-block\">
						<p><input type=\"checkbox\" name=\"mailinglistcheckbox[]\" form=\"form_mailinglist\" value=\"rank,$id\"/>
						$name</p>
					</div>
		";
	}
	echo "			<br/>";
	
	//Display checkbox and name for each designation ordered highest to lowest
	//Select all designations, including those that are inactive
	$SQL = "SELECT * FROM tali_personnel_designations ORDER BY weight DESC";
	$result = mysqli_query($db_handle, $SQL);
	
	//Store designation data collected from above query in array
	$arrayDesignation = [];
	while ($db_field = mysqli_fetch_assoc($result)) {
		$arrayDesignation[] = ["designation_id" => $db_field["designation_id"], "reportsto_designation_id" => $db_field['reportsto_designation_id']];
	}
	
	//Create function that will print each row in the table
	//$db_handle carried in due to needing to query inside function
	//First parameter is the designation (with array of data) to be printed
	function designationRow ($db_handle, $arrayDesignation_selected) {
		$chosen_id = $arrayDesignation_selected['designation_id'];
		//Obtain information about the designation from the database
		$SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$chosen_id";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$designation_id=$db_field['designation_id'];
		$reportsTo=$db_field['reportsto_designation_id'];
		
		//Prepare to create long, full designation name
		//Will make an array of the chain of command, invert it, and make it a string
		$full_desig_name_array = array();
		//Carry reportsTo number through coming while loop while maintaining original
		$reportsTo_cycle = $reportsTo;
		//Add selected designation's name to array first before adding the rest
		$full_desig_name_array[] = $db_field['name'];

		//If the selected designation reports to another designation, print their info, and continue
		//until the newly selected designation does not report to anyone (top of the chain)
		while ($reportsTo_cycle != 0) {
			$desName_SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$reportsTo_cycle";
			$desName_result = mysqli_query($db_handle, $desName_SQL);
			$desigName_db_field = mysqli_fetch_assoc($desName_result);
			//Add designation to array
			$full_desig_name_array[] = $desigName_db_field['name'];
			//Change reportsTo id for next cycle
			$reportsTo_cycle = $desigName_db_field['reportsto_designation_id'];
		}
		
		//Reverse the array and turn it into a string to finally define the full designation name
		$full_desig_name = implode(", ", array_reverse($full_desig_name_array));
		
		//List designations as options
		echo "
					<div style=\"display:inline-block\">
						<p><input type=\"checkbox\" name=\"mailinglistcheckbox[]\" form=\"form_mailinglist\" value=\"designation,$designation_id\"/>
						$full_desig_name</p>
					</div>
		";
	}
	
	//The below function and forEach are used together to correctly list designations
	//based on weight and chain of command, similar to how it's done on the front Roster
	function designationsFill ($db_handle, $arrayDesignation, $designation) {
		foreach ($arrayDesignation as $designation_reportto) {
			if ($designation['designation_id'] == $designation_reportto['reportsto_designation_id']) {
				designationRow ($db_handle, $designation_reportto);
									
				designationsFill ($db_handle, $arrayDesignation, $designation_reportto);
			}
		}
	}
	
	foreach ($arrayDesignation as $designation) {
		if ($designation['reportsto_designation_id'] == 0) {
			designationRow ($db_handle, $designation);
							
			designationsFill ($db_handle, $arrayDesignation, $designation);
		}
	}
		
	//End designation stuff
	
	echo "
					<p>To Individual Address (separated by comma \",\"):</p>
					<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"to\" form=\"form_mailinglist\" value=\"$to\">
					
					<p>Subject:</p>
					<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"subject\" form=\"form_mailinglist\" value=\"$subject\">
					
					<p>Body:</p>
					<p><textarea rows=\"6\" cols=\"75\" id=\"html\" name=\"body\" form=\"form_mailinglist\">$body</textarea></p>
					
					<form method=\"POST\" id=\"form_mailinglist\" action=\"mailinglist.php\">
						<p>
						<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\"/>
						</p>
					</form>
				</div>
			</div>
		</main>
	";
}
?>
<?php
//Stock variables
$module = "TALI_Personnel";

$weight = "";
$name = "";
$image = "";
$awardclass_id = "";
$description = "";
$awardfile_failed = FALSE;
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

if (isset($_GET['action'])) {
	if (isset($_GET['submit'])) {
		switch ($_GET['action']) {
			case "addclass":
				$name = $_POST['name'];
				$weight = $_POST['weight'];
				
				$name_sql = htmlspecialchars($name);
				$name_sql = TALI_quote_smart($name_sql, $db_handle);
				
				//Round weight value to prevent silliness aka decimals
				$weight = round($weight);
				if ($weight < 1) {
					$weight = 1;
				}
				
				$weight_sql = htmlspecialchars($weight);
				$weight_sql = TALI_quote_smart($weight_sql, $db_handle);
				
				$SQL = "INSERT INTO tali_personnel_awards_classes (name, weight) VALUES ($name_sql, $weight_sql)";						
				$result = mysqli_query($db_handle, $SQL);
				
				$SQL = "SELECT awardclass_id FROM tali_personnel_awards_classes ORDER BY awardclass_id DESC LIMIT 1";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$awardclass_id=$db_field['awardclass_id'];	
				
				//History Report
				TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_awards_classes', 'awardclass_id', $awardclass_id, 'Award Class ID#', 'name');
				
				header ("Location: personnel.php?sub=awards");
				exit();
			break;
			case "editclass":
				$name = $_POST['name'];
				$weight = $_POST['weight'];
				$awardclass_id = $_POST['awardclass_id'];
				
				$name_sql = htmlspecialchars($name);
				$name_sql = TALI_quote_smart($name_sql, $db_handle);
				
				//Round weight value to prevent silliness aka decimals
				$weight = round($weight);
				if ($weight < 1) {
					$weight = 1;
				}
				
				$weight_sql = htmlspecialchars($weight);
				$weight_sql = TALI_quote_smart($weight_sql, $db_handle);
				
				$SQL = "UPDATE tali_personnel_awards_classes SET name=$name_sql, weight=$weight_sql WHERE awardclass_id=$awardclass_id";						
				$result = mysqli_query($db_handle, $SQL);
				
				//History Report
				TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_awards_classes', 'awardclass_id', $awardclass_id, 'Award Class ID#', 'name');
				
				header ("Location: personnel.php?sub=awards");
				exit();
			break;
			case "addaward":
				if (isset ($_FILES['award_file']['tmp_name'])) {
					
					$source_file = $_FILES['award_file']['tmp_name'];
	
					//Check file to make sure it's what we want
					if ($source_file != "") {
						$type = "";
						list($width, $height, $type) = getimagesize($source_file);
						if (($type == "") OR (!(($type == 2) OR ($type == 3) OR ($type == 6)))) {
							$errorMessage = "</br>Cannot upload image - must be of .JPG, .PNG, or .BMP format.</br>";
							$awardfile_failed = TRUE;
						}
						
						//bug - add size restrictions?
					}
					else
					{
						$errorMessage = "</br>Cannot upload image - Image size too large or did not completely upload.</br>";
						$awardfile_failed = TRUE;
					}
					
					//Are we ready?
					if (!$awardfile_failed) {
						//Upload single file to FTP
						$ftpUpload_Return = TALI_FTP_Upload('award_file', TALI_AWARDS_IMAGES_DIRECTORY);
						$errorMessage = $ftpUpload_Return[1]; 
						$upload_success = $ftpUpload_Return[2];
						if ($upload_success) {
							$name = $_POST['name'];
							$awardclass_id = $_POST['awardclass_id'];
							$description = $_POST['description'];
							$weight = $_POST['weight'];
							
							$name_sql = htmlspecialchars($name);
							$name_sql = TALI_quote_smart($name_sql, $db_handle);
							
							$awardclass_id_sql = htmlspecialchars($awardclass_id);
							$awardclass_id_sql = TALI_quote_smart($awardclass_id_sql, $db_handle);
							
							$image_sql = htmlspecialchars($_FILES['award_file']['name']);
							$image_sql = TALI_quote_smart($image_sql, $db_handle);
							
							$description_sql = htmlspecialchars($description);
							$description_sql = TALI_quote_smart($description_sql, $db_handle);
							
							//Round weight value to prevent silliness aka decimals
							$weight = round($weight);
							if ($weight < 1) {
								$weight = 1;
							}
							
							$weight_sql = htmlspecialchars($weight);
							$weight_sql = TALI_quote_smart($weight_sql, $db_handle);
							
							$SQL = "INSERT INTO tali_personnel_awards (awardclass_id, name, image, description, weight) VALUES ($awardclass_id_sql, $name_sql, $image_sql, $description_sql, $weight_sql)";						
							$result = mysqli_query($db_handle, $SQL);
							
							$SQL = "SELECT award_id FROM tali_personnel_awards ORDER BY award_id DESC LIMIT 1";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$award_id=$db_field['award_id'];	
							
							//History Report
							TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_awards', 'award_id', $award_id, 'Award ID#', 'name');
							
							header ("Location: personnel.php?sub=awards");
							exit();
						}
					}
				}
			break;
			case "editaward":
				$name = $_POST['name'];
				$awardclass_id = $_POST['awardclass_id'];
				$description = $_POST['description'];
				$weight = $_POST['weight'];
				$award_id = $_POST['award_id'];
				
				$name_sql = htmlspecialchars($name);
				$name_sql = TALI_quote_smart($name_sql, $db_handle);
				
				$awardclass_id_sql = htmlspecialchars($awardclass_id);
				$awardclass_id_sql = TALI_quote_smart($awardclass_id_sql, $db_handle);
										
				$description_sql = htmlspecialchars($description);
				$description_sql = TALI_quote_smart($description_sql, $db_handle);
				
				//Round weight value to prevent silliness aka decimals
				$weight = round($weight);
				if ($weight < 1) {
					$weight = 1;
				}
				
				$weight_sql = htmlspecialchars($weight);
				$weight_sql = TALI_quote_smart($weight_sql, $db_handle);
				
				$SQL = "UPDATE tali_personnel_awards SET name=$name_sql, awardclass_id=$awardclass_id_sql, description=$description_sql, weight=$weight_sql WHERE award_id=$award_id";						
				$result = mysqli_query($db_handle, $SQL);	
				
				//History Report
				TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_awards', 'award_id', $award_id, 'Award ID#', 'name');
				
				header ("Location: personnel.php?sub=awards");
				exit();
			break;
		}
	}
	else
	{
		//Some action
		$action = $_GET['action'];
		
		if ($action == "editclass") {
			$action = "addclass";
		};
		
		if ($action == "editaward") {
			$action = "addaward";
		};
		
		switch ($action) {
			case "addclass":
				if (isset($_GET['id'])) {
					//Editing
					$awardclass_id = $_GET['id'];
					
					//bug - why is this echo'd so early?
					echo "
						<input type=\"hidden\" form=\"add_awardclass\" name=\"awardclass_id\" value=\"$awardclass_id\"/>
					";
						
					$SQL = "SELECT * FROM tali_personnel_awards_classes WHERE awardclass_id=$awardclass_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
				
					$name = $db_field['name'];
					$weight = $db_field['weight'];
				};
			
				echo "
					<main>
						<div class=\"tali-container\">
							<div class=\"tali-page-frame\">
								<h1>Add & Edit Award Class</h1>
								<p>This page allows you to manage specific award classes.</p>
							</div>
							
							<div class=\"tali-page-frame\">
								<h1>Add & Edit Award Class</h1>
								
								<p>Award Class Name:</p>
								<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"name\" form=\"add_awardclass\" value=\"$name\">
								
								<p>Award Class Weight:</p>
								<input type=\"integer\" class=\"tali-personnel-drillreports-textinput\" name=\"weight\" form=\"add_awardclass\" value=\"$weight\">
								
								<form method=\"POST\" id=\"add_awardclass\" action=\"personnel.php?sub=awards&action=" . $_GET['action'] . "&submit=true\">
									<p>
									<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\"/>
									</p>
								</form>
							</div>
						</div>
					</main>
				";
			break;
			case "addaward":
				if (isset($_GET['id'])) {
					//Editing
					$award_id = $_GET['id'];
					
					echo "
								<input type=\"hidden\" form=\"add_award\" name=\"award_id\" value=\"$award_id\"/>
					";
						
					$SQL = "SELECT * FROM tali_personnel_awards WHERE award_id=$award_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
				
					$name = $db_field['name'];
					$awardclass_id = $db_field['awardclass_id'];
					$image = $db_field['image'];
					$description = $db_field['description'];
					$weight = $db_field['weight'];
					$image_url = $image;
				};
			
				echo "
					<main>
						<div class=\"tali-container\">
							<div class=\"tali-page-frame\">
								<h1>Add & Edit Awards</h1>
								<p>This page allows you to manage specific awards.</p>
							</div>
							
							<div class=\"tali-page-frame\">
								<h1>Add & Edit Awards</h1>
								
								<p>Award Name:</p>
								<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"name\" form=\"add_award\" value=\"$name\">
								
								<p>Award Class:</p>
								
								<select class=\"tali-personnel-awards-addaward-dropdown\" name=\"awardclass_id\" form=\"add_award\" value=\"$awardclass_id\">
									<option value=\"\">Select an Award Class</option>
				";
				
				$SQL = "SELECT * FROM tali_personnel_awards_classes ORDER BY weight DESC";
				$result = mysqli_query($db_handle, $SQL);
				while ($db_field = mysqli_fetch_assoc($result)) {
					if ($awardclass_id == $db_field['awardclass_id']) {
						$selected = 'selected="selected"';
					}
					else
					{
						$selected = '';
					}
					echo "
									<option value=\"{$db_field['awardclass_id']}\"".$selected.">{$db_field['name']}</option>
					";
				}
				
				echo "
								</select>
				";
										
				echo "
						
								<p>Award Image:</p>
						
				";

				if (($_GET['action']) == "editaward") {
					echo "
								<p><img src=\"".TALI_DOMAIN_URL."".TALI_AWARDS_IMAGES_DIRECTORY."$image_url\" alt=\"$image\"></img></p>
								<p>$image</p>
					";
				}
				else
				{
					echo "
								<p><input type=\"file\" name=\"award_file\" id=\"award_file\" form=\"add_award\"/></p>
					";
				}
				
				echo "
														
								<p>Award Description:</p>
								<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"description\" form=\"add_award\" value=\"$description\">
								
								<p>Award Weight:</p>
								<p>
								<input type=\"integer\" class=\"tali-personnel-drillreports-textinput\" name=\"weight\" form=\"add_award\" value=\"$weight\">
								</p>
								<form method=\"POST\" enctype=\"multipart/form-data\" id=\"add_award\" action=\"personnel.php?sub=awards&action=" . $_GET['action'] . "&submit=true\">
									<p>
									<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\"/>
									</p>
								</form>
							</div>
						</div>
					</main>
				";
			break;
			case "deleteclass":
				$delid = $_GET['id'];
	
				//History Report
				TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_awards_classes', 'awardclass_id', $delid, 'Award Class ID#', 'name');
										
				$delSQL = "DELETE FROM tali_personnel_awards_classes WHERE awardclass_id = $delid";
				$delresult = mysqli_query($db_handle, $delSQL);
				
				header ("Location: personnel.php?sub=awards");
				exit();
			break;
			case "deleteaward":
				$delid = $_GET['id'];
				$getimageSQL = "SELECT image FROM tali_personnel_awards WHERE award_id=$delid";
				$getimageresult = mysqli_query($db_handle, $getimageSQL);
				$getimage_db_field = mysqli_fetch_assoc($getimageresult);
				$award_image=$getimage_db_field['image'];	
			
				$ftpDelete_Return = TALI_FTP_Delete($award_image, TALI_AWARDS_IMAGES_DIRECTORY);
				$errorMessage = $ftpDelete_Return[1]; 
				$delete_success = $ftpDelete_Return[2];
				if ($delete_success) {
					//History Report
					TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_awards', 'award_id', $delid, 'Award ID#', 'name');
											
					$delSQL = "DELETE FROM tali_personnel_awards WHERE award_id = $delid";
					$delresult = mysqli_query($db_handle, $delSQL);
					
					header ("Location: personnel.php?sub=awards");
					exit();
				}
			break;
		}
	}
}
else
{
	//Fresh page
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Manage Awards</h1>
					<p>This page allows you to manage awards and award classes.</p>
				</div>
	";
	
	echo "
				<div class=\"tali-page-frame\">
					<h1>Manage Awards</h1>
					<a href=\"personnel.php?sub=awards&action=addclass\"><p>Add Award Class</p></a>
					<a href=\"personnel.php?sub=awards&action=addaward\"><p>Add Award</p></a>
	";
	echo "
					<br/>
					<br/>
	";
	
	echo "						
					<table id=\"awardclassTable\" class=\"tali-personnel-table\">
						<col width=\"70%\">
						<col width=\"15%\">
						<col width=\"15%\">
						
						<tr>
							<th>Award Class</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
	";
	
	$SQL = "SELECT * FROM tali_personnel_awards_classes ORDER BY weight DESC";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$awardclass_id = $db_field['awardclass_id'];
		$name = $db_field['name'];
				
		echo "
						<tr>
							<td style=\"text-align:center;\">$name</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=awards&action=editclass&id=$awardclass_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=awards&action=deleteclass&id=$awardclass_id\" onclick=\"return confirm('Are you sure you want to delete this award class?');\">
									<img src=\"../images/icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
								</a>
							</td>
						</tr>
		";
	};
	
	echo "
					</table>
					<br/>
					<br/>
	";
	
	echo "						
					<table id=\"awardsTable\" class=\"tali-personnel-table\">
						<col width=\"19%\">
						<col width=\"19%\">
						<col width=\"19%\">
						<col width=\"19%\">
						<col width=\"4%\">
						<col width=\"5%\">
						<col width=\"5%\">
						
						<tr>
							<th>Award</th>
							<th>Image</th>
							<th>Class</th>
							<th>Description</th>
							<th>Weight</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
	";
	
	$SQL = "SELECT * FROM tali_personnel_awards ORDER BY awardclass_id ASC, weight DESC";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$award_id = $db_field['award_id'];
		$image=$db_field['image'];
		$awardclass_id = $db_field['awardclass_id'];
		$classSQL = "SELECT name FROM tali_personnel_awards_classes WHERE awardclass_id=$awardclass_id";
		$classresult = mysqli_query($db_handle, $classSQL);
		$classdb_field = mysqli_fetch_assoc($classresult);
		$awardclass_name = $classdb_field['name'];
		$name = $db_field['name'];
		$description = $db_field['description'];
		$weight = $db_field['weight'];
				
		echo "
						<tr>
							<td style=\"text-align:center;\">$name</td>
							<td style=\"text-align:center;\"><img src=\"".TALI_DOMAIN_URL."".TALI_AWARDS_IMAGES_DIRECTORY."$image\" alt=\"Award\"></img><br/>$image</td>
							<td style=\"text-align:center;\">$awardclass_name</td>
							<td style=\"text-align:center;\">$description</td>
							<td style=\"text-align:center;\">$weight</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=awards&action=editaward&id=$award_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=awards&action=deleteaward&id=$award_id\" onclick=\"return confirm('Are you sure you want to delete this award?');\">
									<img src=\"../images/icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
								</a>
							</td>
						</tr>
		";
	};
	
	echo "
					</table>
	";
		
	echo "
				</div>
			</div>
		</main>
	";
}
?>
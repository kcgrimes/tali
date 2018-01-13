<?php
//Stock variables
$module = "TALI_Personnel";
$errorMessage = "";
$successMessage = "";
$uniformfile_failed = false;
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
		
TALI_sessionCheck($module, $db_handle);

//Page is accessed from personnel file entry, so id always available
$personnel_id = $_GET['id'];

//Find the dir and url for display and modifiable uniform files
//These values are used in both HTML and POST, so do it first

//Obtain roster and rank data given personnel_id
$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
$result = mysqli_query($db_handle, $SQL);
$db_field = mysqli_fetch_assoc($result);

$rank_abbr = $db_field['abbreviation'];
$firstname = $db_field['firstname'];
$lastname = $db_field['lastname'];
$uniform_display_filename = $db_field['uniform'];
$uniform_modifiable_filename = $db_field['uniform_modifiable'];

//Find display uniform file
$uniform_display_return = TALI_personnelUniformFinder($uniform_display_filename, TALI_UNIFORMS_IMAGES_URI, TALI_PERSONNEL_UNIFORMS_DEFAULT_FILE);
$uniform_display_file_assoc = $uniform_display_return[0];
//dir used to search and navigate to display file
$uniform_display_dir = $uniform_display_return[1];
//url used to actually link to display file for display and download
$uniform_display_url = $uniform_display_return[2];

//Find modifiable uniform file
$uniform_modifiable_return = TALI_personnelUniformFinder($uniform_modifiable_filename, TALI_UNIFORMS_MODIFIABLE_IMAGES_URI, TALI_PERSONNEL_UNIFORMS_MODIFIABLE_DEFAULT_FILE);
$uniform_modifiable_file_assoc = $uniform_modifiable_return[0];
//dir used to search and navigate to modifiable file
$uniform_modifiable_dir = $uniform_modifiable_return[1];
//url used to actually link to modifiable file for download
$uniform_modifiable_url = $uniform_modifiable_return[2];

//Check if button was clicked/if page is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Submit button was clicked
	//Check if attempting to upload modifiable uniform file
	if (!empty($_FILES['uniform_modifiable_file']['tmp_name'])) {
		//Attempting to upload modifiable uniform file
		$source_file = $_FILES['uniform_modifiable_file']['tmp_name'];

		//Check file to make sure it's what we want
		if ($source_file != "") {
			//bug - ..so, any conditions or no?
			//Determine subject directory
			if ($uniform_modifiable_file_assoc) {
				//Obtain URI directory for previously uploaded file
				$uniform_modifiable_uri = substr($uniform_modifiable_url,strlen(TALI_DOMAIN_URL));
				$uniform_modifiable_uri = "/".substr($uniform_modifiable_uri, 0, - (strlen($uniform_modifiable_filename)));
			}
			else
			{
				//First time upload
				$uniform_modifiable_uri = "/".TALI_UNIFORMS_MODIFIABLE_IMAGES_URI;
			}
			//Upload single file to FTP
			$ftpUpload_Return = TALI_FTP_Upload('uniform_modifiable_file', $uniform_modifiable_uri);
			$errorMessage = $errorMessage."<br/>".$ftpUpload_Return[1]; 
			$upload_success = $ftpUpload_Return[2];
			if ($upload_success) {
				//Update association
				//Make filename safe
				$source_file_name_sql = htmlspecialchars($_FILES['uniform_modifiable_file']['name']);
				$source_file_name_sql = TALI_quote_smart($source_file_name_sql, $db_handle);
				
				$SQL = "UPDATE tali_personnel_roster SET uniform_modifiable=$source_file_name_sql WHERE personnel_id=$personnel_id";						
				$result = mysqli_query($db_handle, $SQL);
				
				//History Report
				TALI_Create_History_Report('updated', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $_GET['id'], 'Modifiable Uniform for Personnel ID#', 'lastname');
			}
			else
			{
				$errorMessage = $errorMessage."<br/>Cannot upload modifiable image - Image size too large or did not completely upload.";
				$uniformfile_failed = TRUE;
			}
		}
		else
		{
			$errorMessage = $errorMessage."<br/>Cannot upload modifiable image - Image size too large or did not completely upload.";
			$uniformfile_failed = TRUE;
		}
	}
	
	//Check if attempting to upload display uniform file
	if (!empty($_FILES['uniform_file']['tmp_name'])) {
		//Attempting to upload uniform file
		$source_file = $_FILES['uniform_file']['tmp_name'];

		//Check file to make sure it's what we want
		if ($source_file != "") {
			$type = "";
			list($width, $height, $type) = getimagesize($source_file);
			if (($type == "") OR (!(($type == 2) OR ($type == 3) OR ($type == 6)))) {
				$errorMessage = $errorMessage."<br/>Cannot upload image - must be of .JPG, .PNG, or .BMP format.";
				$uniformfile_failed = TRUE;
			}
			
			//bug - add size restrictions?
		}
		else
		{
			$errorMessage = $errorMessage."<br/>Cannot upload image - Image size too large or did not completely upload.";
			$uniformfile_failed = TRUE;
		}
		
		//Are we ready?
		if (!$uniformfile_failed) {
			//Determine subject directory
			if ($uniform_display_file_assoc) {
				//Obtain URI directory for previously uploaded file
				$uniform_display_uri = substr($uniform_display_url,strlen(TALI_DOMAIN_URL));
				$uniform_display_uri = "/".substr($uniform_display_uri, 0, - (strlen($uniform_display_filename)));
			}
			else
			{
				//First time upload
				$uniform_display_uri = "/".TALI_UNIFORMS_IMAGES_URI;
			}
			//Upload single file to FTP
			$ftpUpload_Return = TALI_FTP_Upload('uniform_file', $uniform_display_uri);
			$errorMessage = $errorMessage."<br/>".$ftpUpload_Return[1]; 
			$upload_success = $ftpUpload_Return[2];
			if ($upload_success) {
				//Update association
				//Make filename safe
				$source_file_name_sql = htmlspecialchars($_FILES['uniform_file']['name']);
				$source_file_name_sql = TALI_quote_smart($source_file_name_sql, $db_handle);
				
				$SQL = "UPDATE tali_personnel_roster SET uniform=$source_file_name_sql WHERE personnel_id=$personnel_id";						
				$result = mysqli_query($db_handle, $SQL);
				
				//History Report
				TALI_Create_History_Report('updated', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $_GET['id'], 'Uniform for Personnel ID#', 'lastname');
			}
		}
	}
}

//Fresh page
//bug - if coming from post, the image links in HTMLmay not be to the new file name.
	//to alleviate this, we just find the file again, but that is clunky...
//Obtain roster and rank data given personnel_id
$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
$result = mysqli_query($db_handle, $SQL);
$db_field = mysqli_fetch_assoc($result);

$rank_abbr = $db_field['abbreviation'];
$firstname = $db_field['firstname'];
$lastname = $db_field['lastname'];
$uniform_display_filename = $db_field['uniform'];
$uniform_modifiable_filename = $db_field['uniform_modifiable'];

//Find display uniform file
$uniform_display_return = TALI_personnelUniformFinder($uniform_display_filename, TALI_UNIFORMS_IMAGES_URI, TALI_PERSONNEL_UNIFORMS_DEFAULT_FILE);
$uniform_display_file_assoc = $uniform_display_return[0];
//dir used to search and navigate to display file
$uniform_display_dir = $uniform_display_return[1];
//url used to actually link to display file for display and download
$uniform_display_url = $uniform_display_return[2];

//Find modifiable uniform file
$uniform_modifiable_return = TALI_personnelUniformFinder($uniform_modifiable_filename, TALI_UNIFORMS_MODIFIABLE_IMAGES_URI, TALI_PERSONNEL_UNIFORMS_MODIFIABLE_DEFAULT_FILE);
$uniform_modifiable_file_assoc = $uniform_modifiable_return[0];
//dir used to search and navigate to modifiable file
$uniform_modifiable_dir = $uniform_modifiable_return[1];
//url used to actually link to modifiable file for download
$uniform_modifiable_url = $uniform_modifiable_return[2];

//End being clunky

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Manage Personnel Uniforms</h1>
				<p>This page allows you to manage uniforms for a specific individual.</p>
";
if ($errorMessage != "") {
	echo "
				<p><font color=\"red\">$errorMessage</font></p>
	";
}
if ($successMessage != "") {
	echo "
				<p><font color=\"green\">$successMessage</font></p>
	";
}
echo "
			</div>
";

echo "
			<div class=\"tali-page-frame\">
				<h1>Manage Personnel Uniform for ".$rank_abbr." ".$firstname." ".$lastname."</h1>
								
				<p><img src=\"$uniform_display_url\" class=\"tali-personnel-roster-front-profile-uniform\" alt=\"Uniform\"></p>
				
				<a href=\"$uniform_modifiable_url\" download><p>Download Modifiable Image File</p></a>
				
				<a href=\"$uniform_display_url\" download><p>Download Display Image File</p></a>

				<p>
				Upload Modifiable Image File:
				<br/>
				<input type=\"file\" name=\"uniform_modifiable_file\" id=\"uniform_modifiable_file\" form=\"add_uniform\"/>
				</p>
				
				<p>
				Upload Display Image File:
				<br/>
				<input type=\"file\" name=\"uniform_file\" id=\"uniform_file\" form=\"add_uniform\"/>
				</p>
				
				<form method=\"POST\" enctype=\"multipart/form-data\" id=\"add_uniform\" action=\"personnel.php?sub=roster_uniform&id=$personnel_id\">
					<p>
					<input type=\"submit\" name=\"btnSubmit\" value=\"Submit\"/>
					</p>
				</form>
			</div>
		</div>
	</main>
";
?>
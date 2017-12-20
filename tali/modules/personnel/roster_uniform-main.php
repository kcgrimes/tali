<?php
//Stock variables
$module = "TALI_Personnel";
$errorMessage = "";
$uniformfile_failed = FALSE;
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
		
TALI_sessionCheck($module, $db_handle);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!empty($_FILES['uniform_file_psd']['tmp_name'])) {
		$source_file = $_FILES['uniform_file_psd']['tmp_name'];

		//Check file to make sure it's what we want
		if ($source_file != "") {
			//bug - Make requirement for PSD!
		}
		else
		{
			$errorMessage = $errorMessage."<br/>Cannot upload PSD image - Image size too large or did not completely upload.";
			$uniformfile_failed = TRUE;
		}
		
		//bug - 3rd - make the Logistics_Storage part dynammic?
		//Are we ready?
		if (!$uniformfile_failed) {
			//Upload single file to FTP
			$ftpUpload_Return = TALI_FTP_Upload('uniform_file_psd', "/Logistics_Storage/Uniform PSDs/Active Uniform PSDs/");
			$errorMessage = $errorMessage."<br/>".$ftpUpload_Return[1].""; 
			$upload_success = $ftpUpload_Return[2];
			if ($upload_success) {
				//History Report
				TALI_Create_History_Report('updated', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $_GET['id'], 'Uniform PSD for Personnel ID#', 'lastname');
			}
		}
	}
	
	if (!empty($_FILES['uniform_file_png']['tmp_name'])) {
		$source_file = $_FILES['uniform_file_png']['tmp_name'];

		//Check file to make sure it's what we want
		if ($source_file != "") {
			$type = "";
			list($width, $height, $type) = getimagesize($source_file);
			if (($type == "") OR (!(($type == 2) OR ($type == 3) OR ($type == 6)))) {
				$errorMessage = $errorMessage."<br/>Cannot upload PNG image - must be of .JPG, .PNG, or .BMP format.";
				$uniformfile_failed = TRUE;
			}
			
			//bug - add size restrictions?
		}
		else
		{
			$errorMessage = $errorMessage."<br/>Cannot upload PNG image - Image size too large or did not completely upload.";
			$uniformfile_failed = TRUE;
		}
		
		//Are we ready?
		if (!$uniformfile_failed) {
			//Upload single file to FTP
			$ftpUpload_Return = TALI_FTP_Upload('uniform_file_png', "/".$_SESSION['TALISupplement_ROOT_URL']."/personnel/uniforms/active/");
			$errorMessage = $errorMessage."<br/>".$ftpUpload_Return[1].""; 
			$upload_success = $ftpUpload_Return[2];
			if ($upload_success) {
				//History Report
				TALI_Create_History_Report('updated', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $_GET['id'], 'Uniform PNG for Personnel ID#', 'lastname');
			}
		}
	}
}

//Fresh page
$personnel_id = $_GET['id'];

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
echo "
			</div>
";

$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
$result = mysqli_query($db_handle, $SQL);
$db_field = mysqli_fetch_assoc($result);

$firstname = $db_field['firstname'];
$lastname = $db_field['lastname'];

$uniform_dir = "".$_SESSION['TALISupplement_ROOT_DIR']."/personnel/uniforms/active/".$firstname[0]."".$lastname.".png";
$uniformExists = 0;

if (!file_exists($uniform_dir)) {
	//bug - 3rd - this requires manual edit, tried glob
	$arrayUniDirs = ["1_DoD-CoD", "2_CoD2", "3_CoD2-CoD4", "4_CoD4-BF3", "5_PS2"];
	$arrayUniDirs = array_reverse($arrayUniDirs);
	foreach ($arrayUniDirs as $path) {
		$uniform_dir = "".$_SESSION['TALISupplement_ROOT_DIR']."/personnel/uniforms/past/".$path."/".$firstname[0]."".$lastname.".png";
		if (file_exists($uniform_dir)) {
			$uniformExists = 1;
			$uniform_dir = "".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/personnel/uniforms/past/".$path."/".$firstname[0]."".$lastname.".png";
			break;
		}
	}
	if ($uniformExists == 0) {
		$uniform_dir = "".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/personnel/uniforms/notfound.png";
	}
}
else
{
	$uniform_dir = "".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/personnel/uniforms/active/".$firstname[0]."".$lastname.".png";
}

//bug - 3rd - make the Logistics_Storage part dynammic?
echo "
			<div class=\"tali-page-frame\">
				<h1>Manage Personnel Uniform for ".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."</h1>
								
				<p><img src=\"$uniform_dir\" class=\"tali-personnel-roster-front-profile-uniform\" alt=\"Uniform\"></p>
				
				<a download=\"".$firstname[0]."".$lastname.".psd\" href=\"".$_SESSION['TALI_Domain_URL']."/Logistics_Storage/Uniform PSDs/Active Uniform PSDs/".$firstname[0]."".$lastname.".psd\"><p>Download .PSD</p></a>
				
				<a download=\"".$firstname[0]."".$lastname.".png\" href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALISupplement_ROOT_URL']."/personnel/uniforms/active/".$firstname[0]."".$lastname.".png\"><p>Download .PNG</p></a>

				<p>
				Upload .PSD:
				<br/>
				<input type=\"file\" name=\"uniform_file_psd\" id=\"uniform_file_psd\" form=\"add_uniform\"/>
				</p>
				
				<p>
				Upload .PNG:
				<br/>
				<input type=\"file\" name=\"uniform_file_png\" id=\"uniform_file_png\" form=\"add_uniform\"/>
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
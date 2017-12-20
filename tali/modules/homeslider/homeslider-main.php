<?php
//Stock variables
$displayUploadMessage = "";
$displayDeleteMessage = "";
$failed = FALSE;
$module = "TALI_Home_Slider";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Upload
	if (isset ($_FILES['file']['tmp_name'])) {
		$source_file = $_FILES['file']['tmp_name'];
		
		//Check file to make sure it's what we want
		if ($source_file != "") {
			$type = "";
			list($width, $height, $type) = getimagesize($source_file);
			if (($type == "") OR (!(($type == 2) OR ($type == 3) OR ($type == 6)))) {
				$displayUploadMessage = "</br>Cannot upload image - must be of .JPG, .PNG, or .BMP format.</br>";
				$failed = TRUE;
			}
			
			/*
			//bug - Mandates 4:3 - is this necessary? Zoom to adjust on front end?
			if ((!$failed) && (($width / $height) != (4 / 3))) {
				$displayUploadMessage = "</br>Cannot upload image - must be 4:3 aspect ratio.</br>";
				$failed = TRUE;
			}*/
		}
		else
		{
			$displayUploadMessage = "</br>Cannot upload image - Image size too large or did not completely upload.</br>";
			$failed = TRUE;
		}
		
		//Are we ready?
		if (!$failed) {
			//Upload single file to FTP
			$ftpUpload_Return = TALI_FTP_Upload('file', $_SESSION['TALI_HomeSlider_Images_Directory']);
			$displayUploadMessage = $ftpUpload_Return[1]; 
			$upload_success = $ftpUpload_Return[2];
			if ($upload_success) {
				//Gather variables for database entry insertion
				$destination_file_name_sql = htmlspecialchars($_FILES['file']['name']);
				$destination_file_name_sql = TALI_quote_smart($destination_file_name_sql, $db_handle);
				
				$text = $_POST['text'];
				$text_sql = htmlspecialchars($text);
				$text_sql = TALI_quote_smart($text_sql, $db_handle);
				
				$weight = $_POST['weight'];
				$weight_sql = htmlspecialchars($weight);
				$weight_sql = TALI_quote_smart($weight_sql, $db_handle);
			
				$SQL = "INSERT INTO tali_homeslider (image, text, weight) VALUES ($destination_file_name_sql, $text_sql, $weight_sql)";
				$result = mysqli_query($db_handle, $SQL);
			
				$SQL = "SELECT image_id FROM tali_homeslider ORDER BY image_id DESC limit 1";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$image_id = $db_field['image_id'];
				
				//History Report
				TALI_Create_History_Report('uploaded', $module, $db_handle, 'tali_homeslider', 'image_id', $image_id, 'Image ID#', 'image');
			}
		}
	}
	//Delete
	if (isset($_POST['tali_homeslider_delete_file'])) {
		//Delete single file from FTP
		$ftpDelete_Return = TALI_FTP_Delete($_POST['selectimage'], $_SESSION['TALI_HomeSlider_Images_Directory']);
		$displayDeleteMessage = $ftpDelete_Return[1]; 
		$delete_success = $ftpDelete_Return[2];

		if ($delete_success) {
			//Gather variables for database entry deletion
			$source_file_sql = htmlspecialchars($_POST['selectimage']);
			$source_file_sql = TALI_quote_smart($source_file_sql, $db_handle);
		
			$SQL = "SELECT image_id FROM tali_homeslider WHERE image=$source_file_sql";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$image_id = $db_field['image_id'];
			
			//History Report
			TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_homeslider', 'image_id', $image_id, 'Image ID#', 'image');
			
			//Delete DB entry
			$delSQL = "DELETE FROM tali_homeslider WHERE image=$source_file_sql";
			$delresult = mysqli_query($db_handle, $delSQL);
		}
	}
}

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Manage Home Slider</h1>
				<p>On this page you can manage the images that appear in the image carrousel on the home page.</p>
			</div>
	
";

echo "
			<div class=\"tali-page-frame\">
				<h1>Available Images</h1>
				<p>Listed below are all images uploaded to display with this module.</p>
				<table class=\"tali-homeslider-table\">
					<col width=\"25%\">
					<col width=\"25%\">
					<col width=\"25%\">
					<col width=\"25%\">
					
					<tr>
						<th>Image</th>
						<th>File Name</th>
						<th>Slider Text</th>
						<th>Weight</th>
					</tr>
";

$SQL = "SELECT * FROM tali_homeslider ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);

//bug - this directory is relative
while ($db_field = mysqli_fetch_assoc($result)) {
	$imagename = $db_field['image'];
	$text = $db_field['text'];
	$weight = $db_field['weight'];
	echo "
					<tr>
						<td><img src=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_HomeSlider_Images_Directory']."$imagename\" style=\"width:60%;height:auto;\" alt=\"$imagename\" name=\"$imagename\"/></td>
						<td>$imagename</td>
						<td>$text</td>
						<td>$weight</td>
					</tr>
	";
}
	
echo "
				</table>
			</div>
";

echo "
			<div class=\"tali-page-frame\">
				<h1>Upload Images</h1>
				<form action=\"homeslider.php\" method=\"post\" enctype=\"multipart/form-data\">
					<p>
					<input type=\"file\" name=\"file\" id=\"file\"/>
					</p>
					<p>
					<font color=\"red\">$displayUploadMessage</font>
					</p>
					<p>
					Slider Text:
					<input type=\"text\" name=\"text\" value=\"\">
					</p>
					<p>
					Weight:
					<input type=\"integer\" class=\"tali-homeslider-input-weight\" name=\"weight\" value=\"\">
					</p>
					<p>
					<input type=\"submit\" name=\"submit\" value=\"Upload Image\"/>
					</p>
				</form>
			</div>
			
			<div class=\"tali-page-frame\">
				<h1>Delete Images</h1>
				<p>Select an image from the dropdown and click Delete Image to remove the image from the website.</p>
				<form method=\"POST\" id=\"tali_homeslider_delete_form\" action=\"homeslider.php\">
					<p>
					<select name=\"selectimage\">
						<option value=\"empty\">Select an Image</option>
";
	
$SQL = "SELECT image FROM tali_homeslider ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);

while ($db_field = mysqli_fetch_assoc($result)) {
	$image = $db_field['image'];
	echo "
						<option value=\"$image\">$image</option>
	";
}
				
echo "
					</select>
					</p>
					<p>
					<font color=\"red\">$displayDeleteMessage</font>
					</p>
					<p>
					<input type=\"Submit\" name=\"tali_homeslider_delete_file\" value=\"Delete Image\">
					</p>
				</form>
			</div>
		</div>
	</main>
";
?>
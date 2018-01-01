<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
// bug - make more dynamic
echo "
	<div class = \"tali-homeslider-front\">
		<div class = \"tali-homeslider-front_img\">
"; 

$SQL = "SELECT * FROM tali_homeslider ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);

$arrayHomeSlider = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$arrayHomeSlider[] = [$db_field['image'], $db_field['text']];
}
		
$firstImage = "".TALI_DOMAIN_URL."".TALI_TALISUPPLEMENT_URI."/homeslider/".$arrayHomeSlider[0][0]."";
$secondImage = "".TALI_DOMAIN_URL."".TALI_TALISUPPLEMENT_URI."/homeslider/".$arrayHomeSlider[1][0]."";
$thirdImage = "".TALI_DOMAIN_URL."".TALI_TALISUPPLEMENT_URI."/homeslider/".$arrayHomeSlider[2][0]."";
$firstText = $arrayHomeSlider[0][1];
$secondText = $arrayHomeSlider[1][1];
$thirdText = $arrayHomeSlider[2][1];

echo "
			<a href=\"http://www.3rd-infantry-division.org/forums/index.php?board=13.0\">
				<img id = \"tali_homeslider_img_id\" src=\"$firstImage\" alt=\"$firstText $secondText $thirdText\">
			</a>
		</div>
		<div class = \"tali-homeslider-front_hoverbox_1\" id = \"tali_homeslider_hoverbox_1_id\">
			<p>$firstText</p>
		</div>
		<div class = \"tali-homeslider-front_hoverbox_2\" id = \"tali_homeslider_hoverbox_2_id\">
			<p>$secondText</p>
		</div>
		<div class = \"tali-homeslider-front_hoverbox_3\" id = \"tali_homeslider_hoverbox_3_id\">
			<p>$thirdText</p>
		</div>
	</div>
";

$firstImage_json = json_encode($firstImage);
$secondImage_json = json_encode($secondImage);
$thirdImage_json = json_encode($thirdImage);
?>
<script>
	$(document).ready(function(){
		var firstImage = <?php echo $firstImage_json; ?>;
		var secondImage = <?php echo $secondImage_json; ?>;
		var thirdImage = <?php echo $thirdImage_json; ?>;
		$("#tali_homeslider_hoverbox_1_id").mouseover(function(){
			document.getElementById("tali_homeslider_img_id").src="" + firstImage + "";
		});
		$("#tali_homeslider_hoverbox_2_id").mouseover(function(){
			document.getElementById("tali_homeslider_img_id").src="" + secondImage + "";
		});
		$("#tali_homeslider_hoverbox_3_id").mouseover(function(){
			document.getElementById("tali_homeslider_img_id").src="" + thirdImage + "";
		});
	});
</script>
<?php	
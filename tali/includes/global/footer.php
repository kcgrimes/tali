<?php
$curYear = date("Y");
echo "
	<footer>
		<div class=\"tali-container\">
			| <a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/termsconditions.php\" style=\"color: #000000\">Terms and Conditions</a> | <a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/privacy.php\" style=\"color: #000000\">Privacy Statement</a> |<br>
			Copyright © 2014-$curYear Travis County Search &amp; Rescue. All Rights Reserved.
		</div>
	</footer>
";
?>

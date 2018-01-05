<?php
$curYear = date("Y");
echo "
	<footer>
		<div class=\"tali-container\">
			| <a href=\"".TALI_DOMAIN_URL."".TALI_URI."/termsconditions.php\" style=\"color: #000000\">Terms and Conditions</a> | <a href=\"".TALI_DOMAIN_URL."".TALI_URI."/privacy.php\" style=\"color: #000000\">Privacy Statement</a> |<br>
			Copyright © 2014-$curYear Kent \"KC\" Grimes. All Rights Reserved.
		</div>
	</footer>
";
?>

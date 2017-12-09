<?php
	$text = "";
	if (isset($_SESSION['username'])) {
		$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
		
		$text = 
		"
		<div class=\"container\">
			<table class=\"header_text_table\">
				<col width=\"50%\">
				<col width=\"50%\">
				<tr>
					<td style=\"text-align:left;\"><a href=\"javascript:history.back()\">Go Back</a></td>
					<td style=\"text-align:right;\">Logged in as ".$_SESSION['username']." (<a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/account.php\">Account</a>) (<a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/login.php\">Log Out</a>)</td>
				</tr>
			</table>
		</div>
		";
	}
	
	echo '
		<div class="header">
			<a href="'.$_SESSION["TALI_Domain_URL"].''.$_SESSION["TALI_ROOT_URL"].'/index.php">
				<img src="'.$_SESSION["TALI_Domain_URL"].''.$_SESSION["TALI_ROOT_URL"].'/images/display/TALIBanner.png" alt="TALI Banner" name="Team Administration/Logistics Interface" style="display:block;max-width:100%;max-height:100px;margin-left:auto;margin-right:auto;"/>
			</a>
	';
	if ($text != "") {
		echo "
			$text
		";
	};
	echo '
		</div>
	';
?>
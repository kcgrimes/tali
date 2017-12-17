<?php
echo "
	<header>
		<div class=\"tali-container\">
			<a href=\"".$_SESSION["TALI_Domain_URL"]."".$_SESSION["TALI_ROOT_URL"]."/index.php\">
				<img class=\"tali-header-banner_img\" src=\"".$_SESSION["TALI_Domain_URL"]."".$_SESSION["TALI_ROOT_URL"]."/images/display/TALIBanner.png\" alt=\"TALI Banner\" name=\"Team Administration/Logistics Interface\"/>
			</a>
";

//If logged in, display some more information
if (isset($_SESSION['username'])) {
	echo "
			<table class=\"tali-header-text_table\">
				<col width=\"50%\">
				<col width=\"50%\">
				<tr>
					<td style=\"text-align:left;\"><a href=\"javascript:history.back()\">Go Back</a></td>
					<td style=\"text-align:right;\">Logged in as ".$_SESSION['username']." (<a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/account.php\">Account</a>) (<a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/login.php\">Log Out</a>)</td>
				</tr>
			</table>
	";
}

echo "
		</div>
	</header>
";
?>
<?php
echo "
	<header>
		<div class=\"tali-container\">
			<a href=\"".$_SESSION["TALI_Domain_URL"]."".$_SESSION["TALI_ROOT_URL"]."/index.php\">
				<img class=\"tali-header-banner_img\" src=\"".$_SESSION["TALI_Domain_URL"]."".$_SESSION["TALI_ROOT_URL"]."/images/TALIBanner.png\" alt=\"TALI Banner\" name=\"Team Administration/Logistics Interface\"/>
			</a>
";

//If logged in, display some more information
if (isset($_SESSION['username'])) {
	echo "
			<div>
				<p class=\"tali-header-goback\"><a href=\"javascript:history.back()\">Go Back</a></p>
				<p class=\"tali-header-account\">Logged in as ".$_SESSION['username']." (<a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/account.php\">Account</a>) (<a href=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_ROOT_URL']."/login.php\">Log Out</a>)</p>
			</div>
	";
}

echo "
		</div>
	</header>
";
?>
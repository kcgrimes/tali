<?php
echo "
	<header>
		<div class=\"tali-container\">
			<a href=\"".TALI_DOMAIN_URL."".TALI_URI."/index.php\">
				<img class=\"tali-header-banner_img\" src=\"".TALI_DOMAIN_URL."".TALI_URI."/images/TALIBanner.png\" alt=\"TALI Banner\" name=\"Team Administration/Logistics Interface\"/>
			</a>
";

//If logged in, display some more information
if (isset($_SESSION['TALI_Username'])) {
	echo "
			<div>
				<p class=\"tali-header-goback\"><a href=\"javascript:history.back()\">Go Back</a></p>
				<p class=\"tali-header-account\">Logged in as ".$_SESSION['TALI_Username']." (<a href=\"".TALI_DOMAIN_URL."".TALI_URI."/account.php\">Account</a>) (<a href=\"".TALI_DOMAIN_URL."".TALI_URI."/login.php\">Log Out</a>)</p>
			</div>
	";
}

echo "
		</div>
	</header>
";
?>
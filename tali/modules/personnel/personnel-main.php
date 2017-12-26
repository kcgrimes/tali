<?php
//Stock variables
$module = "TALI_Personnel";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);
		
//personnel.php
if (!isset($_GET['sub'])) {
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Manage Personnel</h1>
					<p>On this page you can manage personnel through TALI with a variety of submodules.</p>
				</div>
				
				<div class=\"tali-page-frame\">
					<h1>Personnel Modules</h1>
					<div class=\"tali-responsive-row\">
						<a href=\"personnel.php?sub=roster\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/Index-Personnel.png\" alt=\"Roster Icon\" name=\"Roster Icon\">
							<p>Roster</p>
						</a>
						<a href=\"personnel.php?sub=awards\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/Personnel-Awards.png\" alt=\"Awads Icon\" name=\"Awards Icon\">
							<p>Awards</p>
						</a>
						<a href=\"personnel.php?sub=drillreports\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/Personnel-DrillReports.png\" alt=\"Drill Reports Icon\" name=\"Drill Reports Icon\">
							<p>Drill Reports</p>
						</a>
						<a href=\"personnel.php?sub=metrics\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/Personnel-Metrics.png\" alt=\"Metrics Icon\" name=\"Metrics Icon\">
							<p>Metrics</p>
						</a>
";
					/* bug - Commented out, not needed, wanted hidden
						<a href=\"personnel.php?sub=points\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/fillerpic.png\" alt=\"Filler Icon\" name=\"Module Filler Icon\">
							<p>Points</p>
						</a>
					*/
					/* bug - Commented out, not done
						<a href=\"personnel.php?sub=competition\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/fillerpic.png\" alt=\"Filler Icon\" name=\"Module Filler Icon\">
							<p>Competition</p>
						</a>
					*/ 
echo "
						<a href=\"personnel.php?sub=configuration\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/Personnel-Configuration.png\" alt=\"Configuration Icon\" name=\"Configuration Icon\">
							<p>Configuration</p>
						</a>
					</div>
				</div>
			</div>
		</main>
	";
}
else
{
	switch ($_GET['sub']) {
		case "roster": 
			require "personnel/roster-main.php";
			break;
		case "roster_awards":
			require "personnel/roster_awards-main.php";
			break;
		case "roster_servicerecord":
			require "personnel/roster_servicerecord-main.php";
			break;
		case "roster_uniform":
			require "personnel/roster_uniform-main.php";
			break;
		case "awards":
			require "personnel/awards-main.php";
			break;
		case "drillreports":
			require "personnel/drillreports-main.php";
			break;
		case "metrics":
			require "personnel/metrics-main.php";
			break; 
		case "points":
			require "personnel/points-main.php";
			break; 
		case "competition":
			require "personnel/competition-main.php";
		break;
		case "configuration":
			require "personnel/configuration-main.php";
		break;
	}
}
?>
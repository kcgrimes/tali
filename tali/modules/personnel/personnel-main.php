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
		<div class=\"content PageFrame\">
			<h1><strong>Manage Personnel</strong></h1>
			<p>On this page you can manage personnel through TALI with a variety of submodules.</p>
		</div>
		
		<div class=\"content PageFrame\">
			<h1><strong>Personnel Modules</strong></h1>
			<div class=\"row\">
				<div class=\"col\">
					<a href=\"personnel.php?sub=roster\" class=\"thumbnail\">
						<img src=\"../images/display/icons/Index-Personnel.png\" alt=\"Roster Icon\" name=\"Roster Icon\">
						<p>Roster</p>
					</a>
				</div>
				<div class=\"col\">
					<a href=\"personnel.php?sub=awards\" class=\"thumbnail\">
						<img src=\"../images/display/icons/Personnel-Awards.png\" alt=\"Awads Icon\" name=\"Awards Icon\">
						<p>Awards</p>
					</a>
				</div>
				<div class=\"col\">
					<a href=\"personnel.php?sub=drillreports\" class=\"thumbnail\">
						<img src=\"../images/display/icons/Personnel-DrillReports.png\" alt=\"Drill Reports Icon\" name=\"Drill Reports Icon\">
						<p>Drill Reports</p>
					</a>
				</div>
				<div class=\"col\">
					<a href=\"personnel.php?sub=metrics\" class=\"thumbnail\">
						<img src=\"../images/display/icons/Personnel-Metrics.png\" alt=\"Metrics Icon\" name=\"Metrics Icon\">
						<p>Metrics</p>
					</a>
				</div>
	";
				/* bug - Commented out, not needed, wanted hidden
				<div class=\"col\">
					<a href=\"personnel.php?sub=points\" class=\"thumbnail\">
						<img src=\"../images/display/icons/fillerpic.png\" alt=\"Filler Icon\" name=\"Module Filler Icon\">
						<p>Points</p>
					</a>
				</div>
				*/
				/* bug - Commented out, not done
				<div class=\"col\">
					<a href=\"personnel.php?sub=competition\" class=\"thumbnail\">
						<img src=\"../images/display/icons/fillerpic.png\" alt=\"Filler Icon\" name=\"Module Filler Icon\">
						<p>Competition</p>
					</a>
				</div>
				*/ 
	echo "
				<div class=\"col\">
					<a href=\"personnel.php?sub=configuration\" class=\"thumbnail\">
						<img src=\"../images/display/icons/Personnel-Configuration.png\" alt=\"Configuration Icon\" name=\"Configuration Icon\">
						<p>Configuration</p>
					</a>
				</div>
			</div>
		</div>
	";
}
else
{
	switch ($_GET['sub']) {
		case "roster": 
			require 'personnel/roster-main.php';
			break;
		case "roster_awards":
			require 'personnel/roster_awards-main.php';
			break;
		case "roster_servicerecord":
			require 'personnel/roster_servicerecord-main.php';
			break;
		case "roster_uniform":
			require 'personnel/roster_uniform-main.php';
			break;
		case "awards":
			require 'personnel/awards-main.php';
			break;
		case "drillreports":
			require 'personnel/drillreports-main.php';
			break;
		case "metrics":
			require 'personnel/metrics-main.php';
			break; 
		case "points":
			require 'personnel/points-main.php';
			break; 
		case "competition":
			require 'personnel/competition-main.php';
		break;
		case "configuration":
			require 'personnel/configuration-main.php';
		break;
	}
}
?>
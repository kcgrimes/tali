<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
if (!isset($_GET["archive"])) {
	echo "
		<div class=\"content PageFrame\">
			<div class = \"PageFrameTitle\">
				<h1><strong>Views News Archive</strong></h1>
			</div>
			<br/>
			<br/>
			<p>To view a list containing all of our news entries, check out the <a href=\"news.php?archive=true\"><strong>News Archive</strong></a>.</p>
		</div>
	";
	//Normal view
	$SQL = "SELECT * FROM tali_news ORDER BY time DESC";
	$result = mysqli_query($db_handle, $SQL);
	$num_rows = mysqli_num_rows($result);
	$cnt=0;
	while (($db_field = mysqli_fetch_assoc($result)) && ($cnt<5)) {
		$id=$db_field['id'];
		$time=$db_field['time'];
		$author=$db_field['author'];
		$title=$db_field['title'];
		$body=$db_field['body'];
		$body=htmlspecialchars_decode($body);
		$body=BBCode2Html($body);
		$time=strtotime($time);
		$time=date('l\, F jS\, Y \a\t H:i', $time);
		echo "
		<div class=\"frontnewsentry PageFrame\">
			<div class=\"PageFrameTitle\">
				<table style=\"width:100%\" class=\"frontnewstb\">
					<col width=\"50%\">
					<col width=\"50%\">
					<tr>
						<td class=\"frontnewstitle\"><strong>$title</strong></td>
						<td class=\"frontnewsauthor\">Author: $author</td>
					</tr>
				</table>
			</div>
			<p>";
		echo $body;
		echo "
			</p>
			<p style=\"text-align:right;padding-right:5px;margin-bottom:0;\">Posted: $time</p>
		</div>
		";
		$cnt++;
	}
}
else
{
	if (!isset($_GET["id"])) {
		//Archive listing
		echo "
			<div class=\"frontnewsentry PageFrame\">
				<div class = \"PageFrameTitle\">
					<h1><strong>News Archive</strong></h1>
				</div>
				<table class=\"frontnewsarchivetb\">
					<col width=\"33%\">
					<col width=\"33%\">
					<col width=\"33%\">
					<tr>
						<th>Title</th>
						<th>Date/Time</th>
						<th>Author</th>
					</tr>
		";
		$SQL = "SELECT * FROM tali_news ORDER BY time DESC";
		$result = mysqli_query($db_handle, $SQL);
		while ($db_field = mysqli_fetch_assoc($result)) {
			$id=$db_field['id'];
			$time=$db_field['time'];
			$author=$db_field['author'];
			$title=$db_field['title'];
			$time=strtotime($time);
			$time=date('m\/d\/Y \a\t H:i', $time);
			echo "
					<tr>
						<td><a href=\"news.php?archive=true&id=$id\">$title</a></td>
						<td>$time</td>
						<td>$author</td>
					<tr>
			";
		}
		echo "
				</table>
			</div>
		";
	}
	else
	{
		//Single view from arhive
		$id = $_GET["id"];
		$SQL = "SELECT * FROM tali_news WHERE id = $id";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$id=$db_field['id'];
		$time=$db_field['time'];
		$author=$db_field['author'];
		$title=$db_field['title'];
		$body=$db_field['body'];
		$body=htmlspecialchars_decode($body);
		$body=BBCode2Html($body);
		$time=strtotime($time);
		$time=date('l\, F jS\, Y \a\t H:i', $time);
		echo "
		<div class=\"frontnewsentry PageFrame\">
			<div class=\"PageFrameTitle\">
				<table style=\"width:100%\" class=\"frontnewstb\">
					<col width=\"50%\">
					<col width=\"50%\">
					<tr>
						<td class=\"frontnewstitle\"><strong>$title</strong></td>
						<td class=\"frontnewsauthor\">Author: $author</td>
					</tr>
				</table>
			</div>
			<p>";
		echo $body;
		echo "
			</p>
			<p style=\"text-align:right;padding-right:5px;margin-bottom:0;\">Posted: $time</p>
		</div>
		";
	}
}
?>
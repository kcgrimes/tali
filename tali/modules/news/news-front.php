<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

if (!($articleNumber > 0)) {
	$articleNumber = 1;
}
	
if (!isset($_GET["archive"])) {
	echo "
		<div class=\"tali-news-front-page-frame\">
			<div class = \"tali-news-front-page-frame-title\">
				<h1>Views News Archive</h1>
			</div>
	";
	
	//Custom Execution
	if (TALI_CUSTEXE_NEWS_FRONT_HEADER != "") {
		//Target is defined, so require it
		require TALI_CUSTEXE_NEWS_FRONT_HEADER;
	}
	
	echo "
			<p>To view a list containing all of our news entries, check out the <a href=\"news.php?archive=true\"><strong>News Archive</strong></a>.</p>
		</div>
	";
	//Normal view
	$SQL = "SELECT * FROM tali_news ORDER BY time DESC";
	$result = mysqli_query($db_handle, $SQL);
	$num_rows = mysqli_num_rows($result);
	$cnt=0;
	while (($db_field = mysqli_fetch_assoc($result)) && ($cnt<$articleNumber)) {
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
		<div class=\"tali-news-front-page-frame\">
			<div class=\"tali-news-front-page-frame-title\">
				<h1 class=\"tali-news-front-title\">$title</h1>
				<div class=\"tali-news-front-author\">Author: $author</div>
			</div>
			<p>";
		echo $body;
		echo "
			</p>
			<p style=\"text-align:right;padding-right:5px;\">Posted: $time</p>
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
			<div class=\"tali-news-front-page-frame\">
				<div class = \"tali-news-front-page-frame-title\">
					<h1>News Archive</h1>
				</div>
				<table class=\"tali-news-front-archive-table\">
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
		<div class=\"tali-news-front-page-frame\">
			<div class=\"tali-news-front-page-frame-title\">
				<h1 class=\"tali-news-front-title\">$title</h1>
				<div class=\"tali-news-front-author\">Author: $author</div>
			</div>
		";

		//Custom Execution
		if (TALI_CUSTEXE_NEWS_FRONT_HEADER != "") {
			//Target is defined, so require it
			require TALI_CUSTEXE_NEWS_FRONT_HEADER;
		}

		echo "
			<p>";
		echo $body;
		echo "
			</p>
			<p style=\"text-align:right;padding-right:5px;\">Posted: $time</p>
		</div>
		";
	}
}
?>
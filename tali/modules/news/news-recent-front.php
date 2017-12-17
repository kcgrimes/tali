<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

if (!($articleNumber > 0)) {
	$articleNumber = 1;
}

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
			<div class=\"tali-news-front-index-page-frame\">
				<div class=\"tali-news-front-index-page-frame-title\">
					<h1 class=\"tali-news-front-index-title\">$title</h1>
					<div class=\"tali-news-front-index-author\">Author: $author</div>
				</div>
				<p>
	";
	echo $body;
	echo "
				</p>
				<p style=\"text-align:right;padding-right:5px;\">Posted: $time</p>
			</div>
	";
	$cnt++;
}
?>
<?php
session_start();//must be first
require("dbutils.php");
$db = getDB();
if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["logout"])) //trying to log out
{
	session_unset();
}
function topTen($db, $table, $item) //get ten projects for browsing
{
	$query = "SELECT * FROM $table ORDER BY $item DESC";
	$resultStatement = $db->prepare($query);
	$resultStatement -> execute();
	$results = $resultStatement->fetchAll();
	if(count($results)<10)
		return $results;
	return array_slice($results, 0, 10);
}
?>
<!DOCTYPE html>
<head>
	<title>Craftit</title>
	<link rel="stylesheet" type="text/css" href="default.css"/>
	<link href="https://fonts.googleapis.com/css?family=Amatic+SC%7COpen+Sans%7CRock+Salt" rel="stylesheet">
</head>
<body>
<?php 
	require("headgroup.php"); 
	if(isset($_GET["message"])) //display redirect message
	{
		echo("<script> alert(\"{$_GET["message"]}\");</script>\n");
	}
?>
<h2>Featured</h2>
<div class="flex-container">
	<?php
		$tops = topTen($db, "mburbage_projects", "rating");
		foreach($tops as $project)
		{
			$title = urlencode($project["title"]);
			$image = $project["image"];
			echo("<a href=\"view.php?project={$title}\">\n<img src=\"{$image}\" alt=\"{$project["title"]}\">\n<h2>{$project["title"]}</h2>\n</a>");
		}
	?>
</div>
<h2>Recent</h2>
<div class="flex-container">
	<?php
		$tops = topTen($db, "mburbage_projects", "id");
		foreach($tops as $project)
		{
			$title = urlencode($project["title"]);
			$image = $project["image"];
			echo("<a href=\"view.php?project={$title}\">\n<img src=\"{$image}\" alt=\"{$project["title"]}\">\n<h2>{$project["title"]}</h2>\n</a>");
		}
	?>
</div>
</body>
</html>
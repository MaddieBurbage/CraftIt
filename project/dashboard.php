<?php
session_start();//must be first
require("dbutils.php");
$db = getDB();
checkLogin();
?>
<!DOCTYPE html>
<head>
	<title>Craftit</title>
	<link rel="stylesheet" type="text/css" href="default.css"/>
	<link href="https://fonts.googleapis.com/css?family=Amatic+SC%7COpen+Sans%7CRock+Salt" rel="stylesheet">
</head>
<body>
<?php require("headgroup.php"); ?>
<h2>Saved Projects</h2>
<div class="flex-container">
	<?php
		//display projects saved by the user
		$query = "SELECT mburbage_projects.* FROM mburbage_projects, mburbage_saves WHERE mburbage_projects.id = project_id AND saved = true AND user_id = {$_SESSION["user"]} ORDER BY id DESC";
		$resultStatement = $db->prepare($query);
		$resultStatement -> execute();
		$results = $resultStatement->fetchAll();
		if(count($results)==0)
		{
			echo("<p>Seems you haven't saved any projects yet. <a href=\"search.php\">Browse Projects</a></p>");
		}
		if(count($results)>=10)
			$results = array_slice($results, 0, 10);
		foreach($results as $project)
		{
			$title = urlencode($project["title"]);
			$image = $project["image"];
			echo("<a href=\"view.php?project={$title}\">\n<img src=\"{$image}\" alt=\"{$project["title"]}\">\n<h2>{$project["title"]}</h2>\n</a>");
		}
	?>
</div>
<h2>Created Projects</h2>
<div class="flex-container">
	<?php
		//display projects created by the user
		$query = "SELECT * FROM mburbage_projects WHERE creator_id = {$_SESSION["user"]}";
		$resultStatement = $db->prepare($query);
		$resultStatement -> execute();
		$results = $resultStatement->fetchAll();
		if(count($results)==0)
		{
			echo("<p>Seems you haven't created any projects yet. <a href=\"create.php\">Add one now</a></p>");
		}
		if(count($results)>=10)
			$results = array_slices($results, 0, 10);
		foreach($results as $project)
		{
			$title = urlencode($project["title"]);
			$image = $project["image"];
			echo("<a href=\"view.php?project={$title}\">\n<img src=\"{$image}\" alt=\"{$project["title"]}\">\n<h2>{$project["title"]}</h2>\n</a>");
		}
	?>
</div>
</body>
</html>
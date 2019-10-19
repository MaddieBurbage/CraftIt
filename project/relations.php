<?php
session_start();
require("dbutils.php");
$db = getDB();
$maxSize = 2000000;
function updateRow($user, $project, $column, $value) //either insert or update a user-project row with the value $value in the column $column
{
	$db = getDB();
	$query = "SELECT * FROM mburbage_saves WHERE user_id = :user AND project_id = :project";
	$resultStatement = $db->prepare($query);
	$resultStatement -> execute(array('user'=>$user, "project"=>$project));
	$results = $resultStatement->fetchAll();
	if(count($results)!=0)
	{
		$query = "UPDATE mburbage_saves SET $column = :newvalue WHERE user_id = :user AND project_id = :project";
		$resultStatement = $db->prepare($query);
		$resultStatement -> execute(array('newvalue'=>$value, 'user'=>$user, "project"=>$project));
	}
	else
	{
		$query = "INSERT INTO mburbage_saves(user_id, project_id, $column) VALUES (:user, :project, :newvalue)";
		$resultStatement = $db->prepare($query);
		$resultStatement -> execute(array('newvalue'=>$value, 'user'=>$user, "project"=>$project));
	}
}

if(!empty($_GET["title"]))
{
	$ids = getMatches($db, "mburbage_projects", $_GET["title"], "title", "id");
	if(!empty($_GET["liked"]) && ($_GET["liked"]=="true" || $_GET["liked"]=="false")) //updating whether it's saved
	{
		updateRow($_SESSION["user"], $ids[0][0], "saved", $_GET["liked"]);
	}
	else if(!empty($_GET["comment"])) //updating the comment
	{
		updateRow($_SESSION["user"], $ids[0][0], "comment", $_GET["comment"]);
	}
	else if(!empty($_GET["rating"]) && is_numeric($_GET["rating"]) && (int)$_GET["rating"] <= 5 && (int)$_GET["rating"] > 0) //updating the rating
	{
		updateRow($_SESSION["user"], $ids[0][0], "rating", $_GET["rating"]);
		$ratinglist = getMatches($db, "mburbage_saves", $ids[0][0], "project_id", "rating");
		$total = 0;
		foreach($ratinglist as $rating)
		{
			$total+=(int)$rating[0];
		}
		$total/=count($ratinglist);
		$query = "UPDATE mburbage_projects SET rating = $total WHERE id = {$ids[0][0]}";
		$resultStatement = $db->prepare($query);
		$resultStatement -> execute();
	}
}
if(!empty($_FILES["picture"])) //updating and verifying the picture
{
	if(getimagesize($_FILES["picture"]["tmp_name"])!==false)
	{
		if($_FILES["picture"]["size"]<$maxSize)
		{
			$target = "images/" . "resultimg_" . basename($_FILES["picture"]["name"]);
			if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target)) {
		        echo "The file ". basename( $_FILES["picture"]["name"]). " has been uploaded.";
				$ids = getMatches($db, "mburbage_projects", $_POST["title"], "title", "id");
				updateRow($_SESSION["user"], $ids[0][0], "result", $target);
		    }
		    else
		        echo "Sorry, there was an error uploading your file.";
		}
		else
		    echo "Sorry, file too large";
	}
}
?>
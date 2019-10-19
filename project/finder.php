<?php
session_start();
require("dbutils.php");
$db = getDB();
$query = "SELECT * FROM mburbage_projects WHERE UPPER(title) LIKE UPPER(?)";
if($_GET["sorter"] == "timing") //if sorting by time
{
	$query = "SELECT * FROM mburbage_projects INNER JOIN mburbage_timings ON mburbage_timings.id = mburbage_projects.timing_id WHERE UPPER(title) LIKE UPPER(?)";
}
$params = ["%{$_GET["q"]}%"];
$ids = getMatches($db, "mburbage_categories", $_GET["category"], "name", "id");
if(count($ids)==1) //if filtering by category
{
	$query .= " AND category_id = ?";
	$params[] = $ids[0][0];
}
$ids = getMatches($db, "mburbage_timings", $_GET["timing"], "name", "id");
if(count($ids)==1) //if filtering by time
{
	$query .= " AND timing_id = ?";
	$params[] = $ids[0][0];
}
if(is_numeric($_GET["difficulty"]) && is_int(0 + $_GET["difficulty"]) && $_GET["difficulty"]<=5 && $_GET["difficulty"]>=0) //if filtering by difficulty
{
	$query .= " AND difficulty = ?";
	$params[] = $_GET["difficulty"];
}
if($_GET["sorter"] == "rating"  || $_GET["sorter"] == "difficulty") //if sorting by rating or difficulty just order by them
{
	$query .= " ORDER BY {$_GET["sorter"]} DESC";
}
if($_GET["sorter"] == "timing") //if sorting by time order by that
{
	$query .= " ORDER BY minmin DESC";
}
$resultStatement = $db->prepare($query);
$resultStatement -> execute($params);
$results = $resultStatement->fetchAll();
echo json_encode($results); //https://stackoverflow.com/questions/8823925/how-to-return-an-array-from-an-ajax-call
?>
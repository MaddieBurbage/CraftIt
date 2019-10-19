<?php
require("config.php");
function getDB() //get the database based on config values
{
	try
	{
		$db = new PDO("mysql:dbname={$GLOBALS["database"]};host={$GLOBALS["hostname"]}", $GLOBALS["username"], $GLOBALS["password"]);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOEXCEPTION $ex)
	{
		print("Database error: " . $ex->getMessage());
	}
	return $db;
}
function getMatches($db, $table, $search, $column, $returned)
{ //checks the user database for the $search term in the column $column, returning matches in the column $returned
	try
	{
		$query = "SELECT $returned FROM $table WHERE $column = :somename;";//apparently can't be parametrized, luckily no user input now
		$resultStatement = $db->prepare($query);
		$resultStatement->execute(array('somename' => $search));
		$namematches = $resultStatement->fetchAll();
		return $namematches;
	}
	catch(PDOEXCEPTION $ex)
	{
		print("Database error: " . $ex->getMessage());
	}
}
function isPresent($db, $table, $search, $column)
{ //checks if the $search term is present in the column $column using the getMatches function
	return count(getMatches($db, $table, $search, $column, $column))!=0;
}
function checkLogin()
{ //checks if the user is logging out or already logged out, and redirects to the home page
	if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["logout"])) //trying to log out
	{
		session_unset();
	}
	if(!isset($_SESSION["user"]))
	{
		$mess = urlencode("Log in to view content");
		header("Location: index.php?message=$mess");
	}
}
?>
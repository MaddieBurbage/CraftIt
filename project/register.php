<?php
session_start();
require("dbutils.php");
$db = getDB();
$minval = 8; //minimum password length
$made = false;
$mess = NULL;
function verify($minval)
{
//returns whether the form inputs are okay or not
	if(empty($_POST["newname"]))
	{
		return "<p>Please enter a username</p><br/>";
	}
	else if(empty($_POST["newpass"]) || strlen($_POST["newpass"])<$minval)
	{
		return "<p>Please enter an $minval or more character password</p><br/>";
	}
	else if ($_POST["newpass"]!=$_POST["checkpass"])
	{
		return "<p>Please enter matching passwords</p><br/>";
	}
	return NULL;
}
if ($_SERVER["REQUEST_METHOD"]=="POST") //verify and register user
{
	$mess = verify($minval);
	if($mess==NULL)
	{
		if(!isPresent($db, "mburbage_users", $_POST["newname"], "name"))
		{
			try
			{
				$db = new PDO("mysql:dbname={$GLOBALS["database"]};host={$GLOBALS["hostname"]}", $GLOBALS["username"], $GLOBALS["password"]);
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$query = "INSERT INTO mburbage_users(name, pass) VALUES(:aname, :apass)";
				$resultStatement = $db->prepare($query);
				$resultStatement->execute(array('aname' => $_POST["newname"], 'apass' => password_hash($_POST["newpass"], PASSWORD_DEFAULT)));
			}
			catch(PDOEXCEPTION $ex)
			{
				print("Database error: " . $ex->getMessage());
			}
			$mess = urlencode("Registration successful, you can log in!");
			header("Location: index.php?message=$mess");
		}
	}
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
		if($_SERVER["REQUEST_METHOD"]=="POST")
		{
			if(!$made)
			{
				echo "<p>Sorry but that username is already taken</p><br/>";
			}
			if($mess!=NULL)
			{
				echo $mess;
			}
			echo "<a href=\"\">Retry</a>";
		}
		else
		{
	?>
	<h1>Register for Craftit</h1>
	<p>Enter your desired login information to create an account</p>
	<form method="POST">
		Username ..................................... <input type="text" name="newname" /> <br/>
		Password (At least <?php echo("$minval"); ?> characters) <input type="password" name="newpass"/> <br/>
		Confirm Password ....................... <input type="password" name="checkpass" /> <br/>
		<input type="submit" value="Submit!"/>
	</form>
<?php
	}
?>
</body>
</html>
<!--The standard header-->
<div id="top">
	<div id="logo">
		<h1><a href="index.php">CRAFT IT</a></h1>
	</div>
	<?php
		$url = strtok($_SERVER['REQUEST_URI'], '?');
		if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["login"]))//if trying to log in
		{
			if(isPresent($db, "mburbage_users", $_POST["username"], "name"))
			{
				$passmatches = getMatches($db, "mburbage_users", $_POST["username"], "name", "pass");
				if(count($passmatches)!=0 && password_verify($_POST["code"], $passmatches[0][0]))
				{
					$_SESSION["user"] = getMatches($db, "mburbage_users", $_POST["username"], "name", "id")[0]["id"];//session is user_id
				}
			}
		}
		if(isset($_SESSION["user"])) //if logged in
		{
	?>
	<div class="but" onclick="document.getElementById('logoutform').submit();">
		<h2>Log Out</h2>
		<form id="logoutform" method="POST" action=<?php echo "\"$url\""?>>
			<input type="hidden" name="logout" value="Logout">
		</form>
	</div>
	<div class="but">
		<h2><a href="dashboard.php"><?php echo(getMatches($db, "mburbage_users", $_SESSION["user"], "id", "name")[0][0])?></a></h2>
	</div>
	<div class="but">
		<h2><a href="create.php">Create</a></h2>
	</div>
	<?php
		}
		else //if logged out
		{
	?>
	<div class="container">
		<h2>Log In</h2>
			<form method="POST" action=<?php echo "\"$url\""?>>
				Username: <br/>
				<input type="text" name="username" /> <br/>
				Password: <br/>
				<input type="password" name="code" /> <br/>
				<input type="submit" name="login" value="Submit!"/>
			</form>
	</div>
	<div class="but">
		<h2><a href="register.php">Register</a></h2>
	</div>
	<?php
		} //and always display these
	?>
	<div class="but">
		<h2><a href="search.php">Browse</a></h2>
	</div>
	<div class="container">
		<h2>Search</h2>
		<form method="GET" action="search.php">
			<input type="text" name="query"><br/>
			<br/>
			<input type="submit" value="Search">
		</form>
	</div>
</div>
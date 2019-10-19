<?php
session_start();//must be first
require("dbutils.php");
$db = getDB();
if($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST["logout"])) //trying to log out
{
	session_unset();
}
function populateList($db, $table, $item) //fill options based on table column values
{
	$query = "SELECT $item FROM $table";
	$resultStatement = $db->prepare($query);
	$resultStatement -> execute();
	$results = $resultStatement->fetchAll();
	echo "<option value=\"Any\">Any</option>";
	foreach($results as $result)
	{
		echo "<option value=\"$result[0]\">$result[0]</option>\n";
	}
}
$start = "";
?>
<!DOCTYPE html>
<head>
	<title>CraftIt</title>
	<link rel="stylesheet" type="text/css" href="default.css"/>
	<link href="https://fonts.googleapis.com/css?family=Amatic+SC%7COpen+Sans%7CRock+Salt" rel="stylesheet">
</head>
<body>
<script>
	function getResults() //use an ajax request to get search results based on inputs
	{
		var search = document.getElementById("search").value;
		var sorter = document.getElementById("sorting").value;
		var cat = document.getElementById("cat").value;
		var timing = document.getElementById("timing").value;
		var difficulty = document.getElementById("difficulty").value;
		var xmlhttp;
		if (window.XMLHttpRequest)
		{
			xmlhttp=new XMLHttpRequest();
			}
			else
			{
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange=function()
			{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
  				var tops = xmlhttp.response;
  				var container = document.getElementById("pics");
  				while(container.hasChildNodes())
  				{
  					container.removeChild(container.firstChild);
  				}
  				for(var i = 0; i<tops.length; i++)
				{
					var project = tops[i];
					var nextLink = document.createElement("a");
					var title = project.title;
					nextLink.href = "view.php?project=" + title;
					var nextImage = document.createElement("img");
					nextImage.src = project.image;
					nextImage.alt = project.title;
					var nextTitle = document.createElement("h2");
					nextTitle.innerHTML=title;
					nextLink.appendChild(nextImage);
					nextLink.appendChild(nextTitle);
					container.appendChild(nextLink);
				}
			}
		}
		xmlhttp.open("GET","finder.php?q="+search+"&sorter="+sorter+"&category="+cat+"&timing="+timing+"&difficulty="+difficulty,true);
		xmlhttp.responseType = "json";
		xmlhttp.send();
	}
</script>
<?php 
require("headgroup.php"); 
if(isset($_GET["query"]))
{
	$start = $_GET["query"];
}
?>
<div id="filters">
	<div class="box">
		<h3>Search Term</h3>
		<input type="text" id="search" name="search" onchange="getResults()" value=<?php echo $start ?>> <br/>
	</div>
	<div class="box">
		<h3>Sorting</h3>
		<select id="sorting" name="sorting" onchange="getResults()">
			<option value="rating">Rating</option>
			<option value="timing">Time</option>
			<option value="difficulty">Complexity</option>
		</select>
	</div>
	<div class="box">
		<h3>Filtering</h3>
		Category<select id="cat" name="cat" onchange="getResults()"> 
			<?php
				populateList($db, "mburbage_categories", "name");
			?>
		</select> <br/>
		Time<select id="timing" name="timing" onchange="getResults()"> 
			<?php
				populateList($db, "mburbage_timings", "name");
			?>
		</select> <br/>
		Complexity<select id="difficulty" name="difficulty" onchange="getResults()"> 
			<?php
				echo "<option value=\"Any\">Any</option>";
				for($n=1;$n<=5;$n++)
				{
					echo "<option value=\"$n\">$n</option>\n";
				}
			?>
		</select> <br/>
	</div>
</div>
<h2>Results</h2>
<div class="flex-container" id="pics">
</div>
<script>
	getResults();
</script>
</body>
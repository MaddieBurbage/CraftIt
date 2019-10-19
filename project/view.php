<?php
session_start();//must be first
require("dbutils.php");
$db = getDB();
checkLogin();
if(isset($_GET["project"]))
{
	$title = $_GET["project"];
	$projects = getMatches($db, "mburbage_projects", $title, "title", "*"); //grab the project given from the url
}
$commax = 500; //max comment
function populateList($db, $title) //grab project with name $title from the $db database
{
	$query = "SELECT * FROM mburbage_saves WHERE project_id = $title ORDER BY 'id' ASC";
	$resultStatement = $db->prepare($query);
	$resultStatement -> execute();
	$results = $resultStatement->fetchAll();
	return $results;
}
?>
<!DOCTYPE html>
<head>
	<title>Craftit</title>
	<link rel="stylesheet" type="text/css" href="default.css"/>
	<link rel="stylesheet" type="text/css" href="projectlayout.css"/>
	<!-- Font Awesome Icon Library -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Amatic+SC%7COpen+Sans%7CRock+Salt" rel="stylesheet">
</head>
<body>
	<?php
		require("headgroup.php");
		if(!isset($_GET["project"]))
		{
			echo "<p>Sorry, no project found. <a href=\"search.php\">Browse Projects</a></p>";
			die();
		}
		else
		{
			$project = $projects[0];
			$timing = getMatches($db, "mburbage_timings", $project["timing_id"], "id", "name")[0][0];
			$creator = getMatches($db, "mburbage_users", $project["creator_id"], "id", "name")[0][0];
			$steps = getMatches($db, "mburbage_steps", $project["id"], "project_id", "*");
			$tools = getMatches($db, "mburbage_tools", $project["id"], "project_id", "*"); //maybe to add quantities later???
			$supplies = getMatches($db, "mburbage_supplies", $project["id"], "project_id", "*");
		}
	?>
	<script>
		var maxSize = 2000000;
		function toggleLike(x) //when x is clicked, toggle whether project is saved
		{
			var liked = (x.classList=="fa fa-heart-o");
			if(liked)
			{
				x.classList = "fa fa-heart";
				x.firstChild.innerHTML = "Saved";
			}
			else
			{
				x.classList = "fa fa-heart-o";
				x.firstChild.innerHTML = "Save";
			}
			var title = document.getElementById("title").innerHTML;
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
      				alert("Settings changed");
    			}
  			}
  			xmlhttp.open("GET","relations.php?title="+title+"&liked="+liked,true);
  			xmlhttp.send();
		}
		function addComment() //add a comment to the user-project database
		{
			var newcomment = document.getElementById("newcomment").value;
			var title = document.getElementById("title").innerHTML;
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
      				alert("Comment added");
    			}
  			}
  			xmlhttp.open("GET","relations.php?title="+title+"&comment="+newcomment,true);
  			xmlhttp.send();
		}
		function toggleStar(x) //when x is clicked change the rating for the project
		{
			var rating = x.id.slice(0,1);
			for(var i = 0; i<x.parentNode.children.length; i++)
			{
				x.parentNode.children[i].classList = (i<rating)? "fa fa-star" : "fa fa-star-o";
			}
			var title = document.getElementById("title").innerHTML;
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
      				alert("Settings changed");
    			}
  			}
  			xmlhttp.open("GET","relations.php?title="+title+"&rating="+rating,true);
  			xmlhttp.send();
		}
		function addFile(x) //upload a result picture to the user-project database when x is clicked
		{
			var picture = x.files[0];
			if(picture.size>maxSize)
			{
				alert("File needs to be under " +  maxSize/1000 + " kilobytes, right now it's " + picture.size/1000 + " kilobytes");
				return;
			}
			var formData = new FormData();
			formData.append("picture", picture);
			formData.append("title", document.getElementById("title").innerHTML);
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
      				alert(xmlhttp.responseText);
    			}
  			}
			xmlhttp.open("POST", "relations.php", true);
			xmlhttp.send(formData);
		}
		function expand(x) //expand the tipbox when x is clicked
		{
			tipbox = x.parentNode;
			tipbox.classList = (tipbox.classList=="small")? "big" : "small";
		}
	</script>
	<div id="heading">
		<h1 id="title"><?php echo $project["title"] ?></h1>
		<img id="intropic" src=<?php echo $project["image"] ?>>
		<div id="snapshot">
				<?php
					$query = "SELECT saved FROM mburbage_saves WHERE user_id = {$_SESSION["user"]} AND project_id = {$project["id"]}";
					$resultStatement = $db->prepare($query);
					$resultStatement -> execute();
					$results = $resultStatement->fetchAll();
					$heart = "fa fa-heart-o";
					if(count($results)!=0) {
						$heart = $results[0]["saved"]=="true"? "fa fa-heart" : "fa fa-heart-o";
					}
					echo("<span class=\"{$heart}\" onclick=\"toggleLike(this)\"><p id=\"like\">Save</p></span> <br/>");
					$query = "SELECT rating FROM mburbage_saves WHERE user_id = {$_SESSION["user"]} AND project_id = {$project["id"]}";
					$resultStatement = $db->prepare($query);
					$resultStatement -> execute();
					$results = $resultStatement->fetchAll();
					$rated = count($results)!=0? $results[0][0] : 0;
					echo("<span id=\"stars\">");
					for($i = 1; $i<=5; $i++)
					{
						$facon = $rated<$i? "-o" : "";
						echo("<span id=\"{$i}st\" class=\"fa fa-star{$facon}\" onclick=\"toggleStar(this)\"></span>");
					}
					echo("</span>");
				?>
			<p>Rating: <?php echo $project["rating"] ?> </p>
			<p>Time: <?php echo $timing ?> </p>
			<p>Complexity: <?php echo $project["difficulty"] ?> </p>
			<p>Creator: <?php echo $creator ?> </p>
		</div>
		<div id="descr">
			<h2>Description</h2>
			<p> <?php echo $project["blurb"] ?> </p>
		</div>
	</div>
	<div id="substance">
		<div id="steps">
			<?php
				//display project steps
				foreach($steps as $step)
				{
					echo "<h2>{$step["step"]}</h2>\n";
					echo "<p>{$step["description"]}</p>\n";
					if($step["photo"]!=NULL)
					{
						echo "<img src=\"{$step["photo"]}\"><br/>\n";
						echo "<p class=\"caption\">{$step["caption"]}</p>\n";
					}
				}
			?>
		</div>
		<div id="sider">
			<h2>Tools</h2>
			<ul class="items">
				<?php
					foreach($tools as $tool)
					{
						echo "<li>{$tool["name"]}</li>\n";
					}
				?>
			</ul>
			<h2>Supplies</h2>
			<ul class="items">
				<?php
					foreach($supplies as $supply)
					{
						echo "<li>{$supply["name"]}</li>\n";
					}
				?>
			</ul>
		</div>
	</div>
	<div id="adding">
		<h2>Did you make this?</h2>
		<textarea id="newcomment" maxlength=<?php echo $commax ?> placeholder="New comment or tip?"/></textarea> <br/>
		<button type="button" id="comment" onclick="addComment(this)">Add</button>
		<p>Made this? Post your result photo!</p>
		<input type="file" name="pic" accept="image/*" onchange="addFile(this)">
	</div>
	<div id="tipbox" class="small">
		<div onclick="expand(this)"> <span class="fa fa-chevron-down"></span></div>
		<div id="comments" class="grid">
			<h3>Comments</h3>
			<?php
				$results = populateList($db, $project["id"]);
				foreach($results as $result)
				{
					if($result["comment"]!="")
					{
						$commenter = getMatches($db, "mburbage_users", $result["user_id"], "id", "name")[0][0];
						echo "<p>{$result["comment"]}\n~$commenter</p>\n";
					}
				}
			?>
		</div>
		<div id="results" class="grid">
			<h3>Result Photos</h3>
			<?php
				foreach($results as $result)
				{
					if($result["result"]!=NULL)
					{
						echo "<img class=\"grid\" src=\"{$result["result"]}\"><br/>\n";
					}
				}
			?>
		</div>
	</div>
</body>
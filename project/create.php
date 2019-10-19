<?php
session_start();
require("dbutils.php");
$db = getDB();
checkLogin();
if(!isset($_SESSION["user"]))
{
	header('Location: index.php');
}
$titleMax = 100;
$descrMax = 500;
$stepMax = 100;
$picMax = 2000000;
$diffMin = 1;
$diffMax = 5;
function checkLength($query, $highest) //check if $query is longer than $highest
{
	return strlen($query)<=$highest;
}
function populateList($db, $table, $item, $sorting) //fill options based on table column values
{
	$query = "SELECT $item FROM $table ORDER BY $sorting";
	$resultStatement = $db->prepare($query);
	$resultStatement -> execute();
	$results = $resultStatement->fetchAll();
	foreach($results as $result)
	{
		echo "<option value=\"$result[0]\">$result[0]</option>\n";
	}
}
function uploadImage($title, $tag) //verify and upload image
{
	global $picMax;
	if(!empty($_FILES[$title]) && !empty($_FILES[$title]["tmp_name"]))
	{
		if(getimagesize($_FILES[$title]["tmp_name"])!==false)
		{
			if($_FILES[$title]["size"]< $picMax)
			{
				$bits = explode(".", $_FILES[$title]["name"]);
				$target = "images/" . $tag . "_" . time() . "." . $bits[(count($bits)-1)];
				if (move_uploaded_file($_FILES[$title]["tmp_name"], $target))
				{
			        echo "The file ". basename( $_FILES[$title]["name"]). " has been uploaded.";
			        return $target;
			    }
			    else
			        echo "Sorry, there was an error uploading your file.";
			}
			else
			    echo "Sorry, file too large";
		}
	}
	return NULL;
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
		if ($_SERVER["REQUEST_METHOD"]=="POST") //Verify and save project 
		{
			echo("<br/>");
			if(!checkLength($_POST["newtitle"], $titleMax) || !checkLength($_POST["description"], $descrMax)) //verifies lengths
			{
				echo "<p>Your title or description is too long</p><br/>";
			}
			else if(isPresent($db, "mburbage_projects", $_POST["newtitle"], "title")) //Checks if title is available
			{
				echo "<p>Choose a different title, title already taken</p><br/>";
			}
			else if(count(getMatches($db, "mburbage_categories", $_POST["category"], "name", "id"))==0) //Checks if category is real
			{
				echo "<p>Unknown Category</p><br/>";
			}
			else if(count(getMatches($db, "mburbage_timings", $_POST["timing"], "name", "id"))==0) //Checks if timing is real
			{
				echo "<p>Unknown Timing</p><br/>";
			}
			else if($_POST["difficulty"]<$diffMin && $_POST["difficulty"]>$diffMax) //Checks if difficulty is real
			{
				echo "<p>Difficulty must be between $diffMin and $diffMax</p><br/>";
			}
			else //if inputs pass all the tests
			{
				$pic = uploadImage("intropic", "introimg"); //try uploading the cover photo
				if(is_null($pic))
				{
					echo "<p>Need valid picture</p><br/>";
				}
				else
				{ //upload the project values to the databases
					$catId = getMatches($db, "mburbage_categories", $_POST["category"], "name", "id")[0]["id"];
					$timingId = getMatches($db, "mburbage_timings", $_POST["timing"], "name", "id")[0]["id"];
					$query = "INSERT INTO mburbage_projects(title, category_id, rating, timing_id, difficulty, image, creator_id, blurb) VALUES(:atitle, $catId, 0, $timingId, :adiff, :aimage, {$_SESSION['user']}, :ablurb)"; //0 because rating starts low
					$resultStatement = $db->prepare($query);
					$resultStatement -> execute(array('atitle'=>$_POST["newtitle"], 'adiff'=>$_POST["difficulty"], 'aimage'=>$pic, 'ablurb'=>$_POST["description"]));
					$postId = getMatches($db, "mburbage_projects", $_POST["newtitle"], "title", "id")[0]["id"];
					$tools = array_filter(explode(", ", $_POST["tools"]));
					foreach($tools as $value)
					{
						$query = "INSERT INTO mburbage_tools(project_id, name) VALUES($postId, :tool)";
						$resultStatement = $db->prepare($query);
						$resultStatement -> execute(array('tool'=>$value));
					}
					$supplies = array_filter(explode(", ", $_POST["supplies"]));
					foreach($supplies as $value)
					{
						$query = "INSERT INTO mburbage_supplies(project_id, name) VALUES($postId, :supply)";
						$resultStatement = $db->prepare($query);
						$resultStatement -> execute(array('supply'=>$value));
					}
					$n=1;
					while(isset($_POST["stepname".$n]))
					{
						$pic = uploadImage("steppic".$n, "stepimg".$n);
						$query = "INSERT INTO mburbage_steps(project_id, step, description, photo, caption) VALUES($postId, :name, :descr, :photo, :caption)";
						$resultStatement = $db->prepare($query);
						$resultStatement -> execute(array("name"=>$_POST["stepname".$n], "descr"=>$_POST["stepdesc".$n], "photo"=>$pic, "caption"=>$_POST["stepcap".$n]));
						$n++;
					}
					echo "<p>Project created!</p>";
					echo "<a href =\"view.php?project={$_POST["newtitle"]}\">View Project</a>";
				}
			}
		}
		else //if not uploading, show project creation form
		{
	?>
	<h1>Create Project</h1>
	<p>Fill out each field to create a project</p>
	<form method="POST" enctype="multipart/form-data">
		<input type="text" name="newtitle" placeholder="Enter Project Name" maxLength=<?php echo $titleMax ?> /> <br/>
		How long does it take to make?<select name="timing"> 
			<?php
				populateList($db, "mburbage_timings", "name", "minmin");
			?>
		</select> <br/>
		How difficult is it? (1-5) <input type="number" name="difficulty" min="1" Max="5"> <br/>
		Quick Description: <textarea name="description" maxLength=<?php echo $descrMax ?> placeholder="Briefly describe your project"></textarea> <br/>
		Upload cover photo: <input type="file" name="intropic" accept="image/*"> <br/>
		Enter tools, separated by commas: <input type="text" name="tools"/>
		Enter supplies, separated by commas: <input type="text" name="supplies"/> <br/>
		<div id="steps">
			Describe each step of the project: <br/>
			<div id="step1">
				<h3>Step 1</h3>
				<input type="text" name="stepname1" placeholder="Step Name" maxLength=<?php echo $stepMax ?> /> <br/>
				<textarea name="stepdesc1" placeholder="Step Description"></textarea> 
				<p>Add Picture</p><input type="file" name="steppic1" accept="image/*">
				<input type="text" name="stepcap1" placeholder="Photo Caption" maxLength=<?php echo $stepMax ?>>
			</div>
		</div>
		<script>
			function nextStep() //add inputs for another step
			{
				var last = document.getElementById("steps").lastElementChild.id;
				var n = 1 + (+last.slice(-1));
				var stepbox = document.createElement("div");
				stepbox.id = "step" + n;
				var stepTitle = document.createElement("h3");
				stepTitle.innerHTML = "Step " + n;
				var stepname = document.createElement("input");
				stepname.type = "text";
				stepname.name = "stepname" + n;
				stepname.placeholder = "Step Name";
				stepname.maxLength=<?php echo $stepMax ?>;
				var stepdescr = document.createElement("textarea");
				stepdescr.name = "stepdesc" + n;
				stepdescr.placeholder = "Step Description";
				var label = document.createElement("p");
				label.innerHTML="Add Picture";
				var steppic = document.createElement("input");
				steppic.type = "file";
				steppic.name="steppic" + n;
				steppic.accept="image/*";
				var stepcap = document.createElement("input");
				stepcap.type = "text";
				stepcap.name = "stepcap" + n;
				stepcap.placeholder = "Photo Caption";
				stepcap.maxLength=<?php echo $stepMax ?>;
				stepbox.appendChild(stepTitle);
				stepbox.appendChild(stepname);
				stepbox.appendChild(document.createElement("br"));
				stepbox.appendChild(stepdescr);
				stepbox.appendChild(label);
				stepbox.appendChild(steppic);
				stepbox.appendChild(stepcap);
				document.getElementById("steps").appendChild(document.createElement("br"));
				document.getElementById("steps").appendChild(stepbox);
			} 
			function lastStep() //deletes the last step
			{
				var parent = document.getElementById("steps")
				var child = parent.lastElementChild;
				if(child.id.slice(-1)!="1")
				{
					parent.removeChild(child);
					parent.removeChild(parent.lastElementChild);//to also remove the br
				}
			}
		</script>
		<br/>
		<button type="button" id="add1" onclick="nextStep()">Add</button>
		<button type="button" id="remove1" onclick="lastStep()">Remove</button><br/>
		Category<select name="category">
			<?php
				populateList($db, "mburbage_categories", "name", "name");
			?>
		</select> <br/>
		<input type="submit" value="Submit!"/>
	</form>
<?php
	}
?>
</body>
</html>
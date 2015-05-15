<html>
<title>Umassmed lncRNA</title>
<head>
<link rel="stylesheet" type="text/css" href="css/background.css">
<link rel="stylesheet" type="text/css" href="css/results.css">

<script src="http://code.jquery.com/jquery-1.7.1.min.js" type = "text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script src="js/switch.js" type = "text/javascript"></script>

<?php
	include 'php/database.php';
	session_start();
	//Normal Search
	if (isset($_REQUEST["sKey"])) {
		unset($_SESSION["loc"]);
		unset($_SESSION["loc"]);
		$tempKey = $_REQUEST["sKey"];
	}
	//Using "Back to Search" navigation button
	elseif (isset($_SESSION["sKey"])) $tempKey = $_SESSION["sKey"];
	
	//Sanitize input
	$clean = checkInput($tempKey);

	if (isset($tempKey)) {
		if(strpos($tempKey, "chr") > -1) {
			//Use search key as coordinates
			$coords = $tempKey;
		}
		else {
			//Use search key as geneID
			$geneID = $tempKey;
		}
	}
	else {
		header('Location: /~wespisea/search.html');
	}
	
	$sKey;
	//Decide search parameters and create query	
	if($geneID != ""){
		$sKey = $geneID;
        	$sql = "SELECT geneID, transID, chromosome, low, high FROM TransData WHERE geneID LIKE '" .$geneID .  "%' ORDER BY geneID";
	}
	elseif($coords != ""){
		$sKey = $coords;
		//split chr:low-high input into array
		$coords = preg_split("(chr|:|-)", $coords, NULL, PREG_SPLIT_NO_EMPTY);
		$chromosome = $coords[0];
		$low = removeComma($coords[1]);
		$high = removeComma($coords[2]);
		$sql = "SELECT geneID, transID, chromosome, low, high FROM TransData WHERE chromosome LIKE '" . $chromosome . "' AND " . $low . "<high AND " . $high . ">low ORDER BY (abs(" . $low . "-low)+abs(". $high . "-high))";
	}	
	else {
		header('Location: /~wespisea/search.html');
	}
	
	//Access database
	$db = new lncRNA_DB();
        if (!$db) {
                echo $db->lastErrorMsg();
        }

	if ($clean) {
		$data = $db->getAll($sql);
	}
?>

<script>
var data; //Array to hold all geneID search results
var link; //Link beginning for each geneID
var dataLength; //Number of search results
var loc; //Current location for results navigation
var table; //Main search results table
var sKey;
onload = function() {
	data = <?php echo json_encode($data)?>;
	link = 'http://bib.umassmed.edu/~wespisea/data.php?geneID=';
	dataLength = <?php echo count($data)?>;
	sKey = "<?php echo $sKey?>";
	loc = 0;
	$.ajax({
		type: 'POST',
		url: 'php/save.php',
		data: {session: "stop"},
		async: false,
		success: function(result) {
			loc = parseInt(result);
		}
	});
	table = document.getElementById('genes');
	var end = loc + 20;
	if(end > data.length) end = data.length;
	if(dataLength == 0) loc = -1;
	$('#loc').html("Results " + (loc+1) + "-" + end + " of " + dataLength);
	updateTable("cur");
}

function saveInfo() {
	$.ajax({
		type: 'POST',
		url: 'php/save.php',
		data: {session: "start", "loc": loc, "sKey": sKey}, 
		cache: false,
		async: false,
		success: function(result) {
		}
	});

}

/*
 * Updates the search results table based on the direction parameter which takes one of the 
 * following string inputs:
 *
 * next - moves to the next 20 search results, does nothing if already at the end
 * prev - moves to the previous 20 search results, does nothing if already at beginning
 * first - moves to the first 20 or less search results, does nothing if already at beginning
 * last - moves to the last 20 or less search results, does nothing if already at end
 * 
 */
function updateTable(dir) {
	if (dir == "next" || dir == "prev" || dir == "first" || dir == "last" || dir == "cur") {
		var counter; //index for data array
		if (data.length == 0) return;
		if (dir == "next") {
			loc += 20;
			if (loc > dataLength) {
				loc -= 20;
				return;
			}
		}
		else if (dir == "prev") {
			loc -= 20;
			if (loc < 0) {
				loc += 20;
				return;
			}
		}
		else if(dir == "first") {
			loc = 0;
		}
		else if(dir == "last") {
			loc = dataLength - dataLength%20;
			if (loc == dataLength) loc -= 20;
		}
		var counter = loc; //index for data array	
		var cName = ""; //style class name
		
		//Fill table cells with data results
		for (var i = 1; i < table.rows.length; i++) {
			var cells = table.rows[i].cells;
			
			if (data[counter]) {
				var pastID;
				var curID = data[counter]['geneID'];
				if (pastID != curID) {
					cells[0].innerHTML = "<a href =" + link + data[counter]['geneID'] + " onclick = 'saveInfo()'>" + data[counter]['geneID'] + "</a></td>";
					pastID = curID;
					//swap row styles
					if (cName == "") cName = "alt";
					else cName = "";
				}
				else {
					cells[0].innerHTML = "";
				}
				table.rows[i].className = cName;
				cells[1].innerHTML = data[counter]['transID'];
				cells[2].innerHTML = "chr" + data[counter]['chromosome'] + ":" + addComma(data[counter]['low']) + "-" + addComma(data[counter]['high']);
				counter++;
			}
			else {	
				//fill end of last table with blanks
				table.rows[i].className = cName;
				cells[0].innerHTML = "";
				cells[1].innerHTML = "";
				cells[2].innerHTML = "";
			}
		}
		$('#loc').html("Results " + (loc+1) + "-" + counter + " of " + dataLength);
	}
}

</script>
</head>
<body>
<div id = "wrap">
	<div id = "header">
		<div id = "lnc">lncRNA</div>
	</div>
	<div id = "nav">
                <div><a href="http://bib.umassmed.edu/~wespisea/search.html">Home</a></div>
                <div><a href="http://bib.umassmed.edu/~wespisea/references.html">References</a></div>
                <div><a href="http://bib.umassmed.edu/~wespisea/about.html">About</a></div>
        </div>
	<div id = "main">
		<div id = "content">
			<form action = "results.php" method = "post">
			<fieldset>
			<label id = "search">Search:</label>
			<input type = "text" name = "sKey">
			<div class = "submit"><input type = "submit"></div>	
			</fieldset>
			</form>
		
			<h3>Search results</h3>
			<p id = "resultsStats"></p>
	<?php	
	//Set up table
	if (count($data) > 0) {
		echo	"Search for " . $sKey . " yields the following results: ";
		echo 	"<table id = genes>
        	        <tr>
        	        <th>Gene ID</th>
        	        <th>Transcript ID</th>
               		<th>Coordinates</th>
                	</tr>";
		//Set up data rows
		while(($counter < count($data))&&($counter < 20)) {
                	$curRow = $data[$counter];
       	         	echo "<tr>
				<td></td>
				<td></td>
				<td></td>
				</tr>";
                	$counter++;
       		}
        	echo "</table><br>";
	}
	else {
		echo "No matches found for \"" . $sKey . "\". Please try another name to search.";
	}
	$db->close();
		?>
	
			<div id = "table_nav">
			<div id = "loc"></div>
			<button name = "<<" type = "button" value = "first" onclick = "updateTable(this.value)"><<</button>
			<button name = "<" type = "button" value = "prev" onclick = "updateTable(this.value)"><</button>
			<button name = ">" type = "button" value = "next" onclick = "updateTable(this.value)">></button>
			<button name = ">>" type = "button" value = "last" onclick = "updateTable(this.value)">>></button>
			</div>
		</div>
	</div>
	<div id = "footer">
		@2013 ZLab
	</div>
</div>
</body>
</html>

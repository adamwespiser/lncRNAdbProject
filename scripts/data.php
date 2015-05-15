<html>
<title>Umassmed lncRNA</title>
<head>
<link rel="stylesheet" type="text/css" href="css/background.css">
<link rel="stylesheet" type="text/css" href="css/data.css">

<!--AJAX library-->
<script src="http://code.jquery.com/jquery-1.7.1.min.js" type = "text/javascript"></script>

<!--HighCharts library-->
<script src="js/highcharts/highcharts.js" type = "text/javascript"></script>
<script src="js/highcharts/grid.js" type = "text/javascript"></script>
<script src="js/switch.js" type = "text/javascript"></script>
<?php
	include 'php/database.php';
	if (isset($_REQUEST["geneID"]) && checkInput($_REQUEST["geneID"])) {
		$sKey = $_REQUEST["geneID"];
	}
	else {
		header('Location: /~wespisea/search.html');
	}
	//Sanitize input

	//Construct variables for html
	$geneID = explode(".", $sKey);
	$geneID = $geneID[0];
	$ensemblLink = "http://ensembl.org/Multi/Search/Results?species=all;idk=;q=" . $geneID;
	
	/*Construct data for Transcript Expression Chart*/
	//Access database
	$db = new lncRNA_DB();
	if (!$db) echo $db->lastErrorMsg();
	$sql = "SELECT * FROM TransData WHERE geneID LIKE '" . $sKey . "'";
	$transData = $db->getAllOrganized($sql);
	$transIDs = array_keys($transData);
	unset($transIDs[count($transIDs) - 1]);
	
	if (count($transIDs) < 1) {
		header('Location: /~wespisea/search.html');
	}
?>

<script>
var entropyChart;
var transChart;
var transData = <?php echo json_encode($transData);?>;
var transIDs = <?php echo json_encode($transIDs);?>;
	
$(document).ready(function() {
	

	entropyChart = new Highcharts.Chart({
	    chart: {
		renderTo: 'entropyChart',
		type: 'column'
	    },
	    colors: [
		'#A6CEE3',
		'#1F78B4',
		'#B2DF8A',
		'#33A02C',
		'#FB9A99',
		'#E31A1C',
		'#FDBF6F',
		'#FF7F00',
		'#CAB2D6',
		'#6A3D9A',
	    ],
	    title: {
                text: 'Transcript Entropy Expression',
                x: -20 //center
            },
            subtitle: {
                text:  'Gene <?php echo $sKey;?>',
                x: -20
            },
            tooltip: {
            	formatter: function(){
			var seriesName = this.series.name;
			var expr = this.x;
			var value = this.y;
			return seriesName + "<br>" +
				expr + ": " + value; 
		}
	    },
            xAxis: {
		title: { text: 'Expression Type' },
                categories: <?php echo json_encode($entropyTypes);?>,
		},
            yAxis: {
                title: {
                    text: 'J/K'
                },
		type: 'logarithmic',
		minorTickInterval: 0.1
            },
           legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
	});
	
	for (var i = 0; i < transIDs.length; i++)
	{
		var curID = transIDs[i];	
		entropyChart.addSeries({
			name: curID,
			data: transData[curID]['entropyData']
			});
        }


	transChart = new Highcharts.Chart({
	    chart: {
		renderTo: 'transChart',
		type: 'line'
	    },
	    colors: [
		'#000000',
		'#555555',
		'#A6CEE3',
		'#5B717D',
		'#1F78B4',
		'#114263',
		'#B2DF8A',
		'#6B8653',
		'#33A02C',
		'#1F601A',
		'#FB9A99',
		'#975C5C',
		'#E31A1C',
		'#7D0E0F',
		'#FDBF6F',
		'#8B693D',
		'#FF7F00',
		'#8C4600',
		'#CAB2D6',
		'#6F6276',
		'#6A3D9A',
		'#3A2255'	    
	    ],
	    title: {
                text: 'Transcript Expression Across Cell Type',
                x: -20 //center
            },
            subtitle: {
                text:  'Gene <?php echo $sKey;?>',
                x: -20
            },
            xAxis: {
		title: { text: 'Cell Type Categorical' },
                categories: <?php echo json_encode($cellTypes);?>,
		labels: { staggerLines: 2 }
		},
            yAxis: {
                title: {
                    text: 'RPKM'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
            	formatter: function(){
			var seriesName = this.series.name;
			var pivot = seriesName.indexOf("Long");
			var transID = seriesName.substring(0,pivot);
			var exprType = seriesName.substring(pivot);
			var cellType = this.x;
			var value = this.y;
			return transID + "<br>" +
				exprType + "<br>" + 
				"Cell Type: " + cellType + "<br>" +
				value + " RPKM";
		}
	    }, 
           legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
	    series: [{
		id: 'Max Long Poly A',
		name: 'Max Long Poly A',
		data: transData['Max']['polyData']
		}, {
		id: 'Max Long Non Poly A',
		name: 'Max Long Non Poly A',
		data: transData['Max']['nonPolyData']
		}]
	});
	
	//Add data to transcript chart
	for (var i = 0; i < transIDs.length; i++)
	{
		var curID = transIDs[i];
		addSeries(transChart, curID);
        }

	//Construct the summary statistics tables
	var tables = "";
	
	//Create reference table
	tables += "<tr><th>Transcript ID</th><th>Coordinates</th><th>Max Expression Type</th><th>Three Times Rest</th><th>Tissue Specificity</th></tr>";
	

	//Creates summary stats tables for each transcript	
	for (var ctr = 0; ctr < multi_getLength(transData)-1; ctr++) {
		var curID = transIDs[ctr]
		//Header
		tables += "<tr><td>" + curID + "</td>";
		//Body
		var coords = "chr" + transData[curID]["coordsData"]["chromosome"] + ":" + addComma(transData[curID]["coordsData"]["low"]) + "-" + addComma(transData[curID]["coordsData"]["high"]);
		tables += "<td>" + coords + "</td>";
		for (i in transData[curID]["sumStatsData"]) {
			tables += "<td>" + transData[curID]["sumStatsData"][i] + "</td>";
		}
		tables += "</tr>";
	}
	$("#sumStatsTable").append(tables);		
	
	//Construct buttons to hide/show chart series
	buttonMake("All", "optionButtons", "transChart", false);
	buttonMake("None", "optionButtons", "transChart", false);
	buttonMake("Max", "optionButtons", "transChart", true);
	for (i in transIDs) {
		buttonMake(transIDs[i], "transButtons", "transChart", true);
	}
	
	
	addPanelTab("Resources", "resources");
	addPanelTab("Summary Statistics", "sumStats");
	addPanelTab("Transcript Data", "trans");
});

function addPanelTab(title, section) {
	var tabName = section + "Title";
	$("#selectBar").append("<div id = '" + tabName + "'>" + title + "</div>");
	$("#" + tabName).click(function() {
		$("#" + section).slideToggle("slow");
	});
}



//Changes the type of a chart, used in the menu below each chart
function changeType(chart, series, newType) {
	newType = newType.toLowerCase();
	for (var i = 0; i < series.length; i++) {
		serie = series[0];
		try {
			chart.addSeries({
				type: newType,
				id: serie.options.id,
				stack: serie.stack,
				yaxis: serie.yaxis,
				name: serie.name,
				color: serie.color,
				data: serie.options.data,
				visible: serie.visible
			},
			false);
			serie.remove();
		} catch(e) {
			alert(newType & ': ' & e);
		}
	}
	chart.redraw();
}

//Add a series to a chart
function addSeries(chart, transID) {
	chart.addSeries({
		id: transID + ' Long Poly A',	
		name: transID + ' Long Poly A',
		data: transData[transID]['polyData'],
	});
	chart.addSeries({
		id: transID + ' Long Non Poly A',
		name: transID + ' Long Non Poly A',
		data: transData[transID]['nonPolyData']
	});	
}

//Delete a series from a chart
function dropSeries(chart, transID) {
	var series = chart.series;
	for (var i = 0; i < series.length; i++) {
		serie = series[i];
		if (serie.name.indexOf(transID) !== -1) {
			serie.remove();
			i--;
		}
	}	
}

//Length function for counting element in multidimensional array
function multi_getLength(multiArr) {
	var c = 0;
	for(var i in multiArr) {
		c++;
	}
	return c;
}


</script>


</head>

<body>
<div id ="wrap">
	<div id = "header">
		<div id = "lnc">lncRNA</div>
	</div>
	<div id = "nav">
		<div><a href="http://bib.umassmed.edu/~wespisea/search.html">Home</a></div>
		<div><a href="http://bib.umassmed.edu/~wespisea/results.php">Back to Search</a></div>
                <div><a href="http://bib.umassmed.edu/~wespisea/references.html">References</a></div>
                <div><a href="http://bib.umassmed.edu/~wespisea/about.html">About</a></div>
	</div>
	<div id = "main">
		<div id = "content">	
		<h1>Gene ID: <?php echo $sKey;?></h1>
		Click to view:	
		<div id = "selectBar">
		</div>
		<div id = "resources" style = "display:none">
			<h2>Resources:</h2>
			<ul id = "resources">
			<li><a href= '<?php echo $ensemblLink?>'>Ensembl: Genome Database</a></li>
			</ul>
		</div>
		
		<div id = "sumStats">
			<h2>Summary Statistics:</h2>
			<table id = "sumStatsTable">
			</table>
			<h3>Entropy Data:</h3>
			<div id = "entropyChart" style = "width: 85%; height:400px"></div>
			<div style = "float:left; font-size:0.8em;">Change Chart Type:</div>
			<select id="chartType" onchange = 'changeType(entropyChart, entropyChart.series, this.value)'>
				<option value = "bar">bar</option>
				<option value = "line">line</option>
				<option value = "area">area</option>
				<option value = "scatter">scatter</option>
			</select>
		</div>

		<div id = "trans" style = "display:none">
			<h2>Transcript Data:</h2>
			<div id = "chartButtons">
				<div id = "optionButtons">Options:</br></div>
				<div id = "transButtons">Transcripts:</br></div>
			</div>
			<div id = "transChart" style = "width: 80%; height:400px;"></div>
			<div style = "float:left; font-size:0.8em;">Change Chart Type:</div>
			<select id="chartType" onchange = 'changeType(transChart, transChart.series, this.value)'>
				<option value = "line">line</option>
				<option value = "bar">bar</option>
				<option value = "area">area</option>
				<option value = "scatter">scatter</option>
			</select>
		</div>
		</div>
	</div>
	<div id = "footer">
	@2013 Zlab
	</div>
</div>
</body>
</html>	

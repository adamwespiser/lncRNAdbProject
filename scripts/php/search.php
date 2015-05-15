<?php

include 'database.php';

$geneID = $_POST["geneID"];

//Search bar autocomplete for gene ID
if (isset($geneID)) {
	$matches = array();
	
	if (!checkInput($geneID)) return;
	//Connect to database
	$db = new lncRNA_DB();
	if (!$db) {
		echo $db->lastErrorMsg();
	}
	$sql = "SELECT geneID FROM TransData WHERE geneID LIKE '" . $geneID . "%'";
	$ret = $db->query($sql);
        $row = $ret->fetchArray(SQLITE_ASSOC);

	//Get first 5 search results
	for($ctr = 0; $ctr < 5 && $row; $row = $ret->fetchArray(SQLITE_ASSOC)) {
		if (!in_array($row["geneID"], $matches))  $matches[$ctr++] = $row["geneID"];
	}
	$db->close();
	echo json_encode($matches);

}


$x = $_POST["x"];
$y = $_POST["y"];

//Axes change on PCA Data Chart
if (isset($x) && isset($y)) {
	//Connect to database
	$db = new lncRNA_DB();
	
	//Get only columns gene_id, x-axis, and y-axis
	$sql = "SELECT geneID, [" . $x . "], [" . $y . "] FROM PCAData";
	$data = $db->getAll($sql);

	$geneData;
	$ctr = 0;
	
	//Return all data point objects
	foreach ($data as $row) {
		$geneData[$ctr++] = (object) array("geneID"=> $row['geneID'], "x" => $row[$x], "y" =>$row[$y]);
	}
	
	echo json_encode($geneData);
}

?>


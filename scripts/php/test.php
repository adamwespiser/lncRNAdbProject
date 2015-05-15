<?php

include 'database.php';
/*
$t1 = "droptable";
$t2 = "ENSG00";
$t3 = "ENSG";
$t4 = "ENSG00000004142";
$t5 = "ENSGR00000004142.1223";

echo "Gene ID matches: <br>";
echo $t1 . ": " . checkInput("$t1") . "<br>";
echo $t2 . ": " . checkInput("$t2") . "<br>";
echo $t3 . ": " . checkInput("$t3") . "<br>";
echo $t4 . ": " . checkInput("$t4") . "<br>";
echo $t5 . ": " . checkInput("$t5") . "<br>";


$c1 = "chr1:13213-42";
$c2 = "chrY:42355-4215' Drop Table";
$c3 = "chrX";

echo "<br><br>Coordinate matches: <br>";
echo $c1 . ": " . checkInput($c1) . "<br>";
echo $c2 . ": " . checkInput($c2) . "<br>";
echo $c3 . ": " . checkInput($c3) . "<br>";
*/

$db = new lncRNA_DB();
$success = $db->loadExtension('/home/wespisea/databaseProject/install/sqlite/lib/functions.sqlext');
echo $success;
?>

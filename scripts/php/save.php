<?php
session_start();
if($_POST["session"] == "start") {
	$_SESSION["sKey"] = $_POST["sKey"];
	$_SESSION["loc"] = $_POST["loc"];
	echo $_SESSION["loc"];
}
elseif($_POST["session"] == "stop") {
	if (isset($_SESSION["loc"])) echo $_SESSION["loc"];
	else echo 0;
}
?>

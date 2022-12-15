<?php

require_once("./config.php");
require("DatabaseHandler.php");
require("Initializator.php");

$DBHandler = new DatabaseHandler($host, $db, $user, $password);
$init = new Initializator($DBHandler, $dbCount);

$init->initialize();
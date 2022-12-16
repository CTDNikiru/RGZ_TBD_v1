<?php

require_once("./config.php");
require("DatabaseHandler.php");
require("Initializator.php");
require("DatabaseWorker.php");

$DBHandler = new DatabaseHandler($host, $db, $user, $password);
$init = new Initializator($DBHandler, $dbCount);
$worker = new DatabaseWorker($DBHandler);

$init->initialize();

for($i = 0; $i < 20; $i++){
    $worker->work($dbCount);
}


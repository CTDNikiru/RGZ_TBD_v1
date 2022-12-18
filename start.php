<?php

require_once("./config.php");
require("DatabaseHandler.php");
require("Initializator.php");
require("DatabaseWorker.php");
require("Replicator.php");

$DBHandler = new DatabaseHandler($host, $db, $user, $password);
$init = new Initializator($DBHandler, $dbCount);
$worker = new DatabaseWorker($DBHandler);

$init->initialize();

for($k = 0; $k < 3; $k++){
    for($i = 0; $i < $workCount; $i++){
        $worker->work($dbCount);
        sleep(1);
    }

    $replicator = new Replicator($DBHandler, $dbCount);
    $filialReplicator = new Replicator($DBHandler, $dbCount);

    for($i = 1; $i < $dbCount; $i++){
        $replicator->replicate(0, $i);
    }

    for($i = 1; $i < $dbCount; $i++){
        $filialReplicator->replicate($i, 0);
    }
}


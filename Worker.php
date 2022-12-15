<?php

class Worker
{
    private DatabaseHandler $databaseHandler;
    private int $tableCount;

    public function __construct(DatabaseHandler $databaseHandler, int $tableCount)
    {
        $this->databaseHandler = $databaseHandler;
        $this->tableCount = $tableCount;
        $this->work($tableCount);
    }

    public function work(int $n)
    {
        //0 - INSERT
        //1 - UPDATE
        //2 - DELETE
        //log: dbID operation time command

        $action = rand(0, 2);
        $tableId = rand(0, $n);


    }

    private function actionInsert($tableId)
    {

    }

}
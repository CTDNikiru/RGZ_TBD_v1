<?php

require_once 'vendor/autoload.php';

class DatabaseWorker
{
    private DatabaseHandler $databaseHandler;

    public function __construct(DatabaseHandler $databaseHandler)
    {
        $this->databaseHandler = $databaseHandler;
    }

    public function work(int $n)
    {
        //0 - INSERT
        //1 - UPDATE
        //2 - DELETE
        //log: dbID operation time command
        $action = rand(0, 2);
        //Нулевая - центральная БД не изменяется
        $tableId = rand(0, $n - 1);
        $operation = ["INSERT", "UPDATE", "DELETE"];

        switch ($action) {
            case 0:
                $this->actionInsert($tableId);
                break;
            case 1:
                $this->actionUpdate($tableId);
                break;
            case 2:
                $this->actionDelete($tableId);
                break;
        }
    }

    private function actionUpdate($tableId)
    {
        $minOid = $this->getMinOid($tableId);

        $sql = "select * from db_table_" . $tableId . " where oid = $minOid";
        $pastInfo = $this->databaseHandler->executeQuery($sql);


        $faker = Faker\Factory::create();
        $name = $faker->firstName();
        $phone = $faker->e164PhoneNumber();
        $email = $faker->email();
        $updated = "NOW()";
        $operation = "update";

        $sql = "update db_table_" . $tableId . " set
            name='$name',
            phone='$phone',
            email='$email',
            updated=$updated,
            operation='$operation'
            where oid=$minOid
        ";
        $arrToSave = [
            "pastName" => $pastInfo["name"],
            "pastPhone" => $pastInfo["phone"],
            "pastEmail" => $pastInfo["email"],
            "name" => $name,
            "phone" => $phone,
            "email" => $email
        ];
        $log = $tableId . "|" . "update" . "|" . date_create_immutable()->format("Y-m-d H:i:s") . "|" . json_encode($arrToSave) . "\n";
        file_put_contents("log.txt", $log, FILE_APPEND);
        return $this->databaseHandler->executeTransaction([$sql], [[]]);
    }

    private function actionDelete($tableId)
    {
        $maxOid = $this->getMaxOid($tableId);
        $sql = "select * from db_table_" . $tableId . " where oid=$maxOid";
        $info = $this->databaseHandler->executeQuery($sql, []);
        $sql = "delete from db_table_" . $tableId . " where oid=$maxOid";
        $log = $tableId . "|" . "delete" . "|" . date_create_immutable()->format("Y-m-d H:i:s") . "|" . json_encode($info) . "\n";
        file_put_contents("log.txt", $log, FILE_APPEND);
        return $this->databaseHandler->executeTransaction([$sql], [[]]);
    }

    private function actionInsert($tableId)
    {
        $faker = Faker\Factory::create();
        $name = $faker->firstName();
        $phone = $faker->e164PhoneNumber();
        $email = $faker->email();
        $updated = "NOW()";
        $operation = "insert";

        $sql = "insert into db_table_" . $tableId . "(name, phone, email, updated, operation) 
        values ('$name', '$phone', '$email', $updated, '$operation')";
        $arrToSave = ["name" => $name, "phone" => $phone, "email" => $email];
        $log = $tableId . "|" . "insert" . "|" . date_create_immutable()->format("Y-m-d H:i:s") . "|" . json_encode($arrToSave) . "\n";
        file_put_contents("log.txt", $log, FILE_APPEND);

        return $this->databaseHandler->executeTransaction([$sql], [[]]);
    }

    private function getMinOid($tableId)
    {
        $sql = "SELECT MIN(oid) from db_table_" . $tableId;
        $result = $this->databaseHandler->executeQuery($sql);
        if (!isset($result) || count($result) == 0) {
            throw new \Exception("Нет записей в таблице");
        }
        return $result['min'];
    }

    private function getMaxOid($tableId)
    {
        $sql = "SELECT MAX(oid) from db_table_" . $tableId;
        $result = $this->databaseHandler->executeQuery($sql);
        if (!isset($result) || count($result) == 0) {
            throw new \Exception("Нет записей в таблице");
        }
        return $result['max'];
    }
}
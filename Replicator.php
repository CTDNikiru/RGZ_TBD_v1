<?php

class Replicator
{
    private DatabaseHandler $databaseHandler;
    private int $tableCount;
    private array $alreadyDeleted = [];
    private array $alreadyInserted = [];
    private array $alreadyUpdated = [];

    public function __construct(DatabaseHandler $databaseHandler, int $tableCount)
    {
        $this->databaseHandler = $databaseHandler;
        $this->tableCount = $tableCount;
    }

    function replicate($center, $filial)
    {
        $data = file_get_contents("log.txt");
        $data = substr($data, 0, -1);
        $data = explode("\n", $data);

        $data = $this->getArray($data);

        $log_1 = $this->getLogsDb($data, $filial);
        $this->executeInsert($this->getOperationFromLogs($log_1, "insert"), $center);
        $this->executeDelete($this->getOperationFromLogs($log_1, "delete"), $center);
        $this->executeUpdate($this->getOperationFromLogs($log_1, "update"), $center);
    }

    private function getOperationFromLogs($logs, $operation)
    {
        $result = [];
        foreach ($logs as $log) {
            if ($log["operation"] == $operation) {
                $result[] = $log;
            }
        }
        return $result;
    }

    private function executeUpdate($logs, $dbId)
    {
        $commands = [];
        foreach ($logs as $log) {
            $minOid = $this->getMinOid($dbId);
            $sql = "select * from db_table_" . $dbId . " where oid = $minOid";

            $datetime_saved = $this->databaseHandler->executeQuery($sql);
            $datetime_saved = date_create_immutable($datetime_saved["updated"])->format("Y-m-d H:i:s:v");

            $datetime = date("Y-m-d H:i:s:v", (int)$log["time"]);
            if ($datetime_saved < $datetime) {
                $dataLog = json_decode($log["data"], true);
                $name = $dataLog["name"];
                $phone = $dataLog["phone"];
                $email = $dataLog["email"];

                $pastName = $dataLog["pastName"];
                $pastPhone = $dataLog["pastPhone"];
                $pastEmail = $dataLog["pastEmail"];

                $updated = date("Y-m-d\TH:i:s", (int)$log["time"]);
                $operation = 'replication';

                $sql = "update db_table_" . $dbId . " set
                name='$name',
                phone='$phone',
                email='$email',
                updated='$updated',
                operation='$operation'
                where oid = $minOid";
                $commands[] = $sql;

                $arrToSave = [
                    "pastName" => $pastName,
                    "pastPhone" => $pastPhone,
                    "pastEmail" => $pastEmail,
                    "name" => $name,
                    "phone" => $phone,
                    "email" => $email
                ];
                $log = $dbId . "|" . "update" . "|" . date_create_immutable()->format("Y-m-d H:i:s") . "|" . json_encode($arrToSave) . "\n";
                file_put_contents("log.txt", $log, FILE_APPEND);
            }
            $this->databaseHandler->executeTransaction($commands, []);
        }
    }

    private function executeInsert($logs, $dbId)
    {
        $commands = [];
        foreach ($logs as $log) {
            $dataLog = json_decode($log["data"], true);
            $name = $dataLog["name"];
            $phone = $dataLog["phone"];
            $email = $dataLog["email"];
            $check = $this->checkIfExist($dbId, $name, $phone);
            if (!$check) {
                $updated = "NOW()";
                $operation = "replication";
                $sql = "insert into db_table_" . $dbId . " (name, phone, email, updated, operation) 
                values ('$name', '$phone', '$email', $updated, '$operation')";
                $commands[] = $sql;
                $arrToSave = ["name" => $name, "phone" => $phone, "email" => $email];
                $log = $dbId . "|" . "insert" . "|" . date_create_immutable()->format("Y-m-d H:i:s") . "|" . json_encode($arrToSave) . "\n";
                file_put_contents("log.txt", $log, FILE_APPEND);
            }
        }
        $this->databaseHandler->executeTransaction($commands, []);
    }

    private function executeDelete($logs, $dbId)
    {
        $commands = [];
        foreach ($logs as $log) {
            $dataLog = json_decode($log["data"], true);
            $name = $dataLog["name"];
            $phone = $dataLog["phone"];
            $check = $this->checkIfExist($dbId, $name, $phone);
            if ($check) {
                $sql = "delete from db_table_" . $dbId . " where name='" . $name . "' and phone='" . $phone . "'";
                $commands[] = $sql;
                $log = $dbId . "|" . "delete" . "|" . date_create_immutable()->format("Y-m-d H:i:s") . "|" . json_encode($dataLog) . "\n";
                file_put_contents("log.txt", $log, FILE_APPEND);
            }
        }
        $this->databaseHandler->executeTransaction($commands, []);
    }

    private function checkIfExist($dbId, $name, $phone)
    {
        $sql = "select * from db_table_" . $dbId . " where name='$name' and phone='$phone'";
        $res = $this->databaseHandler->executeQuery($sql);
        return !empty($res);
    }

    public function getArray($data)
    {
        $dataArr = [];

        foreach ($data as $log) {
            $arr = explode("|", $log);
            $dataArr[] = [
                "dbId" => $arr[0],
                "operation" => $arr[1],
                "time" => strtotime($arr[2]),
                "data" => $arr[3] ?? null
            ];
        }
        return $dataArr;
    }

    public function getLogsDb($data, $tableId)
    {
        $result = [];
        foreach ($data as $log) {
            if ($log["dbId"] == $tableId) {
                $result[] = $log;
            }
        }
        return $result;
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
}
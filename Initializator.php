<?php

require_once 'vendor/autoload.php';

class Initializator
{
    private DatabaseHandler $databaseHandler;
    private int $tableCount;

    public function __construct(DatabaseHandler $databaseHandler, int $tableCount)
    {
        $this->databaseHandler = $databaseHandler;
        $this->tableCount = $tableCount;
    }

    //Инициализирует чистую базу данных
    public function initialize()
    {
        $this->deleteTables($this->tableCount);
        $this->createTables($this->tableCount);
        $this->insertValues($this->tableCount);
    }

    private function deleteTables(int $n)
    {
        for ($i = 0; $i < $n; $i++) {
            $sql = "drop table if exists db_table_" . $i;
            $this->databaseHandler->executeQuery($sql);
        }

        echo "Удалено таблиц: " . $n . "\n";
    }

    private function createTables(int $n)
    {
        for ($i = 0; $i < $n; $i++) {
            $sql = "create table db_table_" . $i . "(id serial PRIMARY KEY, name varchar(255), phone varchar(12), email varchar(255), updated timestamp, operation varchar(255))";
            $this->databaseHandler->executeQuery($sql);
        }
        echo "Создано таблиц: " . $n . "\n";
    }

    private function insertValues(int $n)
    {
        $faker = Faker\Factory::create();
        $values = "";
        for ($i = 0; $i < 10; $i++) {
            $name = $faker->firstName();
            $phone = $faker->e164PhoneNumber();
            $email = $faker->email();
            $updated = "NOW()";
            $operation = "initial";
            $values = $values . "('$name', '$phone', '$email', $updated, '$operation'),";
        }

        $values = substr($values, 0, -1);

        for ($j = 0; $j < $n; $j++) {
            $sql = "insert into db_table_" . $j . " (name, phone, email, updated, operation) values" . $values;
            $this->databaseHandler->executeQuery($sql);
        }
    }
}
<?php

class DatabaseHandler
{
    private PDO $pdo;

    public function __construct(string $host, string $db, string $user, string $password)
    {
        $this->pdo = $this->connect($host, $db, $user, $password);
    }

    private function connect(string $host, string $db, string $user, string $password): PDO
    {
        try {
            $dsn = "pgsql:host=$host;port=5432;dbname=$db;";

            echo "connection success\n";

            return new PDO(
                $dsn,
                $user,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    //массив команд и массив массивов значений
    public function executeTransaction(array $sqlCommands, array $values)
    {
        try {
            $this->pdo->beginTransaction();

            for ($i = 0; $i < count($sqlCommands); $i++) {
                $this->pdo->prepare($sqlCommands[$i])->execute($values[$i] ?? null);
            }

            $this->pdo->commit();

            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function executeQuery(string $sql, array $value = []){
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute($value);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
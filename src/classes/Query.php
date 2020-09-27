<?php

class Query {

    protected $pdo;

    //init&create tables if they don't exist yet
    public function __construct(PDO $pdo) {

        $this->pdo = $pdo;
        try {
        $statement = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS users 
        ( id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, online INTEGER NOT NULL )");

        $statement->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        try {
        $statement = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS messages (id integer PRIMARY KEY AUTOINCREMENT,
        author text NOT NULL, receiver text NOT NULL, message text NOT NULL);");

        $statement->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    
    public function selectAll($table) {

        $statement = $this->pdo->prepare("select * from $table");

        $statement->execute();
    
        return json_encode($statement->fetchAll(PDO::FETCH_CLASS));

    }


    //pick username and appear as being online
    public function setUser($table, $name) {

        $statement = $this->pdo->prepare("select * from $table where name = '$name'");

        $statement->execute();

        if(empty($statement->fetchAll(PDO::FETCH_CLASS))) {
                $this->insert($table, [
                    'name'=>$name,
                    'online'=>1
                    
                    ]);
        }
        else {
            try {
            $statement = $this->pdo->prepare("update $table set online = 1 where name = '$name'");

            $statement->execute();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

    }


    public function logOff($table, $name) {

        $sql = "update $table set online = 0";
        if($name!='*') {
            $sql = $sql . " where name = '$name'";
        }

        try {
        $statement = $this->pdo->prepare($sql);

        $statement->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    public function insert($table, $parameters) {

        $sql = sprintf(

            'insert into %s (%s) values (%s)',
            $table,
            implode(', ', array_keys($parameters)),
            ':' . implode(', :', array_keys($parameters))
        );

        try {
            $statement = $this->pdo->prepare($sql);

            $statement->execute($parameters);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
}
<?php

class DBO
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }


    public function fetchAll($sql)
    {
        return $this->pdo->query($sql)->fetchAll();
    }

    public function fetch($sql)
    {
        $result = $this->pdo->query($sql);
        return $result->fetch();
    }

    public function insert($table, $data)
    {
        $fields = "";
        $values = "";

        foreach ($data as $key => $value) {
            $fields .= $key . ", ";          // Nối tên cột: "name, price, "
            $values .= "'" . $value . "', "; // Nối giá trị: "'iPhone', '1000', "
        }

        // Xóa dấu phẩy thừa ở cuối
        $fields = rtrim($fields, ", ");
        $values = rtrim($values, ", ");

        $sql = "INSERT INTO $table ($fields) VALUES ($values)";
        return $this->pdo->exec($sql);
    }


    public function update($table, $data, $where)
    {
        $sets = "";

        foreach ($data as $key => $value) {

            $sets .= $key . " = '" . $value . "', ";
        }


        $sets = rtrim($sets, ", ");

        $sql = "UPDATE $table SET $sets WHERE $where";
        return $this->pdo->exec($sql);
    }


    public function delete($table, $where)
    {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->pdo->exec($sql);
    }

    public function getLastId()
    {
        return $this->pdo->lastInsertId();
    }
}
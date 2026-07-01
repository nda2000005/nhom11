<?php
// core/Database.php
class Database {
    protected $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lấy PDO connection (nếu cần dùng trực tiếp)
    public function getConnection() {
        return $this->pdo;
    }

    // =========================
    // FETCH
    // =========================

    // Lấy nhiều dòng
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lấy 1 dòng
    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // =========================
    // QUERY (FIX LỖI CỦA BẠN)
    // =========================

    // Dùng cho INSERT / UPDATE / DELETE viết tay
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // =========================
    // INSERT CHUNG
    // =========================
    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = implode(", ", $keys);
        $placeholders = ":" . implode(", :", $keys);

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // =========================
    // UPDATE CHUNG
    // =========================
    public function update($table, $data, $whereCondition) {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }

        $sql = "UPDATE $table SET " . implode(", ", $fields) . " WHERE $whereCondition";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // =========================
    // DELETE CHUNG
    // =========================
    public function delete($table, $whereCondition, $params = []) {
        $sql = "DELETE FROM $table WHERE $whereCondition";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

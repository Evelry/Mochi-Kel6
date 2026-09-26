<?php
class Database {
    private string $host = '127.0.0.1';
    private string $db   = 'db_mochi_erp';
    private string $user = 'root';
    private string $pass = '';
    private ?PDO $pdo = null;

    public function connect(): PDO {
        if ($this->pdo) return $this->pdo;
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $this->pdo;
    }
}

<?php
/**
 * Database Connection Manager
 * Singleton pattern with PDO
 */

namespace Core\Database;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(array $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
            
            // Set additional PDO attributes
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // Set timezone
            $timezone = config('timezone') ?: 'UTC';
            $this->connection->exec("SET time_zone = '{$timezone}'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            if (config('logging.level') === 'debug') {
                throw $e;
            }
            
            http_response_code(500);
            die('Database connection failed. Please check configuration.');
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute a query and return results
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logger('error', 'Query failed: ' . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Execute a query and return single row
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            logger('error', 'Query failed: ' . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Insert record and return ID
     */
    public function insert(string $table, array $data): int
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->config['prefix']}{$table} ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($data);
            
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            logger('error', 'Insert failed: ' . $e->getMessage(), ['table' => $table]);
            throw $e;
        }
    }
    
    /**
     * Update records
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        try {
            $setParts = [];
            foreach (array_keys($data) as $column) {
                $setParts[] = "{$column} = :{$column}";
            }
            $setClause = implode(', ', $setParts);
            
            $sql = "UPDATE {$this->config['prefix']}{$table} SET {$setClause} WHERE {$where}";
            
            $stmt = $this->connection->prepare($sql);
            $params = array_merge($data, $whereParams);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            logger('error', 'Update failed: ' . $e->getMessage(), ['table' => $table]);
            throw $e;
        }
    }
    
    /**
     * Delete records
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        try {
            $sql = "DELETE FROM {$this->config['prefix']}{$table} WHERE {$where}";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            logger('error', 'Delete failed: ' . $e->getMessage(), ['table' => $table]);
            throw $e;
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Execute within transaction
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get table prefix
     */
    public function getPrefix(): string
    {
        return $this->config['prefix'];
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

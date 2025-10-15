<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Base Model
 * Abstract base class for all models with common CRUD operations
 */
abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Find record by column value
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        
        $result = $stmt->fetch();
        return $result ? $this->transformResult($result) : null;
    }

    /**
     * Get all records
     */
    public function all(array $conditions = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $sql .= " AND {$column} = ?";
                $params[] = $value;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll();
        return array_map(fn($row) => $this->transformResult($row), $results);
    }

    /**
     * Get paginated records
     */
    public function paginate(int $page = 1, int $perPage = 15, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $condition = " AND {$column} = ?";
                $sql .= $condition;
                $countSql .= $condition;
                $params[] = $value;
            }
        }

        // Get total count
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['total'];

        // Get paginated data
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll();
        $items = array_map(fn($row) => $this->transformResult($row), $results);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Create new record
     */
    public function create(array $data): ?int
    {
        $data = $this->filterFillable($data);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute(array_values($data));

        return $success ? (int) $this->db->lastInsertId() : null;
    }

    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $setParts = array_map(fn($col) => "{$col} = ?", array_keys($data));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE {$this->primaryKey} = ?",
            $this->table,
            implode(', ', $setParts)
        );

        $params = array_values($data);
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete record
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = ? WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }

    /**
     * Hard delete record
     */
    public function forceDelete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch() !== false;
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $sql .= " AND {$column} = ?";
                $params[] = $value;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Filter data to only fillable columns
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Transform result (hide fields, cast types)
     */
    protected function transformResult(array $data): array
    {
        // Hide fields
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }

        // Cast types
        foreach ($this->casts as $field => $type) {
            if (!isset($data[$field])) {
                continue;
            }

            $data[$field] = match ($type) {
                'int', 'integer' => (int) $data[$field],
                'float', 'double' => (float) $data[$field],
                'bool', 'boolean' => (bool) $data[$field],
                'string' => (string) $data[$field],
                'array', 'json' => json_decode($data[$field], true),
                'datetime' => $data[$field],
                default => $data[$field]
            };
        }

        return $data;
    }

    /**
     * Execute raw query
     */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->db->rollBack();
    }
}

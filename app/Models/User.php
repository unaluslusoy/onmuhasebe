<?php

namespace App\Models;

/**
 * User Model
 * Handles user authentication and management
 */
class User extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'email',
        'password',
        'full_name',
        'phone',
        'avatar',
        'role',
        'company_id',
        'is_active',
        'email_verified_at'
    ];

    protected array $hidden = [
        'password'
    ];

    protected array $casts = [
        'id' => 'int',
        'is_active' => 'bool'
    ];

    /**
     * Create new user with hashed password
     */
    public function createUser(array $data): ?int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        return $this->create($data);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Verify user password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Update last login timestamp and IP
     */
    public function updateLastLogin(int $userId, string $ip): bool
    {
        $sql = "UPDATE {$this->table} SET last_login_at = ?, last_login_ip = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([date('Y-m-d H:i:s'), $ip, $userId]);
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->update($userId, ['password' => $hashedPassword]);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE email = ? AND deleted_at IS NULL";
        $params = [$email];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Verify email address
     */
    public function verifyEmail(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET email_verified_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([date('Y-m-d H:i:s'), $userId]);
    }

    /**
     * Get user with unhidden password (for authentication)
     */
    public function findForAuth(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Activate/deactivate user
     */
    public function setActive(int $userId, bool $isActive): bool
    {
        return $this->update($userId, ['is_active' => $isActive]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        return $this->all(['role' => $role]);
    }

    /**
     * Count users by role
     */
    public function countByRole(string $role): int
    {
        return $this->count(['role' => $role]);
    }

    /**
     * Update user's company_id
     *
     * @param int $userId User ID
     * @param int $companyId Company ID
     * @return bool Success status
     */
    public function updateCompanyId(int $userId, int $companyId): bool
    {
        $sql = "UPDATE users SET company_id = :company_id WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':company_id' => $companyId,
                ':id' => $userId
            ]);
        } catch (\PDOException $e) {
            logger('Update user company_id failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}

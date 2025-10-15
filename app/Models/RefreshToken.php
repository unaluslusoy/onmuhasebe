<?php

namespace App\Models;

/**
 * RefreshToken Model
 * Manages JWT refresh tokens
 */
class RefreshToken extends BaseModel
{
    protected string $table = 'refresh_tokens';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'user_id',
        'token',
        'expires_at'
    ];

    protected array $casts = [
        'id' => 'int',
        'user_id' => 'int'
    ];

    /**
     * Store refresh token
     */
    public function store(int $userId, string $token, int $expiresIn): ?int
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        return $this->create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Find token with user validation
     */
    public function findValidToken(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE token = ? 
                AND expires_at > NOW() 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        
        return $stmt->fetch() ?: null;
    }

    /**
     * Revoke token
     */
    public function revoke(string $token): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token]);
    }

    /**
     * Revoke all user tokens
     */
    public function revokeAllUserTokens(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Clean expired tokens
     */
    public function cleanExpired(): int
    {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Count user tokens
     */
    public function countUserTokens(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }
}

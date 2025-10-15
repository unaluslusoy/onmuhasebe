-- Add avatar column to users table
-- Created: 2025-10-07

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER email;

-- Add index for avatar lookups (if not exists)
CREATE INDEX IF NOT EXISTS idx_users_avatar ON users(avatar);

COMMIT;

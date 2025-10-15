-- ============================================
-- Migration: 010_add_company_images
-- Description: Add company logo, stamp, and signature columns
-- Created: 2025-10-07
-- ============================================

-- Add company image columns if they don't exist
ALTER TABLE companies 
ADD COLUMN IF NOT EXISTS company_logo VARCHAR(255) NULL COMMENT 'Şirket logosu dosya adı' AFTER website,
ADD COLUMN IF NOT EXISTS company_stamp VARCHAR(255) NULL COMMENT 'Şirket kaşesi dosya adı' AFTER company_logo,
ADD COLUMN IF NOT EXISTS company_signature VARCHAR(255) NULL COMMENT 'Şirket imza dosya adı' AFTER company_stamp;

-- Create indexes for image columns
CREATE INDEX IF NOT EXISTS idx_company_logo ON companies(company_logo);
CREATE INDEX IF NOT EXISTS idx_company_stamp ON companies(company_stamp);
CREATE INDEX IF NOT EXISTS idx_company_signature ON companies(company_signature);

-- Migration: Add company business information fields
-- Date: 2025-10-07
-- Description: Add logo, signature, document type, sector and financial fields to companies table

USE onmuhasebe;

-- Add business information columns
ALTER TABLE companies 
ADD COLUMN IF NOT EXISTS company_logo VARCHAR(255) NULL COMMENT 'Company logo filename',
ADD COLUMN IF NOT EXISTS company_signature VARCHAR(255) NULL COMMENT 'Digital signature filename',
ADD COLUMN IF NOT EXISTS document_type ENUM('invoice', 'waybill', 'receipt', 'other') DEFAULT 'invoice' COMMENT 'Default document type',
ADD COLUMN IF NOT EXISTS sector VARCHAR(100) NULL COMMENT 'Business sector',
ADD COLUMN IF NOT EXISTS annual_revenue DECIMAL(15,2) NULL COMMENT 'Annual revenue',
ADD COLUMN IF NOT EXISTS employee_count INT NULL COMMENT 'Number of employees',
ADD COLUMN IF NOT EXISTS foundation_year YEAR NULL COMMENT 'Company foundation year',
ADD COLUMN IF NOT EXISTS business_description TEXT NULL COMMENT 'Business description';

-- Create index for sector searches
CREATE INDEX IF NOT EXISTS idx_companies_sector ON companies(sector);
CREATE INDEX IF NOT EXISTS idx_companies_document_type ON companies(document_type);

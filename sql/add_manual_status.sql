-- Add manual_status column to elections table
ALTER TABLE elections ADD COLUMN manual_status TINYINT(1) DEFAULT 0;

-- Set manual_status to 1 for all existing elections
UPDATE elections SET manual_status = 1;

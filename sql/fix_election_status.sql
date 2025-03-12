-- Check if status column exists and modify it
SET @exist := (SELECT COUNT(*) 
               FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'elections' 
               AND COLUMN_NAME = 'status');

SET @query = IF(@exist > 0,
    'ALTER TABLE elections MODIFY COLUMN status ENUM("upcoming", "active", "ended") NOT NULL DEFAULT "upcoming"',
    'SELECT "Status column does not exist"');

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing elections based on their dates
UPDATE elections 
SET status = 
    CASE 
        WHEN NOW() < start_date THEN 'upcoming'
        WHEN NOW() BETWEEN start_date AND end_date THEN 'active'
        ELSE 'ended'
    END;

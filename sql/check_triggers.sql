-- Show triggers affecting the elections table
SHOW TRIGGERS WHERE `table` = 'elections';

-- Show create trigger statements
SELECT TRIGGER_NAME, ACTION_STATEMENT, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING
FROM information_schema.TRIGGERS 
WHERE EVENT_OBJECT_TABLE = 'elections';

-- Show the status column definition
SHOW COLUMNS FROM elections WHERE Field = 'status';

-- Check if there are any foreign key constraints
SELECT 
    TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    REFERENCED_TABLE_NAME = 'elections';

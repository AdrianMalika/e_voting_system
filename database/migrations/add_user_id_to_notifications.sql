USE e_voting_system;

-- Drop existing indexes and foreign keys
ALTER TABLE notifications DROP INDEX type;
ALTER TABLE notifications DROP INDEX is_read;

-- Modify the type ENUM to include new types
ALTER TABLE notifications MODIFY COLUMN type ENUM('new_candidate', 'election_request', 'new_vote', 'election_ended', 'election_started', 'new_election') NOT NULL;

-- Add user_id column
ALTER TABLE notifications ADD COLUMN user_id INT;

-- Add foreign key constraint
ALTER TABLE notifications ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Recreate indexes
ALTER TABLE notifications ADD INDEX (type);
ALTER TABLE notifications ADD INDEX (is_read);
ALTER TABLE notifications ADD INDEX (user_id);

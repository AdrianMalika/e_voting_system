USE e_voting_system;

-- Clear all existing notifications
TRUNCATE TABLE notifications;

-- Reset auto increment
ALTER TABLE notifications AUTO_INCREMENT = 1;

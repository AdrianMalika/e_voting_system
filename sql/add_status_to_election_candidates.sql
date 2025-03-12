-- Add status column to election_candidates table
ALTER TABLE election_candidates 
ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending';

-- Update any existing records to 'approved' status
UPDATE election_candidates SET status = 'approved' WHERE status = 'pending';

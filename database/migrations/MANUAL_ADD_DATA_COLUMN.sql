-- Manual SQL Migration for Notifications
-- Run this SQL directly in your database (phpMyAdmin, MySQL Workbench, or Docker exec)

-- CRITICAL FIX: Update the type enum to include 'Priest Declined'
-- This is the main issue preventing notifications from being created!
ALTER TABLE notifications
MODIFY COLUMN `type` ENUM(
    'Approval',
    'Reminder',
    'System Alert',
    'Priest Declined',
    'Assignment',
    'Update'
) NULL;

-- Optional: Add the data column if it doesn't exist (for enhanced features)
ALTER TABLE notifications
ADD COLUMN IF NOT EXISTS `data` JSON NULL AFTER `type`;

-- Verify the changes
DESCRIBE notifications;

-- Check if there are any notifications
SELECT COUNT(*) as total_notifications FROM notifications;

-- Check priest declined notifications
SELECT * FROM notifications WHERE type = 'Priest Declined' ORDER BY sent_at DESC LIMIT 5;

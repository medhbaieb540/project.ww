-- ============================================
-- GAMEBRIDGE FEEDBACK SYSTEM - CORRECTED SCHEMA
-- ============================================
-- This schema matches your actual PHP implementation
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS gamebridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gamebridge;

-- Main Feedback Table (simplified version matching your PHP code)
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `game` VARCHAR(100) NOT NULL COMMENT 'Game name',
  `type` ENUM('feedback', 'report') NOT NULL DEFAULT 'feedback',
  `message` TEXT NOT NULL,
  `author` VARCHAR(50) NOT NULL COMMENT 'Username of submitter',
  `status` ENUM('pending', 'reviewed', 'fixed') NOT NULL DEFAULT 'pending',
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_game` (`game`),
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Replies Table (matching your PHP code)
CREATE TABLE IF NOT EXISTS `replies` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `feedback_id` INT(11) UNSIGNED NOT NULL,
  `author` VARCHAR(50) NOT NULL COMMENT 'Username of person replying',
  `message` TEXT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_feedback_id` (`feedback_id`),
  INDEX `idx_date` (`date`),
  CONSTRAINT `fk_reply_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA FOR TESTING
-- ============================================

INSERT INTO feedback (game, type, message, author, status, date) VALUES
('Neon Runner', 'report', 'The game crashes after level 3 boss fight. Please fix this issue soon!', 'TestUser', 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Shadow Quest', 'feedback', 'Amazing game! The graphics are stunning and gameplay is smooth.', 'PlayerOne', 'reviewed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Cyber Arena', 'report', 'Lag issues in multiplayer mode. Ping spikes above 200ms frequently.', 'GamerPro', 'reviewed', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Space Odyssey', 'feedback', 'Love the soundtrack and atmosphere. Could use more save points though.', 'StarGazer', 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Dragon Tales', 'report', 'Character model glitches when equipping legendary armor.', 'DragonSlayer', 'fixed', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('Pixel Warriors', 'feedback', 'Great retro style! Reminds me of classic arcade games.', 'RetroFan', 'pending', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Sample replies
INSERT INTO replies (feedback_id, author, message, date) VALUES
(1, 'DevTeam', 'Thanks for reporting! We are investigating this crash issue.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'DevTeam', 'We have identified the network issue and deploying a fix soon.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(5, 'DevTeam', 'This has been fixed in version 1.2.3. Please update your game!', DATE_SUB(NOW(), INTERVAL 9 DAY)),
(5, 'DragonSlayer', 'Confirmed! The update fixed it. Thanks!', DATE_SUB(NOW(), INTERVAL 8 DAY));

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check feedback count
SELECT COUNT(*) as total_feedback FROM feedback;

-- Check replies count
SELECT COUNT(*) as total_replies FROM replies;

-- View all feedback with reply counts
SELECT 
    f.id,
    f.game,
    f.type,
    f.status,
    f.author,
    f.date,
    COUNT(r.id) as reply_count
FROM feedback f
LEFT JOIN replies r ON f.id = r.feedback_id
GROUP BY f.id
ORDER BY f.date DESC;

-- ============================================
-- SUCCESS MESSAGE
-- ============================================
SELECT '✅ Database setup completed successfully!' as status;

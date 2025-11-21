-- ============================================
-- GAMEBRIDGE FEEDBACK SYSTEM DATABASE SCHEMA
-- ============================================

-- Main Feedback Table
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_id` INT(11) UNSIGNED NOT NULL,
  `player_id` INT(11) UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `rating` TINYINT(1) UNSIGNED DEFAULT NULL COMMENT 'Rating 1-5 stars',
  `status` ENUM('pending', 'reviewed', 'fixed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_game_id` (`game_id`),
  INDEX `idx_player_id` (`player_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_feedback_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feedback_player` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback Replies Table (for developer responses)
CREATE TABLE IF NOT EXISTS `feedback_replies` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `feedback_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL COMMENT 'Developer or Admin who replied',
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_feedback_id` (`feedback_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_reply_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reply_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback History Table (tracks status changes)
CREATE TABLE IF NOT EXISTS `feedback_history` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `feedback_id` INT(11) UNSIGNED NOT NULL,
  `changed_by` INT(11) UNSIGNED NOT NULL COMMENT 'User who made the change',
  `old_status` ENUM('pending', 'reviewed', 'fixed') DEFAULT NULL,
  `new_status` ENUM('pending', 'reviewed', 'fixed') NOT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_feedback_id` (`feedback_id`),
  INDEX `idx_changed_by` (`changed_by`),
  INDEX `idx_changed_at` (`changed_at`),
  CONSTRAINT `fk_history_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback Archive Table (for deleted feedback)
CREATE TABLE IF NOT EXISTS `feedback_archive` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `feedback_id` INT(11) UNSIGNED NOT NULL,
  `game_id` INT(11) UNSIGNED NOT NULL,
  `player_id` INT(11) UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `rating` TINYINT(1) UNSIGNED DEFAULT NULL,
  `status` ENUM('pending', 'reviewed', 'fixed') NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `deleted_by` INT(11) UNSIGNED NOT NULL,
  `deleted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_feedback_id` (`feedback_id`),
  INDEX `idx_game_id` (`game_id`),
  INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table (if not exists)
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'feedback, feedback_reply, feedback_resolved, etc',
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Games Table Reference (assumed to exist)
-- Ensure your games table has these columns:
-- id, title, developer_id, status, etc.

-- Users Table Reference (assumed to exist)
-- Ensure your users table has these columns:
-- id, username, role (player/developer/admin), etc.

-- ============================================
-- SAMPLE DATA FOR TESTING
-- ============================================

-- Insert sample feedback (adjust IDs based on your existing data)
-- INSERT INTO feedback (game_id, player_id, message, rating, status, created_at) VALUES
-- (1, 2, 'The game crashes after level 3 boss fight. Please fix soon!', 4, 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY)),
-- (2, 3, 'Some levels load too slow on mobile.', 3, 'reviewed', DATE_SUB(NOW(), INTERVAL 7 DAY)),
-- (3, 2, 'Amazing atmosphere! Just fix the lag in level 5.', 5, 'fixed', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- ============================================
-- USEFUL QUERIES FOR ANALYTICS
-- ============================================

-- Get feedback statistics for a developer
-- SELECT 
--   COUNT(*) as total_feedback,
--   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
--   SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
--   SUM(CASE WHEN status = 'fixed' THEN 1 ELSE 0 END) as fixed,
--   AVG(rating) as avg_rating
-- FROM feedback f
-- JOIN games g ON f.game_id = g.id
-- WHERE g.developer_id = ?;

-- Get recent feedback for a game
-- SELECT f.*, u.username as player_name
-- FROM feedback f
-- JOIN users u ON f.player_id = u.id
-- WHERE f.game_id = ?
-- ORDER BY f.created_at DESC
-- LIMIT 10;

-- Get feedback response rate
-- SELECT 
--   g.title,
--   COUNT(DISTINCT f.id) as total_feedback,
--   COUNT(DISTINCT fr.id) as replied_feedback,
--   ROUND(COUNT(DISTINCT fr.id) / COUNT(DISTINCT f.id) * 100, 2) as response_rate
-- FROM games g
-- LEFT JOIN feedback f ON g.id = f.game_id
-- LEFT JOIN feedback_replies fr ON f.id = fr.feedback_id
-- WHERE g.developer_id = ?
-- GROUP BY g.id;

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

-- Additional indexes if needed for better query performance
-- CREATE INDEX idx_feedback_game_status ON feedback(game_id, status);
-- CREATE INDEX idx_feedback_player_created ON feedback(player_id, created_at);
-- CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read);

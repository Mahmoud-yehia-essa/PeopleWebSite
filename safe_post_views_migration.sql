-- =============================================================
-- WiseLook: Safe Migration for Post Views (`posts.view_count` & `post_views`)
-- =============================================================

SET NAMES utf8mb4;

-- 1) Add `view_count` column to `posts` if it does not already exist
SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'posts'
      AND COLUMN_NAME = 'view_count'
);
SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `posts` ADD COLUMN `view_count` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `share_count`',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Create `post_views` table for per-viewer deduplication
CREATE TABLE IF NOT EXISTS `post_views` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `post_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `viewer_key` VARCHAR(64) NOT NULL,
    `viewed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `post_views_post_viewer_unique` (`post_id`, `viewer_key`),
    KEY `post_views_post_id_index` (`post_id`),
    KEY `post_views_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

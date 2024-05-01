<?php
$ti_db_schema = [
'schedule_list' => "
CREATE TABLE ". $this->get_tablename('schedule_list') ." (
 `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
 `email` VARCHAR(255) NOT NULL,
 `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
 `order_id` BIGINT(20) NOT NULL,
 `timestamp` INT(11) NOT NULL,
 `sent` TINYINT(1) NOT NULL DEFAULT 0,
 `created_at` DATETIME,
 `hash` VARCHAR(50) NOT NULL,
 `opened_at` DATETIME NULL,
 `clicked_at` DATETIME NULL,
 `feedback` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL,
 `feedback_at` DATETIME NULL,
 PRIMARY KEY (`id`)
)
",
'unsubscribes' => "
CREATE TABLE ". $this->get_tablename('unsubscribes') ." (
 `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
 `email` VARCHAR(255) NOT NULL,
 `created_at` DATETIME,
 PRIMARY KEY (`id`)
)
"
];
?>
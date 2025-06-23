-- Base de données : trdcrft

-- Table : admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table : tickets (signalements)
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporter_id` int(11) DEFAULT NULL,
  `reported_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `type` ENUM('post', 'message', 'photo') DEFAULT 'post',
  `reason` TEXT,
  `screenshot` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table : messages (chat entre admin et utilisateur pour un ticket)
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `sender` varchar(50) DEFAULT NULL, -- 'admin' ou 'user'
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Contraintes (associations)
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_reporter_id` FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reported_id` FOREIGN KEY (`reported_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `messages`
  ADD CONSTRAINT `fk_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE;

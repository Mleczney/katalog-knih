SET NAMES utf8mb4;

CREATE TABLE `user` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) NOT NULL,
	`password_hash` VARCHAR(255) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `publisher` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(150) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_publisher_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `genre` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_genre_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `author` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`first_name` VARCHAR(100) NOT NULL,
	`last_name` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `book` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NOT NULL,
	`year` SMALLINT UNSIGNED NOT NULL,
	`description` TEXT NOT NULL,
	`publisher_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `chk_book_year` CHECK (`year` > 0),
	CONSTRAINT `fk_book_publisher` FOREIGN KEY (`publisher_id`)
		REFERENCES `publisher` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `book_author` (
	`book_id` INT UNSIGNED NOT NULL,
	`author_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`book_id`, `author_id`),
	KEY `ix_book_author_author` (`author_id`),
	CONSTRAINT `fk_book_author_book` FOREIGN KEY (`book_id`)
		REFERENCES `book` (`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_book_author_author` FOREIGN KEY (`author_id`)
		REFERENCES `author` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `book_genre` (
	`book_id` INT UNSIGNED NOT NULL,
	`genre_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`book_id`, `genre_id`),
	KEY `ix_book_genre_genre` (`genre_id`),
	CONSTRAINT `fk_book_genre_book` FOREIGN KEY (`book_id`)
		REFERENCES `book` (`id`) ON DELETE CASCADE,
	CONSTRAINT `fk_book_genre_genre` FOREIGN KEY (`genre_id`)
		REFERENCES `genre` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
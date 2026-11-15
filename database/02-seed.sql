SET NAMES utf8mb4;

INSERT INTO `user` (`id`, `email`, `password_hash`, `name`) VALUES
	(1, 'test@example.com', '$2y$10$UF4lA/y44/qOcuPT9WjtdOPXMIGz25Do/jGlfCTqkKxGmwR7gO6sm', 'Testovací uživatel');

INSERT INTO `publisher` (`id`, `name`) VALUES
	(1, 'Sever'),
	(2, 'Přístav');

INSERT INTO `genre` (`id`, `name`) VALUES
	(1, 'Román'),
	(2, 'Dobrodružný'),
	(3, 'Detektivka');

INSERT INTO `author` (`id`, `first_name`, `last_name`) VALUES
	(1, 'Eva', 'Malá'),
	(2, 'Jan', 'Růžička');

INSERT INTO `book` (`id`, `title`, `year`, `description`, `publisher_id`) VALUES
	(1, 'Tichá řeka', 2018, 'Dva přátelé se vydávají na vorech po řece, která na mapách nikdy nebyla.', 1),
	(2, 'Město pod sněhem', 2022, 'Horské městečko zapadá sněhem a s ním i stopy po zmizelém hoteliérovi.', 1),
	(3, 'Zápisky z pobřeží', 2020, 'Deník námořníka, který po ztroskotání putuje podél divokého pobřeží.', 2);

INSERT INTO `book_author` (`book_id`, `author_id`) VALUES
	(1, 1), (1, 2),
	(2, 1),
	(3, 2);

INSERT INTO `book_genre` (`book_id`, `genre_id`) VALUES
	(1, 1), (1, 2),
	(2, 1), (2, 3),
	(3, 2);
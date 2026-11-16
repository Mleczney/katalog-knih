<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class BookFacade
{
	public function __construct(
		private readonly Explorer $database,
	) {
	}

	public function findCatalog(?int $authorId, ?int $genreId): array
	{
		$sql = 'SELECT b.id, b.title, b.year, b.description, p.name AS publisher
			FROM book b
			JOIN publisher p ON p.id = b.publisher_id
			WHERE 1 = 1';
		$params = [];

		if ($authorId !== null) {
			$sql .= ' AND EXISTS (SELECT 1 FROM book_author ba WHERE ba.book_id = b.id AND ba.author_id = ?)';
			$params[] = $authorId;
		}
		if ($genreId !== null) {
			$sql .= ' AND EXISTS (SELECT 1 FROM book_genre bg WHERE bg.book_id = b.id AND bg.genre_id = ?)';
			$params[] = $genreId;
		}
		$sql .= ' ORDER BY b.title';

		$books = [];
		foreach ($this->database->query($sql, ...$params) as $row) {
			$books[$row->id] = [
				'title' => $row->title,
				'year' => $row->year,
				'description' => $row->description,
				'publisher' => $row->publisher,
				'authors' => [],
				'genres' => [],
			];
		}

		if ($books === []) {
			return [];
		}
		$ids = array_keys($books);

		$authorRows = $this->database->query(
			"SELECT ba.book_id, a.id, CONCAT(a.first_name, ' ', a.last_name) AS name
			FROM book_author ba
			JOIN author a ON a.id = ba.author_id
			WHERE ba.book_id IN (?)
			ORDER BY a.last_name, a.first_name",
			$ids,
		);
		foreach ($authorRows as $row) {
			$books[$row->book_id]['authors'][$row->id] = $row->name;
		}

		$genreRows = $this->database->query(
			'SELECT bg.book_id, g.id, g.name
			FROM book_genre bg
			JOIN genre g ON g.id = bg.genre_id
			WHERE bg.book_id IN (?)
			ORDER BY g.name',
			$ids,
		);
		foreach ($genreRows as $row) {
			$books[$row->book_id]['genres'][$row->id] = $row->name;
		}

		return $books;
	}

	public function getGenrePairs(): array
	{
		return $this->database->table('genre')->order('name')->fetchPairs('id', 'name');
	}

	public function getPublisherPairs(): array
	{
		return $this->database->table('publisher')->order('name')->fetchPairs('id', 'name');
	}

	public function create(array $data): void
	{
		$this->database->transaction(function () use ($data) : void
		{
			$book = $this->database->table('book')->insert($this->bookColumns($data));
			$this->syncLinks('book_author', 'author_id', $book->id, $data['authors']);
			$this->syncLinks('book_genre', 'genre_id', $book->id, $data['genres']);

		});
	}

	public function update(int $id, array $data): void
	{
		$this->database->transaction(function () use ($id, $data): void {
			$this->database->table('book')->where('id', $id)->update($this->bookColumns($data));
			$this->syncLinks('book_author', 'author_id', $id, $data['authors']);
			$this->syncLinks('book_genre', 'genre_id', $id, $data['genres']);
		});
	}

	public function getBook(int $id) : ?array
	{
		$book = $this->database->table('book')->get($id);
		if( $book === null ){
			return null;
		}

		return [
			'title' => $book->title,
			'year' => $book->year,
			'description' => $book->description,
			'publisher' => $book->publisher_id,
			'authors' => $this->database->table('book_author')->where('book_id', $id)->fetchPairs(null, 'author_id'),
			'genres' => $this->database->table('book_genre')->where('book_id', $id)->fetchPairs(null, 'genre_id'),
		];
	}

	private function bookColumns(array $data) : array
	{
		return [
			'title' => $data['title'],
			'year' => $data['year'],
			'description' => $data['description'],
			'publisher_id' => $data['publisher'],
		];
	}

	private function syncLinks(string $table, string $column, int  $bookId, array $ids) : void
	{
		$this->database->table($table)->where('book_id', $bookId)->where("$column NOT", $ids)->delete();

		$existing = $this->database->table($table)->where('book_id', $bookId)->fetchPairs(null, $column);

		foreach (array_diff($ids, $existing) as $id) {
			$this->database->table($table)->insert(['book_id' => $bookId, $column => $id]);
		}
	}


}
<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class AuthorRepository
{
    public function __construct(
		private readonly Explorer $database,
	) {
	}

    public function getPairs(): array
    {
        return $this->database->query(
            "SELECT id, CONCAT(first_name, ' ', last_name) FROM author ORDER BY last_name, first_name",
        )->fetchPairs();
    }

    public function add(string $firstname, string $lastname): void
    {
        $this->database->table('author')->insert([
            'first_name' => $firstname,
            'last_name'=> $lastname,
        ]);
    }
}
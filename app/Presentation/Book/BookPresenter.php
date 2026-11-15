<?php

declare(strict_types=1);

namespace App\Presentation\Book;

use App\Model\AuthorRepository;
use App\Model\BookFacade;
use App\Presentation\SecuredPresenter;

final class BookPresenter extends SecuredPresenter
{
	public function __construct(
		private readonly BookFacade $books,
		private readonly AuthorRepository $authors,
	) {
	}

	public function renderDefault(string $author = '', string $genre = ''): void
	{
		$authorId = $this->parseFilterId($author);
		$genreId = $this->parseFilterId($genre);

		$this->template->books = $this->books->findCatalog($authorId, $genreId);
		$this->template->authors = $this->authors->getPairs();
		$this->template->genres = $this->books->getGenrePairs();
		$this->template->selectedAuthor = $authorId;
		$this->template->selectedGenre = $genreId;
	}

	/** Prázdný řetězec = bez filtru, kladné číslo = ID, cokoli jiného = chyba 404. */
	private function parseFilterId(string $value): ?int
	{
		if ($value === '') {
			return null;
		}
		if (!ctype_digit($value) || (int) $value < 1) {
			$this->error('Neplatný filtr.');
		}
		return (int) $value;
	}
}
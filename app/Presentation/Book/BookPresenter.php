<?php

declare(strict_types=1);

namespace App\Presentation\Book;

use App\Forms\FormFactory;
use App\Model\AuthorRepository;
use App\Model\BookFacade;
use App\Presentation\SecuredPresenter;
use Nette\Application\UI\Form;
use Nette\Database\ForeignKeyConstraintViolationException;

final class BookPresenter extends SecuredPresenter
{
	public function __construct(
		private readonly BookFacade $books,
		private readonly AuthorRepository $authors,
		private readonly FormFactory $formFactory,
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

	protected function createComponentBookForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('title', 'Název')
			->setRequired('Zadejte název knihy.')
			->addRule($form::MaxLength, 'Název může mít nejvýše %d znaků.', 255);

		$form->addInteger('year', 'Rok vydání')
			->setRequired('Zadejte rok vydání.')
			->addRule($form::Min, 'Rok vydání musí být kladné celé číslo.', 1)
			->addRule($form::Max, 'Rok vydání může být nejvýše %d.', 9999);

		$form->addTextArea('description', 'Popis')
			->setRequired('Zadejte popis knihy.')
			->addRule($form::Pattern, 'Zadejte popis knihy.', '[\s\S]*\S[\s\S]*')
			->addFilter(fn(string $value): string => trim($value));

		$form->addSelect('publisher', 'Nakladatelství', $this->books->getPublisherPairs())
			->setPrompt('— vyberte nakladatelství —')
			->setRequired('Vyberte nakladatelství.');

		$form->addCheckboxList('authors', 'Autoři', $this->authors->getPairs())
			->setRequired('Vyberte alespoň jednoho autora.');

		$form->addCheckboxList('genres', 'Žánry', $this->books->getGenrePairs())
			->setRequired('Vyberte alespoň jeden žánr.');

		$form->addSubmit('save', 'Uložit');

		$form->onValidate[] = $this->validateChoices(...);
		$form->onSuccess[] = $this->bookFormSucceeded(...);
		return $form;
	}

	private function validateChoices(Form $form): void
	{
		foreach (['authors', 'genres'] as $name) {
			$sent = $form[$name]->getRawValue();
			$allowed = array_keys($form[$name]->getItems());
			if (array_diff($sent, $allowed)) {
				$form[$name]->addError('Vybraná položka není platná.');
			}
		}
	}

	private function bookFormSucceeded(Form $form, array $values): void
	{
		try {
			$this->books->create($values);
		} catch (ForeignKeyConstraintViolationException) {
			$form->addError('Vybraný autor, žánr nebo nakladatelství už neexistuje.');
			return;
		}

		$this->flashMessage("Kniha „{$values['title']}“ byla přidána.", 'success');
		$this->redirect('default');
	}

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
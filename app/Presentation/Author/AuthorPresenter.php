<?php

declare(strict_types=1);

namespace App\Presentation\Author;

use App\Forms\FormFactory;
use App\Model\AuthorRepository;
use App\Presentation\SecuredPresenter;
use Nette\Application\UI\Form;

final class AuthorPresenter extends SecuredPresenter
{
	public function __construct(
		private readonly AuthorRepository $authors,
		private readonly FormFactory $formFactory,
	) {
	}

    public function renderDefault(): void
    {
        $this->template->authors = $this->authors->getPairs();
    }

    protected function createComponentAuthorForm(): Form
    {
		$form = $this->formFactory->create();
		$form->addText('firstName', 'Jméno')
			->setRequired('Zadejte jméno autora.')
			->addRule($form::MaxLength, 'Jméno může mít nejvýše %d znaků.', 100);
		$form->addText('lastName', 'Příjmení')
			->setRequired('Zadejte příjmení autora.')
			->addRule($form::MaxLength, 'Příjmení může mít nejvýše %d znaků.', 100);
		$form->addSubmit('save', 'Přidat autora');

		$form->onSuccess[] = $this->authorFormSucceeded(...);
		return $form;
    }

    private function authorFormSucceeded(Form $form, array $values): void
    {
		$this->authors->add($values['firstName'], $values['lastName']);
		$this->flashMessage("Autor {$values['firstName']} {$values['lastName']} byl přidán.", 'success');
		$this->redirect('this');
    }
}
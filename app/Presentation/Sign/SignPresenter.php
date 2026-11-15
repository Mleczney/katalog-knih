<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use App\Forms\FormFactory;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

final class SignPresenter extends Presenter
{
    #[Persistent]
    public string $backlink = "";

    public function __construct(
        private readonly FormFactory $formFactory,
    ){}

    public function actionIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect("Book:default");
        }
    }

    protected function createComponentSignInForm(): Form
    {
        $form = $this->formFactory->create();
        $form->addEmail("email","E-mail")->setRequired("Zadejte e-mail.");
        $form->addPassword("password","Heslo")->setRequired("Zadejte heslo.");
        $form->addSubmit('send','Přihlásit se');
        
        $form->onSuccess[] = $this->signInFormSucceeded(...);
        return $form;
    }

    private function signInFormSucceeded(Form $form, array $values): void
    {
        try {
            $this->getUser()->login($values['email'], $values['password']);
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
            return;
        }

        $this->restoreRequest($this->backlink);
        $this->redirect('Book:default');
    }
}
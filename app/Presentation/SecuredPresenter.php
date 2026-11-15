<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette\Application\UI\Presenter;

abstract class SecuredPresenter extends Presenter
{
    protected function startup(): void
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pro přístup ke katalogu se přihlaste.', 'warning');
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }

    public function handleLogout(): void{
        $this->getUser()->logout(true);
		$this->flashMessage('Byli jste odhlášeni.', 'success');
		$this->redirect('Sign:in');
    }
}
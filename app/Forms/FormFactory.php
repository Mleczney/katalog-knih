<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use Nette\Forms\Controls\CsrfProtection;
use Nette\Forms\Validator;

final class FormFactory
{
	
	public function __construct()
	{
		Validator::$messages = [
			Controls\CsrfProtection::Protection => 'Platnost formuláře vypršela. Odešlete jej prosím znovu.',
			Form::Filled => 'Toto pole je povinné.',
			Form::Email => 'Zadejte platnou e-mailovou adresu.',
			Form::Integer => 'Zadejte celé číslo.',
			Controls\SelectBox::Valid => 'Vyberte platnou položku.',
		] + Validator::$messages;
	}
    public function create(): Form
	{
		$form = new Form;
		$form->addProtection();
		return $form;
	}
}
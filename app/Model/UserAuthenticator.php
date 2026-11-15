<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class UserAuthenticator implements Authenticator
{
	public function __construct(
		private readonly Explorer $database,
		private readonly Passwords $passwords,
	) {
	}

	public function authenticate(string $user, string $password): IIdentity
	{
		$row = $this->database->table('user')
			->where('email', mb_strtolower(trim($user)))
			->fetch();

		if (!$row || !$this->passwords->verify($password, $row->password_hash)) {
			throw new AuthenticationException('Nesprávný e-mail nebo heslo.');
		}

		return new SimpleIdentity($row->id, [], [
			'email' => $row->email,
			'name' => $row->name,
		]);
	}
}
<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

final class Bootstrap
{
	public static function boot(): Configurator
	{
		$rootDir = dirname(__DIR__);
		$configurator = new Configurator;

		$configurator->setDebugMode(getenv('APP_DEBUG') === '1');
		$configurator->enableTracy($rootDir . '/log');
		$configurator->setTempDirectory($rootDir . '/temp');

		$configurator->addConfig($rootDir . '/config/common.neon');
		$configurator->addConfig($rootDir . '/config/services.neon');

		return $configurator;
	}
}
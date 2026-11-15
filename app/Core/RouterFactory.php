<?php

declare(strict_types= 1);

namespace App\Core;

use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    public static function createRouter(): RouteList{
        $router = new RouteList();
        $router->addRoute('prihlaseni', 'Sign:in');
        $router->addRoute('autori', 'Author:default');
        $router->addRoute('kniha/nova', 'Book:add');
        $router->addRoute('', 'Book:default');

        return $router;
    }
}
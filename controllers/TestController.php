<?php

declare(strict_types=1);

namespace controllers;

use entities\Category;
use entities\Product;
use entities\User;
use peps\core\DBAL;
use peps\core\Router;


/**
 * Classe 100% statique, contrôle les produits.
 */
final class TestController
{
    /**
     * Constructeur privé.
     * Il est vide mais doit être créé pour pouvoir le passer en privé, sinon PHP en créé un automatiquement (en public).
     */
    private function __construct()
    {
    }

    /**
     * Méthode de test.
     * 
     * GET /test
     */
    public static function test(): void
    {
        $user = new User();
        $user->log = "tom";
        $user->pwd = "tom";
        $user->login();
        sleep(30);
        UserController::logout();
    }
}

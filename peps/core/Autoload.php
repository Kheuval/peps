<?php

declare(strict_types=1);

namespace peps\core;

/**
 * Classe 100% statique d'autoload.
 */
final class Autoload
{
    /**
     * Constructeur privé.
     * Il est vide mais doit être créé pour pouvoir le passer en privé, sinon PHP en créé un automatiquement (en public).
     */
    private function __construct()
    {
    }

    /**
     * Initialise l'autoload.
     * DOIT être appellée depuis le contrôleur frontal EN TOUT PREMIER.
     */
    public static function init(): void
    {
        // Inscrire la fonction d'autoload dans la pile d'autoload.
        spl_autoload_register(fn ($className) => require $className . '.php');
    }
}

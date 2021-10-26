<?php

declare(strict_types=1);

namespace controllers;

use entities\User;
use peps\core\Router;

/**
 * Contrôle la connexion/deconnexion des utilisateurs.
 * 
 * @see User
 * @see Router
 */
final class UserController
{
    // Messages d'erreur.
    private const ERR_LOGIN = "Identifiant ou mot de passe absent";

    /**
     * Constructeur privé.
     * Il est vide mais doit être créé pour pouvoir le passer en privé, sinon PHP en créé un automatiquement (en public).
     */
    private function __construct()
    {
    }

    /**
     * Affiche le formulaire de connexion.
     * 
     * GET user/signin
     */
    public static function signin(): void
    {
        // Rendre la vue.
        Router::render('signin.php', ['log' => null]);
    }

    /**
     * Connecte l'utilisateur si possible puis redirige.
     * 
     * POST user/login
     */
    public static function login(): void
    {
        // Prévoir le tableau des messages d'erreur.
        $errors = [];
        // Créer un nouvel utilisateur.
        $user = new User();
        // Récupérer les données POST (on rajoute ?: null car filter_input peut retourner false).
        $user->log = filter_input(INPUT_POST, 'log', FILTER_SANITIZE_STRING) ?: null;
        // Récupérer les données POST (on rajoute ?: null car filter_input peut retourner false).
        $user->pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING) ?: null;
        // Si login ok, rediriger vers l'accueil.
        if ($user->login()) {
            Router::redirect('/');
        }
        // Sinon, afficher de nouvau le formulaire avec le message d'erreur.
        $errors[] = self::ERR_LOGIN;
        Router::render('signin.php', ['log' => $user->log, 'errors' => $errors]);
    }

    /**
     * Déconnecte l'utilisateur puis redirige.
     * 
     * GET user/logout
     */
    public static function logout(): void
    {
        // Détruire la session.
        session_destroy();
        // Rediriger vers l'accueil.
        Router::redirect('/');
    }
}

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
    private const ERR_LOGIN = "Identifiant ou mot de passe absents ou invalides.";
    private const ERR_INVALID_LOG = "Identifiant absent ou invalide.";
    private const ERR_INVALID_HASH = "Lien invalide ou expiré.";

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
     * Affiche le formulaire d'inscription.
     *
     * GET /user/signup
     */
    public static function signup(): void
    {
        Router::render('signup.php', ['user' => new User()]);
    }

    /**
     * Inscrit l'utilisateur si possible puis redirige.
     * 
     * POST user/save
     */
    public static function save(): void
    {
        // Prévoir le tableau des messages d'erreur.
        $errors = [];
        // Créer un nouvel utilisateur.
        $user = new User();
        // Récupérer les données POST (on rajoute ?: null car filter_input peut retourner false).
        $user->log = filter_input(INPUT_POST, 'log', FILTER_SANITIZE_STRING) ?: null;
        $user->lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING) ?: null;
        $user->firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING) ?: null;
        $user->email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: null;
        $user->pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING) ?: null;
        // Si données valides, chiffré le mot de passe, persister et rediriger.
        if ($user->validate($errors)) {
            $user->pwd = password_hash($user->pwd, PASSWORD_DEFAULT);
            $user->persist();
            Router::redirect('/user/signin');
        }
        // Sinon, afficher de nouveau le formulaire avec le message d'erreur.
        Router::render('signup.php', ['user' => $user, 'errors' => $errors]);
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

    /**
     * Affiche la vue du mot de passe oublié.
     * 
     * GET /user/forgottenPwd
     */
    public static function forgottenPwd(): void
    {
        Router::render('forgottenPwd.php', ['log' => null]);
    }

    /**
     * Génère et envoie par email un lien destiné à saisir un nouveau mot de passe.
     *
     * GET /user/newPwd
     */
    public static function newPwd(): void
    {
        // Initialiser le tableau des messages d'erreur.
        $errors = [];
        // Récupérer 'log'.
        $log = filter_input(INPUT_POST, 'log', FILTER_SANITIZE_STRING) ?: null;
        // Si 'log' inconnu, rendre la vue 'forgottenPwd.php'.
        if (!$user = User::findOneBy(['log' => $log])) {
            $errors[] = self::ERR_INVALID_LOG;
            Router::render('forgottenPwd.php', ['log' => $log, 'errors' => $errors]);
        }
        // Générer un hash.
        $hash = hash('sha1', microtime(), false);
        // Stocker le hash et son timeout en DB.
        $user->pwdHash = $hash;
        $user->pwdTimeout = date('Y-m-d H:i:s', time() + 10 * 60);
        $user->persist();
        // Envoyer le lien par email.
        $subject = 'ACME : Réinitialiser votre mot de passe';
        $body = "Bonjour,
Veuillez cliquer sur ce lien pour réinitialiser votre mot de passe (ce lien expire dans 10 minutes)
http://acmepeps.local/user/setPwd/{$hash}";
        mail($user->email, $subject, $body);
        // Rediriger vers l'accueil'.
        Router::redirect('/');
    }

    /**
     * Affiche la vue permettant de saisir un nouveau mot de passe.
     * 
     * GET /user/setPwd/{hash}
     *
     * @param array $params Tableau des paramètres.
     */
    public static function setPwd(array $params): void
    {
        // Récupérer le hash.
        $hash = $params['hash'];
        // Si 'hash' absent ou inconnu, ou 'timeout' expiré, rendre la vue 'forgottenPwd.php'.
        if (!$hash || !($user = User::findOneBy(['pwdHash' => $hash])) || $user->pwdTimeout < date('Y-m-d H:i:s')) {
            $errors[] = self::ERR_INVALID_HASH;
            Router::render('forgottenPwd.php', ['log' => null, 'errors' => $errors]);
        }
        // Rendre la vue.
        Router::render('setPwd.php', ['hash' => $hash]);
    }

    /**
     * Sauvegarde le nouveau mot de passe.
     * 
     * POST /user/savePwd
     */
    public static function savePwd(): void
    {
        // Récupérer le nouveau mot de passe et le hash.
        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING) ?: null;
        $hash = filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING) ?: null;
        // Si 'pwd' absent ou 'hash' absent ou 'timeout' expiré, rendre la vue 'forgottenPwd.php'.
        if (!$pwd || !$hash || !($user = User::findOneBy(['pwdHash' => $hash])) || $user->pwdTimeout < date('Y-m-d H:i:s')) {
            $errors[] = self::ERR_INVALID_HASH;
            Router::render('forgottenPwd.php', ['log' => null, 'errors' => $errors]);
        }
        // Chiffrer le nouveau mot de passe.
        $user->pwd = password_hash($pwd, PASSWORD_DEFAULT);
        // Supprimer le hash et le timeout.
        $user->pwdHash = null;
        $user->pwdTimeout = null;
        // Persister.
        $user->persist();
        // Inscrire l'utilisateur dans la session.
        $_SESSION['idUser'] = $user->idUser;
        // Envoyer un email de confirmation.
        $subject = 'ACME : Changement de votre mot de passe';
        $body = "Bonjour,
Votre mot de passe a été modifié avec succès !";
        mail($user->email, $subject, $body);
        // Rediriger vers l'accueil.
        Router::redirect('/');
    }
}

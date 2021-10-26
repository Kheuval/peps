<?php

declare(strict_types=1);

namespace entities;

use peps\core\ORMDB;
use peps\core\UserLoggable;

/**
 * Entité User.
 * 
 * @see  ORMDB
 * @see UserLoggable
 */
class User extends ORMDB implements UserLoggable
{
    /**
     * PK.
     */
    public ?int $idUser = null;

    /**
     * Identifiant de connexion.
     */
    public ?string $log = null;

    /**
     * Mot de passe de connexion.
     * En clair après saisie.
     * Chiffré après hydratation.
     */
    public ?string $pwd = null;

    /**
     * Nom.
     */
    public ?string $lastName = null;

    /**
     * Prénom.
     */
    public ?string $firstName = null;

    /**
     * Instance de l'utilisateur en session.
     * En cache pour lazy loading.
     */
    protected static ?self $userSession = null;

    /**
     * Constructeur.
     */
    public function __construct(int $idUser = null)
    {
        $this->idUser = $idUser;
    }

    /**
     * {@inheritDoc}
     */
    public function login(): bool
    {
        // Si les propriétes log ou pwd non renseignées, retourner false.
        if (!$this->log || !$this->pwd) {
            return false;
        }
        // Si aucun utilisateur correspondant au login, retourner false. On utilise une méthode héritée de ORMDB pour ne pas à faire la requête.
        if (!$user = self::findOneBy(['log' => $this->log])) {
            return false;
        }
        // Si trouvé mais mot de passe incorrect, retourner false.
        if (!password_verify($this->pwd, $user->pwd)) {
            return false;
        }
        // Inscrire l'utilisateur dans la session et retourner true.
        $_SESSION['idUser'] = $user->idUser;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function getUserSession(): ?self
    {
        // Si pas en cache, créer et hydrater l'utilisateur en session.
        if (!self::$userSession) {
            // Créer une instance.
            $user = new self($_SESSION['idUser'] ?? null);
            // Si $user est non-nul et que l'hydratation a réussi, stocker l'instance dans le cache.
            self::$userSession = $user && $user->hydrate() ? $user : null;
        }
        // Retourner l'utilisateur en session.
        return self::$userSession;
    }
}

<?php

declare(strict_types=1);

namespace peps\core;

/**
 * Interface de connexion des utilisateurs.
 */
interface UserLoggable extends ORM
{
    /**
     * Tente de loguer le UserLoggable.
     *
     * @return boolean True ou false selon que le UserLoggable a été logué ou non.
     */
    function login(): bool;

    /**
     * Retourne le UserLoggable en session ou null si aucun en session.
     * DEVRAIT utiliser le lazy loading.
     *
     * @return self|null UserLoggable en session ou null.
     */
    static function getUserSession(): ?self;
}

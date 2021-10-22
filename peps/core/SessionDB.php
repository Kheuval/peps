<?php

declare(strict_types=1);

namespace peps\core;

use SessionHandlerInterface;

/**
 * Gestion des sessions en base de données.
 * NECESSITE un table "session" avec les colonnes "sid", "data", "dateSession".
 * 3 modes possibles : 
 * PERSISTENT : La session se termine exclusivement après l'expiration du timeout au-delà de la dernière requête du client.
 * HYBRID : La session se termine à la fermeture du navigateur ou après l'expiration du timeout au-delà de la dernière requête du client.
 * ABSOLUTE : La session se termine exclusivement après l'expiration du timeout au-delà la PREMIERE requête du client.
 */
class SessionDB implements SessionHandlerInterface
{
    /**
     * Initialise et démarre la session.
     *
     * @param integer $timeout Durée maxi de la session en secondes.
     * @param string $mode Mode de la session.
     * @param string $sameSite Mitigation CSRF.
     */
    public static function init(int $timeout, string $mode, string $sameSite): void
    {
        // Définir la durée de vie du cookie en fonction du mode.
        match ($mode) {
            // 20 ans, expiration gérée côté serveur.
            Cfg::get('SESSION_PERSISTENT') => ini_set('session.cookie_lifetime', '630720000'),
            // Lorsque le navigateur est fermé (cookie de session).
            Cfg::get('SESSION_HYBRID') => ini_set('session.cookie_lifetime', '0'),
            // Après l'expiration du timeout.
            Cfg::get('SESSION_ABSOLUTE') => ini_set('session.cookie_lifetime', (string) $timeout)
        };

        // Définir le timeout de GC pour supprimer les sessions expirées.
        ini_set('session.gc_maxlifetime', (string) $timeout);

        // Utiliser les cookies.
        ini_set('session.use_cookies', '1');

        // Utiliser SEULEMENT les cookies.
        ini_set('session.use_only_cookies', '1');

        // Ne pas passer l'ID de session en GET.
        ini_set('session.use_trans_sid', '0');

        // Mitiger les attaques XSS (Cross Site Scripting = injection de script) en interdisant l'accès aux cookies via JS.
        ini_set('session.cookie_httponly', '1');


        session_set_save_handler(new self());
        session_start();
    }


    public function open($path, $name): bool
    {
        var_dump('open');
        return true;
    }


    public function read($id): string|false
    {
        var_dump($id);
        return '';
    }


    public function write($id, $data): bool
    {
        var_dump("write {$id} : {$data}");
        return true;
    }


    public function close(): bool
    {
        var_dump('close');
        return true;
    }


    public function destroy($id): bool
    {
        var_dump("destroy {$id}");
        return true;
    }


    public function gc($max_lifetime): int|false
    {
        var_dump("gc {$max_lifetime}");
        return 1;
    }
}

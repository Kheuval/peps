<?php

declare(strict_types=1);

namespace peps\core;

use PDOException;
use SessionHandlerInterface;

/**
 * Gestion des sessions en base de données.
 * NECESSITE un table "session" avec les colonnes "sid", "data", "dateSession".
 * 3 modes possibles : 
 * PERSISTENT : La session se termine exclusivement après l'expiration du timeout au-delà de la dernière requête du client.
 * HYBRID : La session se termine à la fermeture du navigateur ou après l'expiration du timeout au-delà de la dernière requête du client.
 * ABSOLUTE : La session se termine exclusivement après l'expiration du timeout au-delà de la PREMIERE requête du client.
 */
class SessionDB implements SessionHandlerInterface
{
    /**
     * Vrai si session expirée, faux sinon.
     */
    protected static bool $expired = false;

    /**
     * Durée maxi de la session en secondes.
     */
    protected static int $timeout;

    /**
     * Initialise et démarre la session.
     *
     * @param integer $timeout Durée maxi de la session en secondes.
     * @param string $mode Mode de la session.
     * @param string $sameSite Mitigation CSRF.
     */
    public static function init(int $timeout, string $mode, string $sameSite): void
    {
        // Définir le timeout.
        self::$timeout = $timeout;

        // Définir la durée de vie du cookie en fonction du mode.
        match ($mode) {
            // 20 ans, expiration gérée côté serveur.
            Cfg::get('SESSION_PERSISTENT') => ini_set('session.cookie_lifetime', '630720000'),
            // Lorsque le navigateur est fermé (cookie de session).
            Cfg::get('SESSION_HYBRID') => ini_set('session.cookie_lifetime', '0'),
            // Après l'expiration du timeout.
            Cfg::get('SESSION_ABSOLUTE') => ini_set('session.cookie_lifetime', (string) $timeout)
        };

        // Définir le timeout du GC pour supprimer les sessions expirées.
        ini_set('session.gc_maxlifetime', (string) $timeout);

        // Utiliser les cookies.
        ini_set('session.use_cookies', '1');

        // Utiliser SEULEMENT les cookies.
        ini_set('session.use_only_cookies', '1');

        // Ne pas passer l'ID de session en GET.
        ini_set('session.use_trans_sid', '0');

        // Mitiger les attaques XSS (Cross Site Scripting = injection de script) en interdisant l'accès aux cookies via JS.
        ini_set('session.cookie_httponly', '1');

        // Mitiger les attaques SFA (Session Fixation Attack) en refusant les cookies non-générés par PHP.
        ini_set('session.use_strict_mode', '1');

        // Mitiger les attaques CSRF (Cross Site Request Forgery).
        ini_set('session.cookie_samesite', $sameSite);

        // Définir une instance de cette classe comme gestionnaire des sessions.
        session_set_save_handler(new self());

        // Démarrer la session.
        session_start();

        // Si session expirée, la détruire et en démarrer une nouvelle.
        if (self::$expired) {
            session_destroy();
            self::$expired = false;
            session_start();
        }
    }

    /**
     * Inutile ici.
     *
     * @param string $path Chemin du fichier de sauvegarde de la session.
     * @param string $name Nom de la session (PHPSESSID par défaut).
     * @return boolean Pour usage interne à PHP, ici systématiquement true.
     */
    public function open($path, $name): bool
    {
        return true;
    }

    /**
     * Lire et retourner les données de session.
     *
     * @param string $id SID
     * @return string|false Données de session sérialisées (en PHP) ou false si lecture impossible.
     */
    public function read($id): string|false
    {
        // Créer la requête.
        $q = "SELECT * FROM session WHERE sid = :sid";
        $params = [':sid' => $id];
        // Exécuter la requête et si une session est retrouvée, vérifier sa validité.
        if ($objSession =  DBAL::get()->xeq($q, $params)->findOne()) {
            // Si expirée, passer le booléen $expired à true et retourner une chaîne vide.
            if (strtotime($objSession->dateSession) + self::$timeout < time()) {
                self::$expired = true;
                return '';
            }
            // Sinon, retourner les données.
            return $objSession->data;
        }
        // Si pas de session, retourner une chaîne vide.
        return '';
    }

    /**
     * Écrit les données de session.
     *
     * @param string $id SID
     * @param string $data Données de session.
     * @return boolean Pour usage interne à PHP, ici systématiquement true.
     */
    public function write($id, $data): bool
    {
        if (!self::$expired) {
            // Tenter une requête INSERT.
            try {
                $q = "INSERT INTO session VALUES (:sid, :data, :dateSession)";
                $params = [':sid' => $id, ':data' => $data, ':dateSession' => date('Y-m-d H:i:s')];
                DBAL::get()->xeq($q, $params);
            }
            // Si erreur (doublon de SID), exécuter une requête UPDATE.
            catch (PDOException $e) {
                $q = "UPDATE session SET data = :data, dateSession = :dateSession WHERE sid = :sid";
                $params = [':sid' => $id, ':data' => $data, ':dateSession' => date('Y-m-d H:i:s')];
                DBAL::get()->xeq($q, $params);
            }
        }
        return true;
    }

    /**
     * Inutile ici.
     *
     * @return boolean Pour usage interne à PHP, ici systématiquement true.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Détruit la session (cookie uniquement, l'enregistrement en DB sera supprimé par le GC)
     *
     * @param string $id SID.
     * @return boolean
     */
    public function destroy($id): bool
    {
        // Récupérer le nom de la session.
        $sessionName = session_name();
        // Supprimer le cookie du navigateur.
        setcookie($sessionName, '', 1, '/');
        // Supprimer la clef du tableau des cookies du serveur.
        unset($_COOKIE[$sessionName]);
        return true;
    }

    /**
     * Garbage Collector, supprime les sessions expirées en DB.
     *
     * @param int $max_lifetime Durée de vie maxi d'une session en secondes.
     * @return integer|false True si la suppression a réussi, false sinon.
     */
    public function gc($max_lifetime): int|false
    {
        // Créer la requête.
        $q = "DELETE FROM session WHERE dateSession < :dateMin";
        $params = [':dateMin' => date('Y-m-d H:i:s', time() - $max_lifetime)];
        // Exécuter la requête et retourner le nombre d'enregistrements supprimés.
        return DBAL::get()->xeq($q, $params)->nb();
    }
}

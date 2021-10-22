<?php

declare(strict_types=1);

namespace peps\core;

use PDO;
use PDOStatement;
use stdClass;

/**
 * Classe DBAL (database abstraction layer) via PDO.
 * Design pattern Singleton.
 */
final class DBAL
{
    /**
     * Options de la connexion communes à toutes les bases de données: 
     *  - Mode de gestion des erreurs basée sur des exceptions.
     *  - Typage des colonnes respecté.
     *  - Requêtes réellement préparées plutôt que simplement simulées.
     */
    private const OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    /**
     * Instance Singleton.
     */
    private static ?self $instance = null;

    /**
     * Instance de PDO.
     */
    private ?PDO $db = null;

    /**
     * Instance de PDOStatement.
     */
    private ?PDOStatement $stmt = null;

    /**
     * Nombre d'enregistrements retrouvés (SELECT) ou affectés (INSERT, UPDATE, DELETE) par la derniére requête.
     */
    private ?int $nb = null;

    /**
     * Constructeur privé.
     * Il est vide mais doit être créé pour pouvoir le passer en privé, sinon PHP en créé un automatiquement (en public).
     */
    private function __construct()
    {
    }

    /**
     * Créer l'instance Singleton et l'instance PDO encapsulée, puis défini ses paramètres.
     *
     * @param string $driver Driver de la DB.
     * @param string $host Hôte de la DB.
     * @param integer $port Port de la DB.
     * @param string $dbName Nom de la DB.
     * @param string $log Identifiant de la DB.
     * @param string $pwd Mot de passe de la DB.
     * @param string $charset Jeu de caractères de la DB.
     */
    public static function init(
        string $driver,
        string $host,
        int $port,
        string $dbName,
        string $log,
        string $pwd,
        string $charset
    ): void {
        // Si déjà initialisée, ne rien faire.
        if (self::$instance) {
            return;
        }
        // Créer la chaîne DSN.
        $dsn = "{$driver}:host={$host};port={$port};dbname={$dbName};charset={$charset}";
        // Créer l'instance Singleton.
        self::$instance = new self();
        // Créer l'instance PDO.
        self::$instance->db = new PDO($dsn, $log, $pwd, self::OPTIONS);
    }

    /**
     * Retourne l'instance Singleton.
     * La méthode init() devrait avoir été appelée au préalable.
     *
     * @return self|null Instance Singleton ou null si init() pas encore appelée.
     */
    public static function get(): ?self
    {
        return self::$instance;
    }

    /**
     * Execute une requête SQL
     *
     * @param string $q Requête.
     * @param array|null $params Tableau associatif des paramètres optionnels.
     * @return static $this pour chaînage.
     */
    public function xeq(string $q, ?array $params = null): static
    {
        if ($params) {
            // Si paramètres présents, préparer et executer la requête.
            $this->stmt = $this->db->prepare($q);
            $this->stmt->execute($params);
            // Récupérer le nombre d'enregistrements retrouvés ou affectés.
            $this->nb = $this->stmt->rowCount();
        } elseif (mb_stripos(ltrim($q), 'SELECT') === 0) {
            // Si requête SELECT l'exécuter avec query.
            $this->stmt = $this->db->query($q);
            // Récupérer le nombre d'enregistrements retrouvés.
            $this->nb = $this->stmt->rowCount();
        } else {
            // Si requête non SELECT l'exécuter avec exec et récupérer le nombre d'enregistrements affectés.
            $this->nb = $this->db->exec($q);
        }
        return $this;
    }

    /**
     * Retourne le nombre d'enregistrements retrouvés (SELECT) ou affectés par la dernière requête.
     */
    public function nb(): int
    {
        return $this->nb;
    }

    /**
     * Retourne un tableau d'instances d'une classe donnée (et hydratée) en exploitant le dernier recordset d'enregistrements.
     * Une requête SELECT devrait avoir été exécutée préalablement.
     *
     * @param string $className Nom de la classe.
     * @return array Tableau des instances de la classe donnée hydratées.
     */
    public function findAll(string $className = 'stdClass'): array
    {
        // Si pas de recordset (stmt), retourner un tableau vide.
        if (!$this->stmt) {
            return [];
        }
        // Sinon, exploiter le recordset et retourner un tableau d'instances.
        $this->stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $className);
        return $this->stmt->fetchAll();
    }

    /**
     * Retourne une instance d'une classe donnée en exploitant le premier des enregistrements du dernier recordset.
     * Une requête SELECT (typiquement retrouvant au maximum 1 enregistrement) devrait avoir été exécutée préalablement.
     * Retourne null si aucun recordset ou recordset vide.
     * 
     * @return object|null L'instance de la classe donnée ou null.
     */
    public function findOne(string $className = 'stdClass'): ?object 
    {
        // Si pas de recordset (stmt), retourner null.
        if (!$this->stmt) {
            return null;
        }
        // Sinon, exploiter le recordset et retourner la première instance ou null.
        $this->stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $className);
        return $this->stmt->fetch() ?: null;
    }

    /**
     * Hydrate une instance donnée en exploitant le premier enregistrement du dernier recordset.
     * Une requête SELECT (typiquement retrouvant au maximum 1 enregistrement) devrait avoir été exécutée préalablement. 
     *
     * @param object $obj L'instance donnée à hydrater.
     * @return boolean True ou false selon que l'hydratation a réussi ou non.
     */
    public function into(object $obj): bool
    {
        // Si pas de recordset (stmt), retourner null.
        if (!$this->stmt) {
            return false;
        }
        // Sinon, on exploite le recordset et on hydrate l'instance. Puis on retourne un booléen.
        $this->stmt->setFetchMode(PDO::FETCH_INTO, $obj);
        return (bool) $this->stmt->fetch();
    }

    /**
     * Retourne la dernière clef primaire auto-incrémentée.
     *
     * @return integer La clef primaire.
     */
    public function pk(): int
    {
        return (int) $this->db->lastInsertId();
    }

    /**
     * Démarre une transaction SQL.
     *
     * @return static $this pour chaînage.
     */
    public function start(): static
    {
        $this->db->beginTransaction();
        return $this;
    }

    /**
     * Définit un point de restauration dans la transaction en cours.
     *
     * @param string $label Le nom du point de restauration.
     * @return static $this pour chaînage.
     */
    public function savepoint(string $label): static
    {
        $q = "SAVEPOINT {$label}";
        return $this->xeq($q);
    }

    /**
     * Effectue un rollback au point de restauration donné ou au début si absent.
     *
     * @param string|null $label Le nom du point de restauration.
     * @return static $this pour chaînage.
     */
    public function rollback(?string $label = null): static
    {
        $q = "ROLLBACK";
        if ($label) {
            $q .= "TO {$label}";
        }
        return $this->xeq($q);
    }

    /**
     * Valide la transaction SQL en cours.
     *
     * @return static $this pour chaînage.
     */
    public function commit(): static
    {
        $this->db->commit();
        return $this;
    }
}

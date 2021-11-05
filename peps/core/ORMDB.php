<?php

declare(strict_types=1);

namespace peps\core;

use Error;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

/**
 * Implémentation de la persistance ORM en base de données via DBAL.
 * Les classes entités DEVRAIENT étendre cette classe.
 * *****************************************************************
 * Règles à respecter pour profiter de cette implémentation.
 * Sinon, redéfinir ses méthodes dans les classes entités.
 * *****************************************************************
 * 1. Tables nommées selon cet exemple : classe 'TrucChose', table 'trucChose'.
 * 2. PK auto-incrémentée nommée selon cet exemple : table 'trucChose', pk 'idTrucChose'.
 * 3. Chaque colonne correspond à une propriété PUBLIC du même nom. Les autres propriétés NE sont pas PUBLIC
 * 4. Si une propriété 'trucChose' est inaccessible, la méthode 'getTrucChose' sera invoquée si elle existe. Sinon null sera retourné.
 */
class ORMDB implements ORM, JsonSerializable
{
    /**
     * Hydrate l'entité depuis le système de stockage.
     *
     * @return boolean True ou false selon que l'hydratation ait réussi ou non.
     */
    function hydrate(): bool
    {
        // Déduire le nom de la table à partir de nom de la classe de l'entité $this.
        $className = (new ReflectionClass($this))->getShortName();
        $tableName = lcfirst($className);
        // Construire le nom de la PK à partir du nom de la classe.
        $pkName = "id{$className}";
        $q = "SELECT * FROM {$tableName} WHERE {$pkName} = :__ID__";
        $params = [':__ID__' => $this->$pkName];
        // Exécuter la requête et hydrater $this.
        return DBAL::get()->xeq($q, $params)->into($this);
    }

    /**
     * Persiste l'entité vers le système de stockage.
     *
     * @return boolean True systématiquement.
     */
    function persist(): bool
    {
        $rc = new ReflectionClass($this);
        // Déduire le nom de la table à partir de nom de la classe de l'entité $this.
        $className = $rc->getShortName();
        $tableName = lcfirst($className);
        // Construire le nom de la PK à partir du nom de la classe.
        $pkName = "id{$className}";
        // Récupérer le tableau des propriétés publiques de la classe.
        $properties = $rc->getProperties(ReflectionProperty::IS_PUBLIC);
        // Initialiser les éléments de requête et le tableau des paramètres.
        $strInsertColumns = $strInsertValues = $strUpdate = '';
        $params = [];
        // Pour chaque propriété, récupérer son nom pour construire une partie des requêtes SQL INSERT et UPDATE.
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $strInsertColumns .= "{$propertyName},";
            $strInsertValues .= ":{$propertyName},";
            $strUpdate .= "{$propertyName} = :{$propertyName},";
            $params[":{$propertyName}"] = $this->$propertyName;
        }
        // Supprimer la dernière virgule de chaque requête.
        $strInsertColumns = rtrim($strInsertColumns, ',');
        $strInsertValues = rtrim($strInsertValues, ',');
        $strUpdate = rtrim($strUpdate, ',');
        // Créer les requêtes SQL et les paramètres SQL.
        $strInsert = "INSERT INTO {$tableName} ({$strInsertColumns}) VALUES({$strInsertValues})";
        $strUpdate = "UPDATE {$tableName} SET {$strUpdate} WHERE {$pkName} = :__ID__";
        // Finir de compléter les tableaux de paramètres.
        $paramsInsert = $paramsUpdate = $params;
        $paramsUpdate[':__ID__'] = $this->$pkName;
        // Exécuter la requête INSERT OU UPDATE et si INSERT, récupérer la PK auto-incrémentée.
        $dbal = DBAL::get();
        $this->$pkName ? $dbal->xeq($strUpdate, $paramsUpdate) : $this->$pkName = $dbal->xeq($strInsert, $paramsInsert)->pk();
        // On retourne true systématiquement.
        return true;
    }

    /**
     * Supprime l'entité du système de stockage.
     *
     * @return boolean True ou false selon que la persistance ait réussi ou non.
     */
    function remove(): bool
    {
        // Déduire le nom de la table à partir de nom de la classe de l'entité $this.
        $className = (new ReflectionClass($this))->getShortName();
        $tableName = lcfirst($className);
        // Construire le nom de la PK à partir du nom de la classe.
        $pkName = "id{$className}";
        if (!$this->$pkName) {
            return false;
        }
        $q = "DELETE FROM {$tableName} WHERE {$pkName} = :__ID__";
        $params = [':__ID__' => $this->$pkName];
        // Exécuter la requête.
        return (bool) DBAL::get()->xeq($q, $params)->nb();
    }

    /**
     * Sélectionne des entités correspondants aux critères dans le système de stockage.
     * 
     * @param array $filters Tableau associatifs de filtres d'égalité reliées par AND sous la forme 'champ' = 'valeur'.
     * Ex : ['name' => 'truc', 'idCategory' => 3]
     * @param array $sortKeys Tableau associatif de clefs de tri sous la forme 'champ' => 'ASC | DESC'.
     * Ex : ['name' => 'DESC', 'price' => 'ASC']
     * @param string $limit Limite de la sélection.
     * Ex : '2,4' signifie 4 entités à partir de la 3ème (incluse)
     * @return array Tableau d'instances implémentant ORM.
     */
    static function findAllBy(array $filters = [], array $sortKeys = [], string $limit = ''): array
    {
        // Déduire le nom de la table à partir de nom de la classe de l'entité $this.
        $className = (new ReflectionClass(static::class))->getShortName();
        $tableName = lcfirst($className);
        // Initialiser les requêtes SQL et les paramètres SQL.
        $q = "SELECT * FROM {$tableName}";
        $params = [];
        if ($filters) {
            // Si filtres, construire la clause WHERE.
            $q .= " WHERE";
            foreach ($filters as $col => $val) {
                $q .= " {$col} = :{$col} AND";
                $params[":{$col}"] = $val;
            }
            // Supprimer le dernier AND.
            $q = rtrim($q, ' AND');
        }
        if ($sortKeys) {
            // Si clefs de tri, construire la clause ORDER BY.
            $q .= " ORDER BY";
            foreach ($sortKeys as $col => $sortOrder) {
                $q .= " {$col} {$sortOrder},";
            }
            // Supprimer le dernier AND.
            $q = rtrim($q, ',');
        }
        if ($limit) {
            // Si limite, ajouter la clause LIMIT.
            $q .= " LIMIT {$limit}";
        }
        // Exécuter la requête et retourner le tableau.
        return DBAL::get()->xeq($q, $params)->findAll(static::class);
    }

    /**
     * Sélectionne une entité correspondant aux critères dans le système de stockage. Retourne une instance ou null.
     * 
     * @param array $filters Tableau associatifs de filtres dégalité reliées par AND sous la forme 'champ' = 'valeur'.
     * Ex : ['name' => 'truc', 'idCategory' => 3]
     * @return ORM|null L'instance implémentant ORM.
     */
    static function findOneBy(array $filters = []): ?ORM
    {
        return self::findAllBy($filters, [], '1')[0] ?? null;
    }

    /**
     * Retourne le résultat de l'invocation de la méthode get{PropertyName}() si elle existe.
     * Sinon retourne null.
     *
     * @param string $propertyName Nom de la propriété.
     * @return mixed Dépend de la classe enfant et de la propriété.
     */
    public function __get(string $propertyName): mixed
    {
        // Construire le nom de la méthode à invoquer.
        $methodName = 'get' . ucfirst($propertyName);
        // Tenter de l'invoquer.
        try {
            return $this->$methodName();
        } catch (Error $e) {
            return null;
        }
    }

    /**
     * Appelé par json_encode().
     * Spécifie les propriétés qui seront sérialisées en JSON.
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}

<?php

declare(strict_types=1);

namespace peps\core;

/**
 * Abstraction ORM de la persistance des entités.
 * Indépendante du type de système de stockage.
 */
interface ORM
{
    /**
     * Hydrate l'entité depuis le système de stockage.
     *
     * @return boolean True ou false selon que l'hydratation ait réussi ou non.
     */
    function hydrate(): bool;

    /**
     * Persiste l'entité vers le système de stockage.
     *
     * @return boolean True ou false selon que la persistance ait réussi ou non.
     */
    function persist(): bool;

    /**
     * Supprime l'entité du système de stockage.
     *
     * @return boolean True systématiquement.
     */
    function remove(): bool;

    /**
     * Sélectionne des entités correspondants aux critères dans le système de stockage.
     * 
     * @param array $filters Tableau associatifs de filtres dégalité reliées par AND sous la forme 'champ' = 'valeur'.
     * Ex : ['name' => 'truc', 'idCategory' => 3]
     * @param array $sortKeys Tableau associatif de clefs de tri sous la forme 'champ' => 'ASC | DESC'.
     * Ex : ['name' => 'DESC', 'price' => 'ASC']
     * @param string $limit Limite de la sélection.
     * Ex : '2,4' signifie 4 entités à partir de la 3ème (incluse)
     * @return array Tableau d'instances implémentant ORM.
     */
    static function findAllBy(array $filters = [], array $sortKeys = [], string $limit = ''): array;

    /**
     * Sélectionne une entité correspondant aux critères dans le système de stockage. Retourne une instance ou null.
     * 
     * @param array $filters Tableau associatifs de filtres dégalité reliées par AND sous la forme 'champ' = 'valeur'.
     * Ex : ['name' => 'truc', 'idCategory' => 3]
     * @return ORM|null L'instance implémentant ORM.
     */
    static function findOneBy(array $filters = []): ?ORM;

    /**
     * Retourne le résultat de l'invocation de la méthode get{PropertyName}() si elle existe.
     * Sinon retourne null.
     *
     * @param string $propertyName Nom de la propriété.
     * @return mixed Dépend de la classe enfant et de la propriété.
     */
    function __get(string $propertyName): mixed;
}

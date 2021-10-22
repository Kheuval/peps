<?php

declare(strict_types=1);

namespace peps\core;

/**
 * Interface de validation des entités.
 * DEVRAIT être implémentéee par les classes entités pour valider les données qu'elles contiennent typiquement avant persistance.
 */
interface Validator
{
    /**
     * Vérifie si l'entité contient des données valides (typiquement avant persistance).
     *
     * @var string[] $errors Tableau des messages d'erreur passé par référence.
     * @return boolean True ou false selon que les données sont valides ou non.
     */
    function validate(array &$errors = []): bool;
}

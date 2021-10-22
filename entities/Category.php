<?php

declare(strict_types=1);

namespace entities;

use peps\core\DBAL;
use peps\core\ORMDB;

/**
 * Entité Category.
 * Toutes les propriétés à null par défaut pour les formulaires de saisie.
 * 
 * @see DBAL
 * @see ORMDB
 */
class Category extends ORMDB
{
    /**
     * PK de la catégorie.
     */
    public ?int $idCategory = null;

    /**
     * Nom de la catégorie.
     */
    public ?string $name = null;

    /**
     * Tableau contenant les produits de la catégorie.
     */
    protected ?array $products = null;

    /**
     * Constructeur qui reçoit l'id du produit.
     */
    public function __construct(int $idCategory = null)
    {
        $this->idCategory = $idCategory;
    }

    /**
     * Retourne un tableau des produits (triés par nom) de cette catégorie.
     * Lazy loading.
     * 
     * @return Product[] Tableau des produits.
     */
    protected function getProducts(): array
    {
        if (empty($this->products)) {
            $this->products = Product::findAllBy(['idCategory' => $this->idCategory], ['name' => 'ASC']);
        }
        return $this->products;
    }
}

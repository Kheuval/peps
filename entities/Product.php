<?php

declare(strict_types=1);

namespace entities;

use peps\core\DBAL;
use peps\core\ORMDB;
use peps\core\Validator;

/**
 * Entité Product.
 * Toutes les propriétés à null par défaut pour les formulaires de saisie.
 * 
 * @see DBAL
 * @see ORMDB
 */
class Product extends ORMDB implements Validator
{
    // Messages d'erreur.
    protected const ERR_INVALID_PK = "Clé primaire invalide";
    protected const ERR_INVALID_CATEGORY = "Catégorie invalide";
    protected const ERR_INVALID_NAME = "Nom invalide";
    protected const ERR_INVALID_REF = "Référence invalide";
    protected const ERR_REF_ALREADY_EXISTS = "Référence déjà existante";
    protected const ERR_INVALID_PRICE = "Prix invalide";

    /**
     * PK du produit.
     */
    public ?int $idProduct = null;

    /**
     * FK de la catégorie du produit.
     */
    public ?int $idCategory = null;

    /**
     * Nom du produit.
     */
    public ?string $name = null;

    /**
     * Référence du produit.
     */
    public ?string $ref = null;

    /**
     * Prix du produit.
     */
    public ?float $price = null;

    /**
     * Catégorie du produit.
     */
    protected ?Category $category = null;

    /**
     * Constructeur qui reçoit l'id du produit.
     */
    public function __construct(int $idProduct = null)
    {
        $this->idProduct = $idProduct;
    }

    /**
     * Méthode pour récupérer la catégorie correspondant au produit et la retourner.
     * Lazy loading.
     * 
     * @return Category L'instance de Category du produit.
     */
    protected function getCategory(): Category
    {
        if (empty($this->category)) {
            // Solution 1 :
            $this->category = Category::findOneBy(['idCategory' => $this->idCategory]);
            // Solution 2 :
            // $category = new Category($this->idCategory);
            // $this->category = $category->hydrate() ? $category : null;
        }
        return $this->category;
    }

    /**
     * {@inheritDoc}
     *
     * @return boolean True ou false selon que les données sont valides ou non.
     */
    public function validate(array &$errors = []): bool
    {
        $valid = true;
        // Si présent, vérifier idProduct (PK) et son existence en DB.
        if ($this->idProduct && ($this->idProduct < 1 || !(new Product($this->idProduct))->hydrate())) {
            $valid = false;
            $errors[] = self::ERR_INVALID_PK;
        }
        // Vérifier idCategory (PK obligatoire) et son existence en DB.
        if (!$this->idCategory || $this->idCategory < 1 || !(new Category($this->idCategory))->hydrate()) {
            $valid = false;
            $errors[] = self::ERR_INVALID_CATEGORY;
        }
        // Vérifier le nom (obligatoire et max 50 caractères)
        if (!$this->name || mb_strlen($this->name) > 50) {
            $valid = false;
            $errors[] = self::ERR_INVALID_NAME;
        }
        // Vérifier la référence (obligatoire et max 10 caractères).
        if (!$this->ref || mb_strlen($this->ref) > 10) {
            $valid = false;
            $errors[] = self::ERR_INVALID_REF;
        }
        // Vérifier l'unicité de la référence en DB.
        if ($this->refExists()) {
            $valid = false;
            $errors[] = self::ERR_REF_ALREADY_EXISTS;
        }
        // Vérifier le prix (obligatoire et > 0 et < 10000).
        if (!$this->price || $this->price <= 0 || $this->price >= 10000) {
            $valid = false;
            $errors[] = self::ERR_INVALID_PRICE;
        }
        return $valid;
    }

    /**
     * Vérifie si la référence existe déjà en DB ou non (sans tenir compte de $this lui-même).
     *
     * @return boolean True ou false selon que la référence existe déjà ou non.
     */
    protected function refExists(): bool
    {
        // Rechercher un éventuel doublon.
        $product = self::findOneBy(['ref' => $this->ref]);
        // Ne pas compter celui qui aurait le même idProduct.
        return (bool) $product && $this->idProduct != $product->idProduct;
    }
}

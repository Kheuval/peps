<?php

declare(strict_types=1);

namespace controllers;

use entities\Category;
use entities\Product;
use peps\core\DBAL;
use peps\core\Router;


/**
 * Classe 100% statique, contrôle les produits.
 */
final class ProductController
{
    /**
     * Constructeur privé.
     * Il est vide mais doit être créé pour pouvoir le passer en privé, sinon PHP en créé un automatiquement (en public).
     */
    private function __construct()
    {
    }

    /**
     * Affiche les produits par catégorie.
     * 
     * GET /
     * GET /product/list
     */
    public static function list(): void
    {
        // Récupérer toutes les catégories
        $categories = Category::findAllBy([], ['name' => 'ASC']);
        // Rendre la vue
        Router::render('listProducts.php', ['categories' => $categories]);
    }

    /**
     * Affiche le détail d'un produit.
     * 
     * GET /product/show/{idProduct}
     */
    public static function show(array $params): void
    {
        $product = new Product((int) $params['idProduct']);
        if (!$product->hydrate()) {
            Router::render('noProduct.php');
        }
        // Ajouter dynamiquement la propriété idImg.
        $product->idImg = file_exists("assets/img/product_{$product->idProduct}_big.jpg") ? $product->idProduct : 0;
        Router::render('showProduct.php', ['product' => $product]);
    }

    /**
     * Supprime un produit et/ou ses images.
     * 
     * GET /product/delete.{idProduct}/mode/{all | img}
     *
     * @param array $params Tableau associatif des paramètres.
     */
    public static function delete(array $params): void
    {
        // Récupérer l'idProduct.
        $idProduct = (int) $params['idProduct'];
        // Tenter de supprimer les images.
        @unlink("assets/img/product_{$idProduct}_small.jpg");
        @unlink("assets/img/product_{$idProduct}_big.jpg");
        // Si mode 'all', créer un produit pour pouvoir le supprimer.
        if ($params['mode'] === 'all') {
            (new Product($idProduct))->remove();
        }
        // Rediriger vers l'accueil (synchrone).
        // Router::redirect('/');
        // Faire un echo sans précision (asynchrone).
        Router::json(json_encode(''));
    }

    /**
     * Affiche le formulaire d'ajout d'un produit.
     * 
     * GET /product/create/([1-9][0-9]*)
     *
     * @param array $params Tableau associatif des paramètres.
     */
    public static function create(array $params): void
    {
        // Récupérer idCategory.
        $idCategory = (int) $params['idCategory'];
        // Créer un nouveau product.
        $product = new Product();
        // Renseigner son idCategory.
        $product->idCategory = $idCategory;
        // Récupérer toutes les catégories.
        $categories = Category::findAllBy([], ['name' => 'ASC']);
        // Rendre la vue.
        Router::render('editProduct.php', ['categories' => $categories, 'product' => $product]);
    }

    /**
     * Affiche le formulaire d'édition d'un produit.
     * 
     * GET /product/update/([1-9][0-9]*)
     *
     * @param array $params Tableau associatif des paramètres.
     */
    public static function update(array $params): void
    {
        // Récupérer idProduct et créer un nouveau product.
        $idProduct = (int) $params['idProduct'];
        $product = new Product($idProduct);
        // Hydrater le produit.
        if (!$product->hydrate()) {
            Router::render('noProduct.php');
        }
        // Récupérer toutes les catégories.
        $categories = Category::findAllBy([], ['name' => 'ASC']);
        // Rendre la vue.
        Router::render('editProduct.php', ['categories' => $categories, 'product' => $product]);
    }

    /**
     * Persiste le produit en ajout ou modification.
     * POST /product/save
     */
    public static function save(): void
    {
        //Initialiser le tableau des erreurs.
        $errors = [];
        // Créer le produit.
        $product = new Product();
        // Récupérer et filtrer les données POST.
        $product->idProduct = filter_input(INPUT_POST, 'idProduct', FILTER_VALIDATE_INT) ?: null;
        $product->idCategory = filter_input(INPUT_POST, 'idCategory', FILTER_VALIDATE_INT) ?: null;
        $product->name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) ?: null;
        $product->ref = filter_input(INPUT_POST, 'ref', FILTER_SANITIZE_STRING) ?: null;
        $product->price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT) ?: null;
        // Si les données sont valides, persister le produit et rediriger vers le détail du produit.
        if ($product->validate($errors)) {
            $product->persist();
            Router::redirect("/product/show/{$product->idProduct}");
        }
        // Sinon, rendre la même vue en lui passant le tableau des erreurs.
        $categories = Category::findAllBy([], ['name' => 'ASC']);
        Router::render('editProduct.php', ['categories' => $categories, 'product' => $product, 'errors' => $errors]);
    }
}

<?php

declare(strict_types=1);

namespace controllers;

use entities\Category;
use entities\Product;
use peps\core\DBAL;
use peps\core\Router;

/**
 * Classe 100% statique.
 * Contrôle les produits.
 */
final class TestController
{
	/**
	 * Constructeur privé.
	 */
	private function __construct()
	{
	}

	/**
	 * Méthode de test.
	 * 
	 * GET /testy
	 */
	public static function test(): void
	{
		$categories = Category::findAllBy([], ['name' => 'ASC']);
		foreach ($categories as $category) {
			$category->products;
		}
		Router::render('test.php', ['categories' => $categories]);
	}

	/**
	 * Méthode d'auto-complétion.
	 * 
	 * GET /testy/autocomplete/{value}
	 */
	public static function autocomplete(array $params): void
	{
		// Récupérer la value.
		$value = $params['value'];
		// Si non-vide récupérer les produits correspondants.
		if (!empty($value)) {
			// Exécuter la requête.
			$q = "SELECT * FROM product WHERE name LIKE :value ORDER BY name";
			$paramsSQL = [':value' => "%{$value}%"];
			$products = DBAL::get()->xeq($q, $paramsSQL)->findAll(Product::class);
		} else {
			// Sinon, retourner un tableau vide.
			$products = [];
		}
		// Envoyer le tableau encodé en JSON.
		Router::json(json_encode($products));
	}
}

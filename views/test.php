<?php

declare(strict_types=1);

namespace views;

use peps\core\Cfg;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<title><?= Cfg::get('appTitle') ?> | Éditer un produit</title>
	<link rel="stylesheet" href="/assets/css/acme.css">
</head>

<body>
	<a href="/">
		<header></header>
	</a>
	<main>
		<div class="category">
			<a href="/product/list">Produits</a> &gt; Editer
		</div>
		<div class="error"><?= implode('<br>', $errors ?? []) ?></div>
		<form name="form1" action="/product/save" method="POST">
		<input  name="idProduct" value="" />
			<div class="item">
				<label>Catégorie</label>
				<input name="idCategory" value="">
			</div>
			<div class="item">
				<label>Nom</label>
				<input name="name"  value=""/>
			</div>
			<div class="item">
				<label>Référence</label>
				<input name="ref" value=""/>
			</div>
			<div class="item">
				<label>Prix</label>
				<input name="price" value=""  />
			</div>
			<div class="item">
				<label></label>
				<a href="/product/list"><input type="button" value="Annuler" /></a>
				<input type="submit" value="Valider" />
			</div>
		</form>
	</main>
	<footer></footer>
</body>

</html>
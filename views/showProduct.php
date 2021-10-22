<?php

declare(strict_types=1);

namespace views;

use peps\core\Cfg;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/acme.css">
    <title><?= Cfg::get('appTitle') ?> | Produits | <?= $product->name ?></title>
</head>

<body>
    <a href="/">
        <header>
        </header>
    </a>
    <main>
        <div class="category">
            <a href="/product/list">Produits </a>&gt; <?= $product->name ?>
        </div>
        <div id="detailProduct">
            <img src="/assets/img/product_<?= $product->idImg ?>_big.jpg" alt="Image <?= $product->name ?>">
            <div>
                <div class="price"><?= Cfg::get('NF_LOCALE_2DEC')->format($product->price) ?> €</div>
                <div class="category">Catégorie <br>
                    <?= $product->category->name ?>
                </div>
                <div class="ref">Référence <br>
                    <?= $product->ref ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
    </footer>
</body>

</html>
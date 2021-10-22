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
    <title><?= Cfg::get('appTitle') ?> | Produits</title>
</head>

<body>
    <a href="/">
        <header>
        </header>
    </a>
    <main>
        <?php foreach ($categories as $category) { ?>
            <div class="category">
                <a href="/product/create/<?= $category->idCategory ?>">
                    <img src="/assets/img/ico_create.svg" alt="Create" class="ico">
                </a>
                <?= $category->name ?>
            </div>
            <?php foreach ($category->products as $product) { ?>
                <!-- Ajouter dynamiquement la propriété idImg. -->
                <?php $product->idImg = file_exists("assets/img/product_{$product->idProduct}_small.jpg") ? $product->idProduct : 0; ?>
                <div class="blockProduct">
                    <a href="/product/show/<?= $product->idProduct ?>">
                        <img class="thumbnail" src="/assets/img/product_<?= $product->idImg ?>_small.jpg" alt="Image <?= $product->name ?>">
                        <div class="name"><?= $product->name ?></div>
                    </a>
                    <a href="/product/update/<?= $product->idProduct ?>" class="ico update"><img src="/assets/img/ico_update.svg" alt="Update"></a>
                    <img src="/assets/img/ico_delete.svg" alt="Delete" class="ico delete" onclick="deleteAll(<?= $product->idProduct ?>)">
                    <img src="/assets/img/ico_deleteImg.svg" alt="Delete" class="ico deleteImg" onclick="deleteImg(<?= $product->idProduct ?>)">
                </div>
            <?php } ?>
        <?php } ?>
    </main>
    <footer>
    </footer>
    <script src="/assets/js/listProducts.js"></script>
</body>

</html>
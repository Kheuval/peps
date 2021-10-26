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
    <title><?= Cfg::get('appTitle') ?> | Produit non trouvé</title>
</head>

<body>
    <?php require 'views/inc/header.php' ?>
    <main>
        <div class="category">
            <a href="/">Accueil </a> &gt; Produit indisponible
        </div>
        <img src="/assets/img/coyote404.jpg" alt="Not found">
    </main>
    <footer>
    </footer>
</body>

</html>
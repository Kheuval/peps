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
    <title><?= Cfg::get('appTitle') ?> | Oups...</title>
</head>

<body>
    <a href="/">
        <header>
        </header>
    </a>
    <main>
        <div class="category">
            <a href="/">Accueil </a> &gt; Oups...
        </div>
        <img src="/assets/img/error404.png" alt="Oups">
    </main>
    <footer>
    </footer>
</body>

</html>
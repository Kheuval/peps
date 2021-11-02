<?php

declare(strict_types=1);

namespace views;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/acme.css">
    <title>Mot de passe oublié</title>
</head>

<body>
    <?php require 'views/inc/header.php' ?>
    <main>
        <div class="category">
            <a href="/">Accueil </a> &gt; Mot de passe oublié
        </div>
        <div class="error"><?= implode('<br />', $errors ?? []) ?></div>
        <form name="form1" action="/user/newPwd" method="POST">
            <div class="item">
                <label>Identifiant</label>
                <input name="log" value="<?= $log ?>" size="20" maxlength="50" required="required" />
            </div>
            <div class="item">
                <input type="submit" value="Envoyer" />
            </div>
        </form>
    </main>
    <footer></footer>
</body>

</html>
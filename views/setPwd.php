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
    <title>Nouveau mot de passe</title>
</head>

<body>
    <?php require 'views/inc/header.php' ?>
    <main>
        <div class="category">
            <a href="/">Accueil </a> &gt; Nouveau mot de passe
        </div>
        <div class="error"><?= implode('<br />', $errors ?? []) ?></div>
        <form name="form1" action="/user/savePwd" method="POST">
            <input type="hidden" value="<?= $hash ?>" name="hash">
            <div class="item">
                <label>Mot de passe</label>
                <input type="password" name="pwd" size="20" required="required" />
            </div>
            <div class="item">
                <label></label>
                <input type="submit" value="Envoyer" />
            </div>
        </form>
    </main>
    <footer></footer>
</body>

</html>
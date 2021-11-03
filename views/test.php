<?php

declare(strict_types=1);

namespace views;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/acme.css">
    <title>Test</title>
</head>

<body>
    <main>
        <div class="category">
            <input id="autocomplete" type="text" oninput="test()">
            <div id="out"></div>
        </div>
        <div class="category">
            <select name="categories" onchange="select()" id="selectCategories">
                <option value="0">Choisissez une catégorie</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?= $category->idCategory ?>"><?= $category->name ?></option>
                <?php } ?>
            </select>
            <div id="outy"></div>
        </div>
    </main>
    <script src="../assets/js/test.js"></script>
    <script>
        let categories = <?= json_encode($categories) ?>
    </script>
    <script src="../assets/js/select.js"></script>
</body>

</html>
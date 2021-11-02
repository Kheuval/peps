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
    </main>
    <script src="../assets/js/test.js"></script>
</body>

</html>
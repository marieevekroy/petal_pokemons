<?php

declare(strict_types=1);

use Controller\Pokemon;

require("vendor/autoload.php");

// Custom router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if ($uri[3] != "pokemons") {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$pokemonController = new Pokemon();
$pokemonController->processRequest();

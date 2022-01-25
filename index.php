<?php

declare(strict_types=1);

require("vendor/autoload.php");

// Custom router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

//var_dump($uri); die();

if ($uri[3] != "pokemons") {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$pokemonController = new \Controller\Pokemon();
$pokemonController->processRequest();

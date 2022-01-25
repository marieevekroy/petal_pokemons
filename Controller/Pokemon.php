<?php

declare(strict_types=1);

namespace Controller;

use \Model\Pokemon as PokemonModel;

class Pokemon
{

    private string $requestMethod;

    private ?int $pokemonId;

    private ?string $pokemonName;

    private PokemonModel $pokemonModel;

    public function __construct()
    {
        $this->requestMethod = $_SERVER["REQUEST_METHOD"];
        $this->pokemonModel = new PokemonModel();

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        // We have the choice to receive an {id} or {name}
        if ($pokemonIdParam = $uri[4]) {
            if (is_numeric($pokemonIdParam)) {
                $this->pokemonId = intval($pokemonIdParam);
            } else {
                $this->pokemonName = addslashes(strip_tags($pokemonIdParam));
            }
        }
    }

    public function processRequest(): void
    {
        // Validate request method
        switch ($this->requestMethod) {
            case 'GET':
                if (isset($this->pokemonId)) {
                    $this->getById();
                } else if (isset($this->pokemonName)) {
                    $this->getByName();
                } else {
                    $this->list();
                };
                break;
            case 'POST':
                $this->createPokemon($this->pokemonName);
                break;
            case 'PUT':
                if (!isset($this->pokemonName)) {
                    $this->sendOutput(
                        json_encode("Invalid input. Missing {name}"),
                        ["HTTP/1.1 400 Bad Request"]
                    );
                }
                $this->updatePokemon($this->pokemonName);
                break;
            case 'DELETE':
                if (!isset($this->pokemonName)) {
                    $this->sendOutput(
                        json_encode("Invalid input. Missing {name}"),
                        ["HTTP/1.1 400 Bad Request"]
                    );
                }
                $this->deletePokemon($this->pokemonName);
                break;
            default:
                $this->sendOutput(
                    json_encode("Method not supported"),
                    ["HTTP/1.1 422 Unprocessable Entity"]
                );
                break;
        }
    }

    private function list(): void
    {
        // Default values will be 10 per page
        parse_str($_SERVER['QUERY_STRING'], $params);
        $limit = isset($params['limit'])?intval($params['limit']):10;
        $page = isset($params['page'])?intval($params['page']):0;

        $pokemons = $this->pokemonModel->getAll($limit, $page);

        $this->sendOutput(
            json_encode($pokemons),
            ["HTTP/1.1 200 OK"]
        );
    }

    private function getById()
    {
        $pokemon = $this->pokemonModel->getById($this->pokemonId);

        if (!$pokemon) {
            $this->sendOutput(
                json_encode("Entity Not Found"),
                ["HTTP/1.1 404 Not Found"]
            );
        }

        $this->sendOutput(
            json_encode($pokemon),
            ["HTTP/1.1 200 OK"]
        );
    }

    private function getByName()
    {
        $pokemon = $this->pokemonModel->getByName($this->pokemonName);

        if (!$pokemon) {
            $this->sendOutput(
                json_encode("Entity Not Found"),
                ["HTTP/1.1 404 Not Found"]
            );
        }

        $this->sendOutput(
            json_encode($pokemon),
            ["HTTP/1.1 200 OK"]
        );
    }

    private function createPokemon()
    {
        // Validate the entity is not already created
        if ($this->pokemonModel->getByName($this->pokemonName)) {
            $this->sendOutput(
                json_encode("Invalid input. Entity already exists"),
                ["HTTP/1.1 422 Unprocessable Entity"]
            );
        }

        $body = (array)json_decode(file_get_contents('php://input'), TRUE);
        $body["Name"] = $this->pokemonName;

        // Validate the base name of the Pokemon (without evolution)
        // and retrieve its id
        // This will prevent to create a different id for the same type of pokemon
        preg_match(
            "/(.*)Mega/",
            $this->pokemonName,
            $matches
        );

        if ($matches[1]) {
            $basePokemon = $this->pokemonModel->getByName($matches[1]);
            $body["#"] = $basePokemon["#"];
        }

        $this->pokemonModel->insert($body);
        $this->sendOutput(
            json_encode("Success"),
            ["HTTP/1.1 201 Created"]
        );
    }

    private function updatePokemon()
    {
        // Validate the entity is not already created
        $existingPokemon = $this->pokemonModel->getByName($this->pokemonName);
        if (!$existingPokemon) {
            $this->sendOutput(
                json_encode("Entity Not Found"),
                ["HTTP/1.1 404 Not Found"]
            );
        }

        $body = (array)json_decode(file_get_contents('php://input'), TRUE);
        $body["Name"] = $this->pokemonName;
        $body = array_merge($existingPokemon, $body);
        $this->pokemonModel->update($body);

        $this->sendOutput(
            json_encode("Success"),
            ["HTTP/1.1 200 OK"]
        );
    }

    private function deletePokemon()
    {
        // Validate entity exists
        if (!$this->pokemonModel->getByName($this->pokemonName)) {
            $this->sendOutput(
                json_encode("Not found. Entity does not exist"),
                ["HTTP/1.1 404 Not Found"]
            );
        }

        $this->pokemonModel->deleteByName($this->pokemonName);
        $this->sendOutput(
            json_encode("Success"),
            ["HTTP/1.1 200 OK"]
        );
    }

    private function sendOutput($data, $headers): void
    {
        // Define headers
        header('Content-Type: application/json');
        foreach ($headers as $header) {
            header($header);
        }

        // Output data
        echo $data;

        exit();
    }
}
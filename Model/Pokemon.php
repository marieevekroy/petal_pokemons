<?php

declare(strict_types=1);

/**
 * The model will handle all the business logic around the data
 */

namespace Model;

use Model\PokemonResourceModel;

class Pokemon {

    public int $id;

    public string $name;

    private PokemonResourceModel $resourceModel;

    public function __construct(){
        // Associate resource model (csv file)
        $this->resourceModel = new PokemonResourceModel("pokemons.csv");
    }

    public function getAll($limit = 10, $page = 0):array {
        $data = $this->resourceModel->getAll($limit, $page);
        return $data;
    }

    public function getById(int $id):array{
        $data = $this->resourceModel->getById($id);
        return $data;
    }

    public function getByName(string $name):array {
        $data = $this->resourceModel->getByName($name);
        return $data;
    }

    public function insert($data) {
        if (!is_numeric($data["#"])){
            $data["#"] = $this->resourceModel->incrementId;
        }
        // find next increment id from csv
        $formattedData = $this->reformatData($data);
        $this->resourceModel->insert($formattedData);
    }

    public function update($data) {
        $formattedData = $this->reformatData($data);
        $this->resourceModel->update($formattedData);
    }

    public function deleteByName($name):void{
        $this->resourceModel->deleteByName($name);
    }

    private function reformatData($data):array {
        $formattedData = [];
        $fields = $this->resourceModel->getHeaders();
        foreach($fields as $field) {
            if ($value = $data[$field]) {
                $formattedData[$field] = $value;
            } else {
                $formattedData[$field] = null;
            }
        }
        return $formattedData;
    }
}
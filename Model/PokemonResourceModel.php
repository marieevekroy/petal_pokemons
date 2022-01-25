<?php

declare(strict_types=1);

/**
 * Usually Resource Models only purpose is to interact with the database
 * In our case, it will only read/write in our csv file
 */

namespace Model;

class PokemonResourceModel
{

    private string $filename;

    public array $csvHeaders;

    public int $incrementId;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->csvHeaders = $this->getHeaders();
        $this->incrementId = $this->getIncrementId();
    }

    public function getAll($limit, $page)
    {
        if (!($fopen = fopen("csv/" . $this->filename, "r"))) {
            throw new \ErrorException("Invalid filename");
        }
        $pokemons = [];
        $line = 0;
        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            if (is_numeric($data[0])) {
                if ($line >= ($page * $limit) && $line < (($page + 1) * $limit)) {
                    $singlepokemon = [];
                    foreach ($data as $index => $value) {
                        $singlepokemon[$this->csvHeaders[$index]] = $value;
                    }
                    $pokemons[] = $singlepokemon;
                }
                $line++;
            }
        }
        fclose($fopen);

        return $pokemons;
    }

    // Pokemon id is NOT unique
    // can return multiple results
    public function getById(int $id)
    {
        // mode read
        if (!($fopen = fopen("csv/" . $this->filename, "r"))) {
            throw new \ErrorException("Invalid filename");
        }
        $pokemon = [];
        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            if ($data[0] == $id) {
                foreach ($data as $index => $value) {
                    $pokemon[$this->csvHeaders[$index]] = $value;
                }
            }
        }
        fclose($fopen);

        return $pokemon;
    }

    // Pokemon name is unique and returns only one result
    public function getByName(string $name)
    {
        // mode read
        if (!($fopen = fopen("csv/" . $this->filename, "r"))) {
            throw new \ErrorException("Invalid filename");
        }
        $pokemon = [];
        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            if ($data[1] == $name) {
                foreach ($data as $index => $value) {
                    $pokemon[$this->csvHeaders[$index]] = $value;
                }
                break;
            }
        }
        fclose($fopen);

        return $pokemon;
    }

    public function insert($data)
    {
        // mode append
        if (!($fopen = fopen("csv/" . $this->filename, "a"))) {
            throw new \ErrorException("Invalid filename or not writable file");
        }

        fputcsv($fopen, $data);
        fclose($fopen);
    }

    // impossible to update a single csv line
    // rebuild csv data
    public function update($pokemon)
    {
        if (!($fopen = fopen("csv/" . $this->filename, "r"))) {
            throw new \ErrorException("Invalid filename or wrong permissions");
        }

        $allData = [];
        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            $newData = [];
            foreach ($data as $index => $value) {
                if ($data[1] == $pokemon["Name"]) {
                    $newData = $pokemon;
                } else {
                    $newData[$this->csvHeaders[$index]] = $value;
                }
            }
            $allData[] = $newData;
        }
        fclose($fopen);

        // rewrite file
        $fopen = fopen("csv/" . $this->filename, "w");
        foreach ($allData as $data) {
            fputcsv($fopen, $data);
        }
        fclose($fopen);
    }

    // cannot modify middle of a csv file
    // rebuild csv data
    public function deleteByName($name)
    {
        if (!($fopen = fopen("csv/" . $this->filename, "rw"))) {
            throw new \ErrorException("Invalid filename or not writable file");
        }

        $allData = [];
        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            $newData = [];
            foreach ($data as $index => $value) {
                if ($data[1] == $name) {
                    continue;
                } else {
                    $newData[$this->csvHeaders[$index]] = $value;
                }
            }
            $allData[] = $newData;
        }
        fclose($fopen);

        // rewrite file
        $fopen = fopen("csv/" . $this->filename, "w");
        foreach ($allData as $data) {
            fputcsv($fopen, $data);
        }
        fclose($fopen);
    }

    public function getHeaders(): array
    {
        if (!($fopen = fopen("csv/" . $this->filename, "r"))) {
            throw new \ErrorException("Invalid filename");
        }

        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            if (!is_numeric($data[0])) {
                return $data;
            }
        }
    }

    private function getIncrementId(){
        $maxId = 0;
        if (!($fopen = fopen("csv/" . $this->filename, "r"))) {
            throw new \ErrorException("Invalid filename");
        }

        while (($data = fgetcsv($fopen, 1000, ",")) !== FALSE) {
            if (is_numeric($data[0]) && $data[0] > $maxId) {
                $maxId = $data[0];
            }
        }

        return $maxId+1;
    }
}
<?php

namespace App\Reader;

use League\Csv\Reader;
use League\Csv\Statement;

class Csv
{
    private $clients = [];

    public function createArrayFrom(string $file): array
    {
        if (is_file($file) === false) {
            throw new \Exception($file . ' is not a valid file');
        }

        $array = [];

        $reader = Reader::createFromPath($file, 'r');
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        $stmt = (new Statement())->where(function (array $record) {
                return empty($this->clients) || in_array($record["Nom Client"], $this->clients);
            });

        $records = $stmt->process($reader);

        foreach ($records as $record) {
            $numero_facture = $record["numero_facture"];
            if (array_key_exists($numero_facture, $array) === false) {
                $array[$numero_facture] = [
                    'client' => $record["Nom Client"],
                    'presta' => []
                ];
            }

            $array[$numero_facture]['presta'][$record["Intitule ligne"]] = [
                'prix' => $record['Prix unitaire'],
                'qte' => $record['Nombre jours'],
                'total' => $record['Total HT']
            ];
        }

        return $array;
    }

    public function setClients(?string $clients): void
    {
        $this->clients = (empty($clients)) ? [] : explode(',', $clients);
    }
}
<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use SimpleXMLElement;
use VitesseCms\Spreadshirt\DTO\PrintTypeDTO;
use VitesseCms\Spreadshirt\Factories\PrintTypeApiFactory;
use VitesseCms\Spreadshirt\Models\PrintTypeApi;

class PrintTypeHelper extends AbstractSpreadShirtHelper
{
    public function getById(int $id): PrintTypeApi
    {
        return PrintTypeApiFactory::createFromXml($this->get($id));
    }

    public function get(int $id): PrintTypeDTO
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'printTypes/' . $id, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new PrintTypeDTO(json_decode($result));
    }

    public function getAll(): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'printTypes?fullData=true', 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }
}

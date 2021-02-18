<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use VitesseCms\Spreadshirt\Factories\PrintTypeApiFactory;
use VitesseCms\Spreadshirt\Models\PrintTypeApi;
use \SimpleXMLElement;

class PrintTypeHelper extends AbstractSpreadShirtHelper
{
    public function getById(int $id): PrintTypeApi
    {
        return PrintTypeApiFactory::createFromXml($this->get($id));
    }

    public function get(int $id): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'printTypes/' . $id, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }

    public function getAll(): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'printTypes?fullData=true', 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }
}

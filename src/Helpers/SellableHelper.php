<?php

namespace VitesseCms\Spreadshirt\Helpers;

use VitesseCms\Spreadshirt\DTO\SellablesDTO;

class SellableHelper extends AbstractSpreadShirtHelper
{
    public function getAll(): SellablesDTO
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'sellables', 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new SellablesDTO(json_decode($result));
    }
}

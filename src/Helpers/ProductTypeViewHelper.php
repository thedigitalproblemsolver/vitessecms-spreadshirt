<?php

namespace VitesseCms\Spreadshirt\Helpers;

/**
 * Class ProductTypeHelper
 */
class ProductTypeViewHelper extends AbstractSpreadShirtHelper
{
    /**
     * @param int $productTypeId
     * @param int $id
     *
     * @return \SimpleXMLElement
     */
    public function get(int $productTypeId, int $id): \SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'productTypes/'.$productTypeId.'/Views/'.$id,'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new \SimpleXMLElement($result);
    }
}

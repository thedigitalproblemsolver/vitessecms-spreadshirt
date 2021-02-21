<?php

namespace VitesseCms\Spreadshirt\Helpers;

/**
 * Class ProductTypeHelper
 */
class ProductTypeHelper extends AbstractSpreadShirtHelper
{
    /**
     * @param int $id
     *
     * @return \SimpleXMLElement
     */
    public function get(int $id): \SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'productTypes/' . $id, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new \SimpleXMLElement($result);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getAll(): \SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'productTypes?fullData=true&limit=200', 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new \SimpleXMLElement($result);
    }

    /**
     * @param \SimpleXMLElement $productType
     * @param array $namespaces
     *
     * @return string
     */
    public function buildSizeTable(
        \SimpleXMLElement $productType,
        array $namespaces
    ): string
    {
        $sizeImage = (string)$productType->resources->resource[1]->attributes($namespaces['xlink']);
        $sizeRows = [];
        foreach ($productType->sizes->size as $size) :
            if (!isset($sizeRows[0])) :
                $sizeRows[0] = '<th></th>';
            endif;
            $sizeRows[0] .= '<th>' . strtoupper((string)$size->name) . '</th>';
            $row = 1;
            foreach ($size->measures->measure as $measure) :
                if (!isset($sizeRows[$row])) :
                    $sizeRows[$row] = '<th>' . (string)$measure->name . '</th>';
                endif;
                $sizeRows[$row] .= '<td>' . number_format((float)$measure->value / 10, 1) . '</td>';
                $row++;
            endforeach;
        endforeach;
        $return = '<table class="size-table">
            <tr>
                <td>
                    <img src="' . $sizeImage . '" alt="%SHOP_SIZE_TABLE% ' . (string)$productType->name . '" />
                </td>
                <td>
                <table>';
        foreach ($sizeRows as $row) :
            $return .= '<tr>' . $row . '</tr>';
        endforeach;
        $return .= '</table>
                </td>
            </tr>
        </table>';

        return $return;
    }
}

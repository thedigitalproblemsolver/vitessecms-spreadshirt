<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use VitesseCms\Spreadshirt\DTO\ProductTypeDTO;
use VitesseCms\Spreadshirt\DTO\ProductTypesDTO;

class ProductTypeHelper extends AbstractSpreadShirtHelper
{
    public function get(int $id): ProductTypeDTO
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'productTypes/' . $id, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new ProductTypeDTO(json_decode($result));
    }

    public function getAll(): ProductTypesDTO
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'productTypes?fullData=true&limit=200', 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new ProductTypesDTO(json_decode($result));
    }

    public function buildSizeTable(ProductTypeDTO $productTypeDTO): string
    {
        $sizeImage = $productTypeDTO->sizeImage;
        $sizeRows = [];
        foreach ($productTypeDTO->sizes as $size) :
            if (!isset($sizeRows[0])) :
                $sizeRows[0] = '<th></th>';
            endif;
            $sizeRows[0] .= '<th>' . strtoupper($size->name) . '</th>';
            $row = 1;
            foreach ($size->measures as $measure) :
                if (!isset($sizeRows[$row])) :
                    $sizeRows[$row] = '<th>' . $measure->name . '</th>';
                endif;
                $sizeRows[$row] .= '<td>' . $measure->value->value . '</td>';
                $row++;
            endforeach;
        endforeach;
        $return = '<table class="size-table">
            <tr>
                <td>
                    <img src="' . $sizeImage . '" alt="%SHOP_SIZE_TABLE% ' . $productTypeDTO->name . '" />
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

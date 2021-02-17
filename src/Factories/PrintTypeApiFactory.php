<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Spreadshirt\Models\Color;
use VitesseCms\Spreadshirt\Models\ColorIterator;
use VitesseCms\Spreadshirt\Models\PrintTypeApi;
use SimpleXMLElement;

class PrintTypeApiFactory {

    public static function createFromXml(SimpleXMLElement $simpleXMLElement): PrintTypeApi
    {
        $colors = new ColorIterator();
        foreach($simpleXMLElement->colors->color as $key => $value) :
            $id = (int)XmlUtil::getAttribute($value,'id');
            $value = (array)$value;

            $colors->add((new Color())
                ->setId($id)
                ->setName((string)$value['name'])
                ->setHex((string)$value['fill'])
            );
        endforeach;

        return (new PrintTypeApi())
            ->setColors($colors)
            ;
    }
}

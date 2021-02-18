<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Spreadshirt\Models\ProductTypeAppearance;
use \SimpleXMLElement;

class ProductTypeAppearanceFactory
{
    public static function createFromXml(SimpleXMLElement $simpleXMLElement): ProductTypeAppearance
    {
        $colors = [];
        foreach ($simpleXMLElement->colors as $key => $value) :
            $value = (array)$value;
            if (is_array($value['color'])) :
                $colors = $value['color'];
            else :
                $colors[] = $value['color'];
            endif;
        endforeach;

        return (new ProductTypeAppearance())
            ->setId((int)XmlUtil::getAttribute($simpleXMLElement, 'id'))
            ->setName((string)$simpleXMLElement->name)
            ->setColors($colors);
    }
}

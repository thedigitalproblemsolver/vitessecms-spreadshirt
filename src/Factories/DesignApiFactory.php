<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Spreadshirt\Models\DesignApi;
use \SimpleXMLElement;

class DesignApiFactory
{
    public static function createFromXml(SimpleXMLElement $simpleXMLElement): DesignApi
    {
        $colors = $colorIds = [];
        foreach ($simpleXMLElement->colors as $key => $value) :
            $value = (array)$value;
            if (is_object($value['color'])) {
                $colors[] = (string)$value['color']->default;
            }
            if (is_array($value['color'])) {
                foreach ($value['color'] as $color) :
                    $colors[] = (string)$color->default;
                endforeach;
            }
        endforeach;

        return (new DesignApi())
            ->setId((int)XmlUtil::getAttribute($simpleXMLElement, 'id'))
            ->setFileExtension((string)$simpleXMLElement->fileExtension)
            ->setColors($colors)
            ->setColorIds($colorIds);
    }
}

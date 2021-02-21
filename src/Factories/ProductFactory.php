<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\Product;

/**
 * Class ProductFactory
 */
class ProductFactory
{

    /**
     * @param string $productTypeId
     * @param string $productTypePrintAreaId
     * @param string $designId
     * @param string $printTypeId
     * @param float $scale
     *
     * @return Product
     */
    public static function create(
        string $productTypeId,
        string $productTypePrintAreaId,
        string $designId,
        string $printTypeId,
        float $scale = 1
    ): Product
    {
        return (new Product())
            ->set('productType', $productTypeId)
            ->set('productTypePrintAreaId', $productTypePrintAreaId)
            ->set('design', $designId)
            ->set('printTypeId', $printTypeId)
            ->set('scale', $scale);
    }
}

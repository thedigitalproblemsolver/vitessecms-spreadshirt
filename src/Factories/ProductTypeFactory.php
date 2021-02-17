<?php

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\ProductType;

/**
 * Class ProductTypeFactory
 */
class ProductTypeFactory {

    /**
     * @param string $name
     * @param int $productTypeId
     *
     * @return ProductType
     */
    public static function create(
        string $name,
        int $productTypeId
    ) : ProductType {
        return (new ProductType())
            ->set('name',$name, true)
            ->set('productTypeId',$productTypeId)
        ;
    }
}

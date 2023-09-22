<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\ProductType;

class ProductTypeFactory
{
    public static function create(
        string $name,
        int $productTypeId
    ): ProductType {
        return (new ProductType())
            ->set('name', $name, true)
            ->set('productTypeId', $productTypeId);
    }
}

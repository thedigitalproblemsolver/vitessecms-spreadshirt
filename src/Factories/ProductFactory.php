<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\Product;

final class ProductFactory
{
    public static function create(
        string $name,
        string $productTypeId,
        string $designId,
        bool $published = false
    ): Product {
        $product = new Product();
        $product->productType = $productTypeId;
        $product->design = $designId;
        $product->set('name', $name);
        $product->setPublished($published);

        return $product;
    }
}

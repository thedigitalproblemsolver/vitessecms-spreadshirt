<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductIterator;

final class ProductRepository
{
    public function getByProductType(
        string $productTypeId,
        bool $hideUnpublished = true
    ): ProductIterator {
        Product::setFindPublished($hideUnpublished);
        Product::setFindValue('productType', $productTypeId);

        return new ProductIterator(Product::findAll());
    }

    public function getByDesign(
        string $designId,
        bool $hideUnpublished = true
    ): ProductIterator {
        Product::setFindPublished($hideUnpublished);
        Product::setFindValue('design', $designId);

        return new ProductIterator(Product::findAll());
    }

    public function getByProductTypeAndDesignId(
        string $productTypeId,
        string $designId,
        bool $hideUnpublished = true
    ): ?Product {
        Product::setFindPublished($hideUnpublished);
        Product::setFindValue('design', $designId);
        Product::setFindValue('productType', $productTypeId);

        return Product::findFirst();
    }
}

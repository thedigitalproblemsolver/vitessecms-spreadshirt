<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Traits\TraitRepositoryConstructor;
use VitesseCms\Database\Traits\TraitRepositoryParseCount;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductIterator;

final class ProductRepository
{
    use TraitRepositoryParseCount;
    use TraitRepositoryConstructor;

    public function getByProductType(string $productTypeId, bool $hideUnpublished = true): ProductIterator
    {
        Product::setFindPublished($hideUnpublished);
        Product::setFindValue('productType', $productTypeId);

        return new ProductIterator(Product::findAll());
    }

    public function count(?FindValueIterator $findValueIterator = null, bool $hideUnpublished = true): int
    {
        return $this->parseCount($findValueIterator, $hideUnpublished);
    }

    public function getByDesign(string $designId, bool $hideUnpublished = true): ProductIterator
    {
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

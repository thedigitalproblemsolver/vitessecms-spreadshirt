<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Models\ProductTypeIterator;

final class ProductTypeRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?ProductType
    {
        ProductType::setFindPublished($hideUnpublished);
        /** @var ProductType $productType */
        $productType = ProductType::findById($id);
        if (is_object($productType)):
            return $productType;
        endif;

        return null;
    }

    public function getByProductTypeId(int $productTypeId, bool $hideUnpublished = true): ?ProductType
    {
        ProductType::setFindPublished($hideUnpublished);
        ProductType::setFindValue('productTypeId', $productTypeId);

        /** @var ProductType $productType */
        $productType = ProductType::findFirst();
        if (is_object($productType)):
            return $productType;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): ProductTypeIterator {
        ProductType::setFindPublished($hideUnpublished);
        ProductType::addFindOrder('name');
        $this->parsefindValues($findValues);

        return new ProductTypeIterator(ProductType::findAll());
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                ProductType::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}

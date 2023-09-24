<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Models\ProductTypeIterator;

final class ProductTypeRepository
{
    public function getById(
        string $id,
        bool $hideUnpublished = true
    ): ?ProductType {
        ProductType::setFindPublished($hideUnpublished);
        /** @var ProductType $productType */
        $productType = ProductType::findById($id);
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
        ProductType::setFindLimit(999);
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

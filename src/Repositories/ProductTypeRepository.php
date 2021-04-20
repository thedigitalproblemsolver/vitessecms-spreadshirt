<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Spreadshirt\Models\ProductType;

class ProductTypeRepository
{
    public function getById(
        string $id,
        bool $hideUnpublished = true
    ): ?ProductType
    {
        ProductType::setFindPublished($hideUnpublished);
        /** @var ProductType $productType */
        $productType = ProductType::findById($id);
        if (is_object($productType)):
            return $productType;
        endif;

        return null;
    }
}

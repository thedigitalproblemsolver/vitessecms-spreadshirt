<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use stdClass;

class ProductTypesDTO
{
    private array $productTypes;

    public function __construct(private readonly stdClass $jsonData)
    {
        $this->productTypes = $this->jsonData->productTypes;
    }

    public function getProductTypeDTOs(): ProductTypeDTOIterator
    {
        $productTypeDTOIterator = new ProductTypeDTOIterator([]);
        foreach ($this->productTypes as $productType) {
            $productTypeDTOIterator->add(new ProductTypeDTO($productType));
        }

        return $productTypeDTOIterator;
    }
}
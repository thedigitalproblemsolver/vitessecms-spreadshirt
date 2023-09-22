<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use ArrayIterator;

class ProductTypeDTOIterator extends ArrayIterator
{
    public function __construct(array $productTypeDTOs)
    {
        parent::__construct($productTypeDTOs);
    }

    public function add(ProductTypeDTO $productType): void
    {
        parent::append($productType);
    }

    public function current(): ProductTypeDTO
    {
        return parent::current();
    }
}
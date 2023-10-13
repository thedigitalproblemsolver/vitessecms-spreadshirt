<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use ArrayIterator;

final class ProductTypeDTOIterator extends ArrayIterator
{
    public function __construct(array $appearanceDTOs)
    {
        parent::__construct($appearanceDTOs);
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
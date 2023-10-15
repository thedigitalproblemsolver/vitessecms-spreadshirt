<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use ArrayIterator;

class ProductTypeIterator extends ArrayIterator
{
    public function __construct(array $productTypes)
    {
        parent::__construct($productTypes);
    }

    public function add(ProductType $productType): void
    {
        parent::append($productType);
    }

    public function current(): ProductType
    {
        return parent::current();
    }
}

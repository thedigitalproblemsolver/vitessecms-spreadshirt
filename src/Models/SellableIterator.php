<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use ArrayIterator;

class SellableIterator extends ArrayIterator
{
    public function __construct(array $sellables)
    {
        parent::__construct($sellables);
    }

    public function add(Sellable $productType): void
    {
        parent::append($productType);
    }

    public function current(): Sellable
    {
        return parent::current();
    }
}

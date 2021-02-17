<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use \ArrayIterator;

class ProductIterator extends ArrayIterator
{
    /**
     * ProductIterator constructor.
     *
     * @param Product[] $products
     */
    public function __construct(array $products)
    {
        parent::__construct($products);
    }

    public function current(): Product
    {
        return parent::current();
    }
}

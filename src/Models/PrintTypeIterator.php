<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use ArrayIterator;

final class PrintTypeIterator extends ArrayIterator
{
    public function __construct(array $printTypes)
    {
        parent::__construct($printTypes);
    }

    public function current(): PrintType
    {
        return parent::current();
    }
}

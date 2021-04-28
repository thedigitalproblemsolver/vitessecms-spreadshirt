<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use ArrayIterator;

class ColorIterator extends ArrayIterator
{
    public function __construct(array $colors = [])
    {
        parent::__construct($colors);
    }

    public function current(): Color
    {
        return parent::current();
    }

    public function add(Color $color): void
    {
        $this->append($color);
    }
}

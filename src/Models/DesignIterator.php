<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use \ArrayIterator;

class DesignIterator extends ArrayIterator
{
    /**
     * DesignIterator constructor.
     *
     * @param Design[] $designs
     */
    public function __construct(array $designs)
    {
        parent::__construct($designs);
    }

    public function current(): Design
    {
        return parent::current();
    }
}

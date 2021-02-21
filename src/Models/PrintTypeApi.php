<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

class PrintTypeApi
{
    /**
     * @var ColorIterator
     */
    protected $colors;

    public function getColors(): ColorIterator
    {
        return $this->colors;
    }

    public function setColors(ColorIterator $colors): PrintTypeApi
    {
        $this->colors = $colors;

        return $this;
    }
}

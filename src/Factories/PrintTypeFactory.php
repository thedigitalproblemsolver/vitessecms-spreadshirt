<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\PrintType;

class PrintTypeFactory
{
    public static function create(
        string $name,
        int $printTypeId,
        bool $published = false
    ): PrintType {
        $printType = new PrintType();
        $printType->set('name', $name, true);
        $printType->printTypeId = $printTypeId;
        $printType->setPublished($published);

        return $printType;
    }
}

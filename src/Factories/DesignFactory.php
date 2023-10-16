<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\Design;

final class DesignFactory
{
    public static function create(string $name, int $designId, bool $published = false): Design
    {
        $design = new Design();
        $design->set('name', $name);
        $design->designId = $designId;
        $design->setPublished($published);

        return $design;
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\Design;

class DesignFactory
{
    public static function create(
        string $name,
        string $designId,
        bool $published = false
    ): Design
    {
        return (new Design())
            ->set('name', $name)
            ->set('designId', $designId)
            ->set('published', $published);
    }
}

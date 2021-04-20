<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Factories;

use VitesseCms\Spreadshirt\Models\PrintType;

/**
 * Class ProductTypeFactory
 */
class PrintTypeFactory
{

    /**
     * @param string $name
     * @param int $printTypeId
     * @param bool $published
     *
     * @return PrintType
     */
    public static function create(
        string $name,
        int $printTypeId,
        bool $published = false
    ): PrintType
    {
        return (new PrintType())
            ->set('name', $name, true)
            ->set('printTypeId', $printTypeId)
            ->set('published', $published);
    }
}

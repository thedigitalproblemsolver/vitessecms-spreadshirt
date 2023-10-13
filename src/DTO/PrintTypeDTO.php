<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use stdClass;

class PrintTypeDTO
{
    public readonly string $name;

    final public function __construct(private readonly stdClass $jsonData)
    {
        $this->name = $this->jsonData->name;
    }
}
<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use stdClass;

class ProductDTO
{
    public function __construct(private readonly stdClass $jsonData)
    {
    }

    public function getNamespaces(): array
    {
    }
}
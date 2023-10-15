<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use VitesseCms\Spreadshirt\Repositories\PrintTypeRepository;

final class PrintTypeListener
{
    public function __construct(
        private readonly PrintTypeRepository $printTypeRepository
    ) {
    }

    public function getRepository(): PrintTypeRepository
    {
        return $this->printTypeRepository;
    }
}
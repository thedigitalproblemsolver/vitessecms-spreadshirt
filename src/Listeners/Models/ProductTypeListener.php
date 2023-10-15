<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;

final class ProductTypeListener
{
    public function __construct(
        private readonly ProductTypeRepository $productTypeRepository
    ) {
    }

    public function getRepository(): ProductTypeRepository
    {
        return $this->productTypeRepository;
    }
}
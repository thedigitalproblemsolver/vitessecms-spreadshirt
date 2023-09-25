<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class SellableListener
{
    public function __construct(private readonly SellableRepository $sellableRepository)
    {
    }

    public function getRepository(): SellableRepository
    {
        return $this->sellableRepository;
    }
}
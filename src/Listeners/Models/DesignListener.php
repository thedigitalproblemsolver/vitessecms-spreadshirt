<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use VitesseCms\Database\Traits\TraitRepositoryListener;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;

final class DesignListener
{
    use TraitRepositoryListener;

    public function __construct(private readonly string $class)
    {
        $this->setRepositoryClass($this->class);
    }

    public function getRepository(): DesignRepository
    {
        return $this->parseGetRepository();
    }
}
<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use ArrayIterator;

final class SellableDTOIterator extends ArrayIterator
{
    public function __construct(array $appearanceDTOs)
    {
        parent::__construct($appearanceDTOs);
    }

    public function add(SellableDTO $sellableDTO): void
    {
        parent::append($sellableDTO);
    }

    public function current(): SellableDTO
    {
        return parent::current();
    }
}
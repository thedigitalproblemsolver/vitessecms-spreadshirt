<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use stdClass;

final class SellablesDTO
{
    public function __construct(private readonly stdClass $jsonData)
    {
    }

    public function getSellabeDTOs(): SellableDTOIterator
    {
        $sellableDTOIterator = new SellableDTOIterator([]);
        foreach ($this->jsonData->sellables as $sellable) {
            $sellableDTOIterator->add(new SellableDTO($sellable));
        }

        return $sellableDTOIterator;
    }
}
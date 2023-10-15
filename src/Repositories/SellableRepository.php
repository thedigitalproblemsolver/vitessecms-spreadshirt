<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Models\Sellable;
use VitesseCms\Spreadshirt\Models\SellableIterator;

final class SellableRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?Sellable
    {
        Sellable::setFindPublished($hideUnpublished);
        /** @var Sellable $sellable */
        $sellable = Sellable::findById($id);
        if (is_object($sellable)):
            return $sellable;
        endif;

        return null;
    }

    public function findAll(?FindValueIterator $findValues = null, bool $hideUnpublished = true): SellableIterator
    {
        Sellable::setFindPublished($hideUnpublished);
        Sellable::addFindOrder('name');
        $this->parsefindValues($findValues);

        return new SellableIterator(Sellable::findAll());
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Sellable::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}

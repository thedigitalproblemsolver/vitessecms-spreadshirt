<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Models\PrintType;
use VitesseCms\Spreadshirt\Models\PrintTypeIterator;

final class PrintTypeRepository
{
    public function getById(
        string $id,
        bool $hideUnpublished = true
    ): ?PrintType {
        PrintType::setFindPublished($hideUnpublished);
        /** @var PrintType $printType */
        $printType = PrintType::findById($id);
        if (is_object($printType)):
            return $printType;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): PrintTypeIterator {
        PrintType::setFindPublished($hideUnpublished);
        PrintType::addFindOrder('name');
        $this->parsefindValues($findValues);

        return new PrintTypeIterator(PrintType::findAll());
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                PrintType::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}

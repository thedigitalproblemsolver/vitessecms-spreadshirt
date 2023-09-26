<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Models\Design;

class DesignRepository
{
    public function countAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): int {
        Design::setFindPublished($hideUnpublished);

        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Design::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;

        return Design::count();
    }

    public function getById(string $id, bool $hideUnpublished = true): ?Design
    {
        Design::setFindPublished($hideUnpublished);

        /** @var Design $design */
        $design = Design::findById($id);
        if (is_object($design)):
            return $design;
        endif;

        return null;
    }

    public function getByDesignId(int $designId, bool $hideUnpublished = true): ?Design
    {
        Design::setFindPublished($hideUnpublished);
        Design::setFindValue('designId', $designId);

        /** @var Design $design */
        $design = Design::findFirst();
        if (is_object($design)):
            return $design;
        endif;

        return null;
    }
}

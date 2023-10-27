<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Traits\TraitRepositoryConstructor;
use VitesseCms\Database\Traits\TraitRepositoryParseFindAll;
use VitesseCms\Database\Traits\TraitRepositoryParseGetById;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\DesignIterator;

class DesignRepository
{
    use TraitRepositoryParseGetById;
    use TraitRepositoryParseFindAll;
    use TraitRepositoryConstructor;

    public function countAll(?FindValueIterator $findValues = null, bool $hideUnpublished = true): int {
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
        return $this->parseGetById($id, $hideUnpublished);
    }

    public function findAll(
        ?FindValueIterator $findValueIterator = null,
        bool $hideUnpublished = true,
        ?int $limit = null,
        ?FindOrderIterator $findOrders = null,
        ?array $returnFields = null
    ): DesignIterator {
        return $this->parseFindAll($findValueIterator, $hideUnpublished, $limit,$findOrders, $returnFields);
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

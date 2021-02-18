<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use VitesseCms\Database\AbstractCollection;

class ProductType extends AbstractCollection
{
    /**
     * @var ?int
     */
    public $productTypeId;

    public function getProductTypeId(): ?int
    {
        return $this->productTypeId ? (int)$this->productTypeId : null;
    }
}

<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use VitesseCms\Database\AbstractCollection;

class ProductType extends AbstractCollection
{
    public ?int $productTypeId;
    public string $previewImage;
    public array $sizes;
    public ?string $manufacturer = null;

    public function getProductTypeId(): ?int
    {
        return $this->productTypeId ? (int)$this->productTypeId : null;
    }
}

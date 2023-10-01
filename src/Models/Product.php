<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use VitesseCms\Database\AbstractCollection;

final class Product extends AbstractCollection
{
    public ?string $productType;
    public ?string $design;
    public ?array $appearances;
    public string $appearanceBaseImageUrl;
    public ?float $priceSale;
    public ?string $sellableId;
}

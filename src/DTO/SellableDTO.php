<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use stdClass;

final class SellableDTO
{
    public readonly string $name;
    public readonly int $mainDesignId;
    public readonly int $productTypeId;
    public readonly array $appearanceIds;
    public readonly string $previewImage;
    public readonly int $defaultAppearanceId;

    public function __construct(private readonly stdClass $jsonData)
    {
        $this->name = $this->jsonData->name;
        $this->mainDesignId = (int)$this->jsonData->mainDesignId;
        $this->productTypeId = (int)$this->jsonData->productTypeId;
        $this->appearanceIds = $this->jsonData->appearanceIds;
        $this->previewImage = $this->jsonData->previewImage->url;
        $this->defaultAppearanceId = (int)$this->jsonData->defaultAppearanceId;
    }
}
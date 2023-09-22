<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

use stdClass;

class ProductTypeDTO
{
    public readonly string $previewImage;
    public readonly string $sizeImage;
    public readonly array $sizes;
    public readonly array $appearances;
    public readonly array $stockStates;
    public readonly int $id;
    public readonly string $shortDescription;
    public readonly string $description;
    public readonly string $name;

    public function __construct(private readonly stdClass $jsonData)
    {
        $this->previewImage = $this->jsonData->resources[0]->href;
        $this->sizeImage = $this->jsonData->resources[1]->href;
        $this->sizes = $this->jsonData->sizes;
        $this->appearances = $this->jsonData->appearances;
        $this->stockStates = $this->jsonData->stockStates;
        $this->id = (int)$this->jsonData->id;
        $this->shortDescription = $this->jsonData->shortDescription;
        $this->description = $this->jsonData->description;
        $this->name = $this->jsonData->name;
    }
}
<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

class DesignApi
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $fileExtension;

    /**
     * @var array
     */
    protected $colors;

    /**
     * @var array
     */
    protected $colorIds;

    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(string $fileExtension): DesignApi
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): DesignApi
    {
        $this->colors = $colors;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): DesignApi
    {
        $this->id = $id;

        return $this;
    }

    public function getColorIds(): array
    {
        return $this->colorIds;
    }

    public function setColorIds(array $colorIds): DesignApi
    {
        $this->colorIds = $colorIds;

        return $this;
    }
}

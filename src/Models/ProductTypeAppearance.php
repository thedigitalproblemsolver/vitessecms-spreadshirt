<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

class ProductTypeAppearance
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $colors;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): ProductTypeAppearance
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ProductTypeAppearance
    {
        $this->name = $name;

        return $this;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): ProductTypeAppearance
    {
        $this->colors = $colors;

        return $this;
    }
}

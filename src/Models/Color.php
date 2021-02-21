<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

class Color
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $hex;

    /**
     * @var string
     */
    protected $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Color
    {
        $this->id = $id;

        return $this;
    }

    public function getHex(): string
    {
        return $this->hex;
    }

    public function setHex(string $hex): Color
    {
        $this->hex = $hex;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Color
    {
        $this->name = $name;

        return $this;
    }
}

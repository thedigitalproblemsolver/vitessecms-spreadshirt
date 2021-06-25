<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use VitesseCms\Database\AbstractCollection;

class Design extends AbstractCollection
{
    /**
     * @var mixed
     */
    public $name;

    /**
     * @var ?string
     */
    public $designId;

    public function getDesignId(): ?string
    {
        return $this->designId;
    }
}

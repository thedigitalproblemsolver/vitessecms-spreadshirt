<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;

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

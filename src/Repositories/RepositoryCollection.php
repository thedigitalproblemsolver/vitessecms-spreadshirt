<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Spreadshirt\Interfaces\RepositoryInterface;

class RepositoryCollection implements RepositoryInterface
{
    /**
     * @var ProductRepository
     */
    public $product;

    /**
     * @var ItemRepository
     */
    public $item;

    /**
     * @var DesignRepository
     */
    public $design;

    /**
     * @var ProductTypeRepository
     */
    public $productType;

    public function __construct(
        ProductRepository $productRepository,
        ItemRepository $itemRepository,
        DesignRepository $designRepository,
        ProductTypeRepository $productTypeRepository
    ) {
        $this->product = $productRepository;
        $this->item = $itemRepository;
        $this->design = $designRepository;
        $this->productType = $productTypeRepository;
    }
}

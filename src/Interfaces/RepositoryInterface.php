<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Interfaces;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;

/**
 * Interface RepositoryInterface
 * @property ProductRepository $product
 * @property ItemRepository $item
 * @property DesignRepository $design
 * @property ProductTypeRepository $productType
 */
interface RepositoryInterface
{
}

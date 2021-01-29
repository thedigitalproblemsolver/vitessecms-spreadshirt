<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Services;

use VitesseCms\Core\Services\ViewService;
use VitesseCms\Spreadshirt\Helpers\BasketHelper;
use VitesseCms\Spreadshirt\Helpers\DesignHelper;
use VitesseCms\Spreadshirt\Helpers\PrintTypeHelper;
use VitesseCms\Spreadshirt\Helpers\ProductHelper;
use VitesseCms\Spreadshirt\Helpers\ProductTypeHelper;
use VitesseCms\Spreadshirt\Helpers\ProductTypeViewHelper;

class SpreadshirtService
{
    /**
     * @var ProductHelper
     */
    public $product;

    /**
     * @var DesignHelper
     */
    public $design;

    /**
     * @var PrintTypeHelper
     */
    public $printType;

    /**
     * @var ProductTypeHelper
     */
    public $productType;

    /**
     * @var ProductTypeViewHelper
     */
    public $productTypeView;

    /**
     * @var BasketHelper
     */
    public $basket;

    public function __construct(ViewService $view)
    {
        $this->product = new ProductHelper($view);
        $this->design = new DesignHelper($view);
        $this->printType = new PrintTypeHelper($view);
        $this->productType = new ProductTypeHelper($view);
        $this->productTypeView = new ProductTypeViewHelper($view);
        $this->basket = new BasketHelper($view);
    }
}

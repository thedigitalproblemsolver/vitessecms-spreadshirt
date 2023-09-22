<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Services;

use VitesseCms\Spreadshirt\Helpers\BasketHelper;
use VitesseCms\Spreadshirt\Helpers\DesignHelper;
use VitesseCms\Spreadshirt\Helpers\PrintTypeHelper;
use VitesseCms\Spreadshirt\Helpers\ProductHelper;
use VitesseCms\Spreadshirt\Helpers\ProductTypeHelper;
use VitesseCms\Spreadshirt\Helpers\ProductTypeViewHelper;

class SpreadshirtService
{
    public function __construct(
        public readonly ProductHelper $product,
        public readonly DesignHelper $design,
        public readonly PrintTypeHelper $printType,
        public readonly ProductTypeHelper $productType,
        public readonly ProductTypeViewHelper $productTypeView,
        public readonly BasketHelper $basket
    ) {
    }
}

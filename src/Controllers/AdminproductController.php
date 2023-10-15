<?php
declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Spreadshirt\Forms\ProductForm;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Product;

class AdminproductController extends AbstractAdminController implements ModuleInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Product::class;
        $this->classForm = ProductForm::class;
    }
}

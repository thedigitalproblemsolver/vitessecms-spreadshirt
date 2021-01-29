<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Spreadshirt\Forms\ProductForm;
use VitesseCms\Spreadshirt\Interfaces\AdminRepositoriesInterface;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;

class AdminproductController extends AbstractAdminController implements ModuleInterface, AdminRepositoriesInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Product::class;
        $this->classForm = ProductForm::class;
    }
}

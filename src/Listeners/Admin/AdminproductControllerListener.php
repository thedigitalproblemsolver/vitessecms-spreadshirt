<?php
declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Admin;

use Phalcon\Events\Event;
use VitesseCms\Spreadshirt\Controllers\AdminproductController;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;

class AdminproductControllerListener
{
    public function beforeEdit(Event $event, AdminproductController $controller, Product $product): void
    {
        if (
            empty($product->getAppearances())
            && $product->getDesignId() !== null
            && $product->getProductTypeId() !== null
            && $product->getProductTypePrintAreaId() !== null
            && $product->getPrintTypeId() !== null
        ) :
            $appearances = $controller->spreadshirt->product->getAppearances(
                $product,
                $controller->spreadshirt->productType,
                $controller->spreadshirt->printType,
                $controller->repositories
            );
            $product->setAppearances($appearances)->save();
        endif;
    }

    public function beforeModelSave(Event $event, AdminproductController $controller, Product $product): void
    {
        if (
            $controller->request->hasPost('renderSpreadShirt')
            && $product->getDesignId() !== null
            && $product->getProductTypeId() !== null
            && $product->getProductTypePrintAreaId() !== null
            && $product->getPrintTypeId() !== null
        ) :
            $product->setAppearances(
                $controller->spreadshirt->product->getAppearances(
                    $product,
                    $controller->spreadshirt->productType,
                    $controller->spreadshirt->printType,
                    $controller->repositories
                )
            );
        endif;

        if ($controller->request->hasPost('selectedVariations')) :
            $product->set(
                'selectedVariations',
                $controller->request->getPost('selectedVariations')
            );
            unset($_POST['selectedVariations']);
        endif;

        $product->set('renderSpreadShirt', false);
        if ($controller->request->getPost('renderSpreadShirt') !== null) :
            unset($_POST['renderSpreadShirt']);
        endif;

        if (
            !empty($product->productType)
            && !empty($product->design)
            && empty($product->_('name'))
        ) :
            $design = Design::findById($product->design);
            $productType = ProductType::findById($product->productType);
            $product->set('name', $productType->_('name') . ' - ' . $design->_('name'), true);
        endif;
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Spreadshirt\Controllers\AdminproductController;
use VitesseCms\Spreadshirt\Controllers\AdminproducttypeController;
use VitesseCms\Spreadshirt\Models\Product;
use Phalcon\Events\Event;
use VitesseCms\Spreadshirt\Models\ProductType;

class AdminproducttypeControllerListener
{
    public function beforeModelSave(Event $event, AdminproducttypeController $controller, ProductType $productType): void
    {
        if (!$productType->_('price_purchase') && $productType->_('productTypeId')) :
            $productTypeXml = $controller->spreadshirt->productType->get((int)$productType->_('productTypeId'));
            $productType->set('price_purchase', (float)$productTypeXml->price->vatExcluded);
        endif;

        if (
            $productType->_('price_purchase')
            && $productType->_('taxrate')
            && !$productType->_('price_sale')
        ) :
            /** @var TaxRate $taxrate */
            $taxrate = TaxRate::findById($productType->_('taxrate'));
            $amount = ($productType->_('price_purchase') * 1.4) * (1 + $taxrate->getTaxRate() / 100);
            $productType->set('price_sale', $amount);
        endif;
    }
}

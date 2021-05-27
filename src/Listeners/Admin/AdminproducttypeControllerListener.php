<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Admin;

use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Spreadshirt\Controllers\AdminproducttypeController;
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

    public function afterPublish(Event $event, AdminproducttypeController $controller, ProductType $productType): void
    {
        $products = $controller->repositories->product->getByProductType((string)$productType->getId(), false);

        while ($products->valid()) :
            $product = $products->current();
            $ItemIsPublished = $productType->isPublished();
            $design = $controller->repositories->design->getById($product->getDesignId());
            if ($ItemIsPublished && ($design === null || !$design->isPublished())) :
                $ItemIsPublished = false;
            endif;

            $shopItems = $controller->repositories->item->findAll(
                new FindValueIterator(
                    [new FindValue('spreadShirtProductId', (string)$product->getId())]
                ),
                false
            );
            while ($shopItems->valid()) :
                $shopItem = $shopItems->current();
                $shopItem->setPublished($ItemIsPublished)->save();
                $shopItems->next();
            endwhile;

            $product->setPublished($ItemIsPublished)->save();
            $products->next();
        endwhile;
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Controllers;

use Phalcon\Events\Event;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Spreadshirt\Controllers\AdmindesignController;
use VitesseCms\Spreadshirt\Models\Design;
use function count;

final class AdmindesignControllerListener
{
    public function adminListFilter(Event $event, AdmindesignController $controller, AdminlistFormInterface $form): void {
        $form->addNameField($form);
        $form->addPublishedField($form);
    }

    public function beforeModelSave(Event $event, AdmindesignController $controller, Design $design): void
    {
        if (!$design->_('designId')) :
            Item::setFindPublished(false);
            $baseDesign = Item::findById($design->_('baseDesign'));
            $file = $controller->config->get('uploadDir') . $baseDesign->_('spreadshirtRasterizedImage');
            if (is_file($file)) :
                $design->set('designId',
                    $controller->spreadshirt->design->createDesign($design->_('name'), $baseDesign->_('description')));
                $controller->spreadshirt->design->uploadDesign(
                    $controller->spreadshirt->design->getImageUploadUrl($design->_('designId')),
                    $file
                );
            endif;
        endif;

        if ($design->_('designId') && empty($design->_('printTypeIds'))) :
            $designXml = $controller->spreadshirt->design->get($design->_('designId'));
            $printTypeIds = [];
            foreach ($designXml->printTypes->printType as $printType) :
                $printTypeIds[] = (int)XmlUtil::getAttribute($printType, 'id');
            endforeach;
            if (count($printTypeIds)) :
                $design->set('printTypeIds', $printTypeIds);
            endif;
        endif;

        if (MongoUtil::isObjectId($design->_('baseDesign'))) :
            Item::setFindPublished(false);
            $baseDesign = Item::findById($design->_('baseDesign'));
            $design->name = $baseDesign->name;
        endif;
    }

    public function afterPublish(Event $event, AdmindesignController $controller, Design $design): void
    {
        $products = $controller->repositories->product->getByDesign((string)$design->getId(), false);

        while ($products->valid()) :
            $product = $products->current();
            $ItemIsPublished = $design->isPublished();
            $productType = $controller->repositories->productType->getById(
                $product->getProductTypeId(), false
            );
            if ($ItemIsPublished && ($productType === null || !$productType->isPublished())) :
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

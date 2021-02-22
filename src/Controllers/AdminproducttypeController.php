<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Interfaces\BaseCollectionInterface;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Spreadshirt\Enum\DatafieldEnum;
use VitesseCms\Spreadshirt\Factories\ProductTypeFactory;
use VitesseCms\Spreadshirt\Forms\ProductTypeForm;
use VitesseCms\Spreadshirt\Interfaces\AdminRepositoriesInterface;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;

class AdminproducttypeController
    extends AbstractAdminController
    implements AdminRepositoriesInterface, ModuleInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = ProductType::class;
        $this->classForm = ProductTypeForm::class;
    }

    public function reloadAction(): void
    {
        $productTypes = $this->spreadshirt->productType->getAll();
        $namespaces = $productTypes->getNamespaces(true);
        foreach ($productTypes->productType as $productType) :
            $previewImage = (string)$productType->resources->resource[0]->attributes($namespaces['xlink']);
            $sizesMap = [];
            foreach ($productType->sizes->size as $size) :
                $sizesMap[strtoupper((string)$size->name)] = (int)XmlUtil::getAttribute($size, 'id');
            endforeach;

            $appearances = [];
            foreach ($productType->appearances->appearance as $appearance) :
                $resource = $appearance->resources->resource[0];
                $attributes = $resource->attributes($this->spreadshirt->product->getNamespaces()['xlink']);
                $id = (int)XmlUtil::getAttribute($appearance, 'id');
                $appearances[$id] = [
                    'color' => strtolower((string)$appearance->colors->color),
                    'colorId' => $id,
                    'colorName' => (string)$appearance->name,
                    'image' => (string)$attributes->href,
                    'stockStates' => [],
                ];
            endforeach;

            $sizesIdMap = array_flip($sizesMap);
            foreach ($productType->stockStates->stockState as $stockState) :
                $appearanceId = (int)XmlUtil::getAttribute($stockState->appearance, 'id');
                $sizeId = (int)XmlUtil::getAttribute($stockState->size, 'id');
                $stock = 0;
                if ((bool)$stockState->available) :
                    $stock = (int)$stockState->quantity;
                endif;
                $appearances[$appearanceId]['stockStates'][$sizesIdMap[$sizeId]] = $stock;
            endforeach;


            $productTypeId = (int)XmlUtil::getAttribute($productType, 'id');
            ProductType::setFindPublished(false);
            ProductType::setFindValue('productTypeId', $productTypeId);
            $productTypeItem = ProductType::findAll();
            if (\count($productTypeItem) === 0) {
                ProductType::setFindPublished(false);
                ProductType::setFindValue('productTypeId', (string)$productTypeId);
                $productTypeItem = ProductType::findAll();
                if ($productTypeItem) {
                    $productTypeItem[0]
                        ->set('sizesMap', $sizesMap)
                        ->set('productTypeId', (int)$productTypeId)
                        ->set('introtext', (string)$productType->shortDescription)
                        ->set('bodytext', (string)$productType->description)
                        ->set('previewImage', $previewImage)
                        ->set('sizeTable', $this->spreadshirt->productType->buildSizeTable($productType, $namespaces))
                        ->set('appearances', $appearances)
                        ->save();
                } else {
                    ProductTypeFactory::create(
                        (string)$productType->name,
                        $productTypeId
                    )
                        ->set('sizesMap', $sizesMap)
                        ->set('introtext', (string)$productType->shortDescription)
                        ->set('bodytext', (string)$productType->description)
                        ->set('previewImage', $previewImage)
                        ->set('sizeTable', $this->spreadshirt->productType->buildSizeTable($productType, $namespaces))
                        ->set('appearances', $appearances)
                        ->save();
                }
            } elseif (\count($productTypeItem) === 1) {
                $productTypeItem[0]
                    ->set('sizesMap', $sizesMap)
                    ->set('introtext', (string)$productType->shortDescription)
                    ->set('bodytext', (string)$productType->description)
                    ->set('previewImage', $previewImage)
                    ->set('sizeTable', $this->spreadshirt->productType->buildSizeTable($productType, $namespaces))
                    ->set('appearances', $appearances)
                    ->save();
            }
        endforeach;

        $this->flash->setSucces('ProductTypes reloaded');
        $this->redirect();
    }

    public function afterPublish(BaseCollectionInterface $item): void
    {
        $products = $this->repositories->product
            ->getByProductType((string)$item->getId(), false);

        while ($products->valid()) :
            $product = $products->current();
            $ItemIsPublished = $item->isPublished();
            $design = $this->repositories->design->getById($product->getDesignId());
            if ($ItemIsPublished && ($design === null || !$design->isPublished())) :
                $ItemIsPublished = false;
            endif;

            $shopItems = $this->repositories->item->findAll(
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

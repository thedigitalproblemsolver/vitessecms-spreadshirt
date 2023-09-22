<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Spreadshirt\Factories\ProductTypeFactory;
use VitesseCms\Spreadshirt\Forms\ProductTypeForm;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Interfaces\RepositoriesInterface;
use VitesseCms\Spreadshirt\Models\ProductType;

use function count;

class AdminproducttypeController
    extends AbstractAdminController
    implements RepositoriesInterface, ModuleInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = ProductType::class;
        $this->classForm = ProductTypeForm::class;
    }

    public function reloadAction(): void
    {
        $productTypesDTO = $this->spreadshirt->productType->getAll();

        while ($productTypesDTO->getProductTypeDTOs()->valid()) {
            $productTypeDTO = $productTypesDTO->getProductTypeDTOs()->current();
            $previewImage = $productTypeDTO->previewImage;

            $sizesMap = [];
            foreach ($productTypeDTO->sizes as $size) {
                $sizesMap[strtoupper((string)$size->name)] = (int)$size->id;
            }

            $appearances = [];
            foreach ($productTypeDTO->appearances as $appearance) {
                $appearances[(int)$appearance->id] = [
                    'color' => $appearance->colors[0]->value,
                    'colorId' => (int)$appearance->id,
                    'colorName' => $appearance->name,
                    'image' => $appearance->resources[0]->href,
                    'stockStates' => [],
                ];
            }

            $sizesIdMap = array_flip($sizesMap);
            foreach ($productTypeDTO->stockStates as $stockState) {
                $appearanceId = (int)$stockState->appearance->id;
                $sizeId = (int)$stockState->size->id;
                $stock = 0;
                if ($stockState->available) :
                    $stock = $stockState->quantity;
                endif;
                $appearances[$appearanceId]['stockStates'][$sizesIdMap[$sizeId]] = $stock;
            }

            $productTypeId = $productTypeDTO->id;
            ProductType::setFindPublished(false);
            ProductType::setFindValue('productTypeId', $productTypeId);
            $productTypeItem = ProductType::findAll();
            if (count($productTypeItem) === 0) {
                ProductTypeFactory::create(
                    $productTypeDTO->name,
                    $productTypeId
                )
                    ->set('sizesMap', $sizesMap)
                    ->set('introtext', $productTypeDTO->shortDescription)
                    ->set('bodytext', $productTypeDTO->description)
                    ->set('previewImage', $previewImage)
                    ->set('sizeTable', $this->spreadshirt->productType->buildSizeTable($productTypeDTO))
                    ->set('appearances', $appearances)
                    ->save();
            } else {
                $productTypeItem[0]
                    ->set('sizesMap', $sizesMap)
                    ->set('introtext', $productTypeDTO->shortDescription)
                    ->set('bodytext', $productTypeDTO->description)
                    ->set('previewImage', $previewImage)
                    ->set('sizeTable', $this->spreadshirt->productType->buildSizeTable($productTypeDTO))
                    ->set('appearances', $appearances)
                    ->save();
            }
            
            $productTypesDTO->getProductTypeDTOs()->next();
        }

        $this->flash->setSucces('ProductTypes reloaded');

        $this->redirect();
    }
}

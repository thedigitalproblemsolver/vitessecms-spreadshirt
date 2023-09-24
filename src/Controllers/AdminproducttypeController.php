<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use ArrayIterator;
use stdClass;
use VitesseCms\Admin\Interfaces\AdminModelEditableInterface;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Admin\Interfaces\AdminModelListInterface;
use VitesseCms\Admin\Interfaces\AdminModelPublishableInterface;
use VitesseCms\Admin\Traits\TraitAdminModelEditable;
use VitesseCms\Admin\Traits\TraitAdminModelList;
use VitesseCms\Admin\Traits\TraitAdminModelPublishable;
use VitesseCms\Core\AbstractControllerAdmin;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Enums\ProductTypeEnum;
use VitesseCms\Spreadshirt\Factories\ProductTypeFactory;
use VitesseCms\Spreadshirt\Forms\ProductTypeForm;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;

final class AdminproducttypeController extends AbstractControllerAdmin implements
    AdminModelPublishableInterface,
    AdminModelEditableInterface,
    AdminModelListInterface
{
    use TraitAdminModelEditable;
    use TraitAdminModelPublishable;
    use TraitAdminModelList;

    private readonly ProductTypeRepository $productTypeRepository;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->productTypeRepository = $this->eventsManager->fire(
            ProductTypeEnum::GET_REPOSITORY->value,
            new stdClass()
        );
    }

    public function getModel(string $id): ?AbstractCollection
    {
        return match ($id) {
            'new' => new ProductType(),
            default => $this->productTypeRepository->getById($id, false)
        };
    }

    public function getModelList(?FindValueIterator $findValueIterator): ArrayIterator
    {
        return $this->productTypeRepository->findAll(
            $findValueIterator,
            false
        );
    }

    public function getModelForm(): AdminModelFormInterface
    {
        return new ProductTypeForm();
    }

    public function reloadAction(): void
    {
        $productTypesDTO = $this->spreadshirt->productType->getAll();
        $getProductTypeDTOs = $productTypesDTO->getProductTypeDTOs();

        while ($getProductTypeDTOs->valid()) {
            $productTypeDTO = $getProductTypeDTOs->current();
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
                if ($stockState->available) :
                    $appearances[$appearanceId]['stockStates'][$sizesIdMap[$sizeId]] = $stockState->quantity;
                endif;
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

            $getProductTypeDTOs->next();
        }

        $this->flashService->setSucces('ProductTypes reloaded');

        $this->redirect($this->request->getHTTPReferer());
    }
}

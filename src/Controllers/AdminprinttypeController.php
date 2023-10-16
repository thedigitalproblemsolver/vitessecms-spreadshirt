<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use ArrayIterator;
use stdClass;
use VitesseCms\Admin\Interfaces\AdminModelEditableInterface;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Admin\Interfaces\AdminModelListInterface;
use VitesseCms\Admin\Traits\TraitAdminModelEditable;
use VitesseCms\Admin\Traits\TraitAdminModelList;
use VitesseCms\Core\AbstractControllerAdmin;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Models\FindOrder;
use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Enums\PrintTypeEnum;
use VitesseCms\Spreadshirt\Forms\PrintTypeForm;
use VitesseCms\Spreadshirt\Models\PrintType;
use VitesseCms\Spreadshirt\Repositories\PrintTypeRepository;

final class AdminprinttypeController extends AbstractControllerAdmin implements
    AdminModelListInterface,
    AdminModelEditableInterface
{
    use TraitAdminModelList;
    use TraitAdminModelEditable;

    private readonly PrintTypeRepository $printTypeRepository;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->printTypeRepository = $this->eventsManager->fire(PrintTypeEnum::GET_REPOSITORY->value, new stdClass());
    }

    public function getModel(string $id): ?AbstractCollection
    {
        return match ($id) {
            'new' => new PrintType(),
            default => $this->printTypeRepository->getById($id, false)
        };
    }

    public function getModelList(?FindValueIterator $findValueIterator): ArrayIterator
    {
        return $this->printTypeRepository->findAll(
            $findValueIterator,
            false,
            99999,
            new FindOrderIterator([new FindOrder('createdAt', -1)])
        );
    }

    public function getModelForm(): AdminModelFormInterface
    {
        return new PrintTypeForm();
    }
}

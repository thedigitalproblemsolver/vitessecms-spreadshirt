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
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Forms\SellableForm;
use VitesseCms\Spreadshirt\Models\Sellable;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class AdminsellableController extends AbstractControllerAdmin implements
    AdminModelPublishableInterface,
    AdminModelEditableInterface,
    AdminModelListInterface
{
    use TraitAdminModelEditable;
    use TraitAdminModelPublishable;
    use TraitAdminModelList;

    private readonly SellableRepository $sellableRepository;

    public function OnConstruct()
    {
        parent::OnConstruct();

        $this->sellableRepository = $this->eventsManager->fire(SellableEnum::GET_REPOSITORY->value, new stdClass());
    }

    public function getModel(string $id): ?AbstractCollection
    {
        return match ($id) {
            'new' => new Sellable(),
            default => $this->sellableRepository->getById($id, false)
        };
    }

    public function getModelList(?FindValueIterator $findValueIterator): ArrayIterator
    {
        return $this->sellableRepository->findAll(
            $findValueIterator,
            false
        );
    }

    public function getModelForm(): AdminModelFormInterface
    {
        return new SellableForm();
    }
}
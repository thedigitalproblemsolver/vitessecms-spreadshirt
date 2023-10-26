<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Admin\Interfaces\AdminModelDeletableInterface;
use VitesseCms\Admin\Interfaces\AdminModelEditableInterface;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Admin\Interfaces\AdminModelListInterface;
use VitesseCms\Admin\Interfaces\AdminModelPublishableInterface;
use VitesseCms\Admin\Traits\TraitAdminModelDeletable;
use VitesseCms\Admin\Traits\TraitAdminModelEditable;
use VitesseCms\Admin\Traits\TraitAdminModelList;
use VitesseCms\Admin\Traits\TraitAdminModelPublishable;
use VitesseCms\Core\AbstractControllerAdmin;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Spreadshirt\Enums\DesignEnum;
use VitesseCms\Spreadshirt\Forms\DesignForm;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use stdClass;
use ArrayIterator;

class AdmindesignController extends AbstractControllerAdmin implements
    AdminModelListInterface,
    AdminModelDeletableInterface,
    AdminModelPublishableInterface,
    AdminModelEditableInterface
{
    use TraitAdminModelList;
    use TraitAdminModelDeletable;
    use TraitAdminModelPublishable;
    use TraitAdminModelEditable;

    private DesignRepository $designRepository;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->designRepository = $this->eventsManager->fire(DesignEnum::GET_REPOSITORY->value, new stdClass());
    }

    public function getModel(string $id): ?AbstractCollection
    {
        return match ($id) {
            'new' => new Design(),
            default => $this->designRepository->getById($id, false)
        };
    }

    public function getModelList(?FindValueIterator $findValueIterator): ArrayIterator
    {
        return $this->designRepository->findAll($findValueIterator,false);
    }

    public function getModelForm(): AdminModelFormInterface
    {
        return new DesignForm();
    }
}

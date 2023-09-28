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
use VitesseCms\Job\Enum\JobQueueEnum;
use VitesseCms\Job\Services\BeanstalkService;
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Forms\SellableForm;
use VitesseCms\Spreadshirt\Helpers\SellableHelper;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Sellable;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class AdminsellableController extends AbstractControllerAdmin implements
    ModuleInterface,
    AdminModelPublishableInterface,
    AdminModelEditableInterface,
    AdminModelListInterface
{
    use TraitAdminModelEditable;
    use TraitAdminModelPublishable;
    use TraitAdminModelList;

    private readonly SellableRepository $sellableRepository;
    private readonly SellableHelper $sellableHelper;
    private readonly BeanstalkService $jobQueue;

    public function OnConstruct()
    {
        parent::OnConstruct();

        $this->sellableRepository = $this->eventsManager->fire(SellableEnum::GET_REPOSITORY->value, new stdClass());
        $this->jobQueue = $this->eventsManager->fire(JobQueueEnum::ATTACH_SERVICE_LISTENER->value, new stdClass());
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

    public function reloadAction(): void
    {
        $sellablesDTO = $this->spreadshirt->sellable->getAll();
        $sellableDTOIterator = $sellablesDTO->getSellabeDTOs();
        while ($sellableDTOIterator->valid()) {
            $sellableDTO = $sellableDTOIterator->current();

            $this->jobQueue->createListenerJob(
                'Spreadshirts sellable : ' . $sellableDTO->name,
                SellableEnum::HANDLE_IMPORT->value,
                $sellableDTO
            );
            $sellableDTOIterator->next();
        }
        echo 'in reload';
        die();
    }
}
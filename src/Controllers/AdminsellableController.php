<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use stdClass;
use VitesseCms\Core\AbstractControllerAdmin;
use VitesseCms\Job\Enum\JobQueueEnum;
use VitesseCms\Job\Services\BeanstalkService;
use VitesseCms\Spreadshirt\Enums\SellableEnum;

final class AdminsellableController extends AbstractControllerAdmin
{
    private readonly BeanstalkService $jobQueue;

    public function OnConstruct()
    {
        parent::OnConstruct();

        $this->jobQueue = $this->eventsManager->fire(JobQueueEnum::ATTACH_SERVICE_LISTENER->value, new stdClass());
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

        $this->flashService->setSucces('SPREADSHIRT_SELLABLE_RELOAD_SUCCES');

        $this->redirect($this->request->getHTTPReferer());
    }
}
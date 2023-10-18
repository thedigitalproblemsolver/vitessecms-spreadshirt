<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use stdClass;
use VitesseCms\Core\AbstractControllerAdmin;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Job\Enum\JobQueueEnum;
use VitesseCms\Job\Services\BeanstalkService;
use VitesseCms\Spreadshirt\DTO\SellableDTO;
use VitesseCms\Spreadshirt\Enums\ProductEnum;
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;

final class AdminsellableController extends AbstractControllerAdmin
{
    private readonly BeanstalkService $jobQueue;
    private readonly ProductRepository $productRepository;

    public function OnConstruct()
    {
        parent::OnConstruct();

        $this->jobQueue = $this->eventsManager->fire(JobQueueEnum::ATTACH_SERVICE_LISTENER->value, new stdClass());
        $this->productRepository = $this->eventsManager->fire(ProductEnum::GET_REPOSITORY->value, new stdClass());
    }

    public function reloadAction(): void
    {
        $sellablesDTO = $this->spreadshirt->sellable->getAll();
        $sellableDTOIterator = $sellablesDTO->getSellabeDTOs();
        while ($sellableDTOIterator->valid()) {
            $this->createJob($sellableDTOIterator->current());
            $sellableDTOIterator->next();
        }

        $this->flashService->setSucces('SPREADSHIRT_SELLABLE_RELOAD_SUCCES');

        $this->redirect($this->request->getHTTPReferer());
    }

    private function createJob(SellableDTO $sellableDTO): void
    {
        $this->jobQueue->createListenerJob(
            'Spreadshirts sellable : ' . $sellableDTO->name,
            SellableEnum::HANDLE_IMPORT->value,
            $sellableDTO
        );
    }

    public function reloadnewAction(): void
    {
        $sellablesDTO = $this->spreadshirt->sellable->getAll();
        $sellableDTOIterator = $sellablesDTO->getSellabeDTOs();
        $jobCreated = false;
        while ($sellableDTOIterator->valid()) {
            $sellableDTO = $sellableDTOIterator->current();
            if ($this->productRepository->count(
                    new FindValueIterator([new FindValue('sellableId', $sellableDTO->sellableId)])
                ) === 0
            ) {
                $this->createJob($sellableDTO);
                $jobCreated = true;
            }
            $sellableDTOIterator->next();
        }

        if ($jobCreated) {
            $this->flashService->setSucces('SPREADSHIRT_SELLABLE_RELOAD_SUCCES');
        } else {
            $this->flashService->setSucces('SPREADSHIRT_SELLABLE_RELOAD_NO_JOBS_CREATED');
        }


        $this->redirect($this->request->getHTTPReferer());
    }
}
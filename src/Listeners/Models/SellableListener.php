<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use Phalcon\Events\Event;
use VitesseCms\Job\Services\BeanstalkService;
use VitesseCms\Log\Services\LogService;
use VitesseCms\Spreadshirt\DTO\SellableDTO;
use VitesseCms\Spreadshirt\Enums\ProductEnum;
use VitesseCms\Spreadshirt\Factories\DesignFactory;
use VitesseCms\Spreadshirt\Factories\ProductFactory;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class SellableListener
{
    public function __construct(
        private readonly SellableRepository $sellableRepository,
        private readonly DesignRepository $designRepository,
        private readonly ProductRepository $productRepository,
        private readonly ProductTypeRepository $productTypeRepository,
        private readonly BeanstalkService $beanstalkService,
        private readonly LogService $logService
    ) {
    }

    public function getRepository(): SellableRepository
    {
        return $this->sellableRepository;
    }

    public function handleImport(Event $event, SellableDTO $sellableDTO): bool
    {
        $design = $this->handleDesign($sellableDTO->mainDesignId, $sellableDTO->name);

        if ($design->baseDesign !== null) {
            $productType = $this->productTypeRepository->getByProductTypeId($sellableDTO->productTypeId, false);
            $appearanceBaseUrl = str_replace(
                'appearanceId=' . $sellableDTO->defaultAppearanceId,
                '[APPEARANCE_ID]',
                $sellableDTO->previewImage
            );

            $product = $this->handleProduct(
                $design,
                $productType,
                $sellableDTO->appearanceIds,
                $sellableDTO->priceSale,
                $sellableDTO->sellableId,
                $appearanceBaseUrl
            );
            $this->beanstalkService->createListenerJob(
                'Covert Spreadshirt product to shop Product',
                ProductEnum::CONVERT_TO_SHOP_PRODUCT->value,
                $product
            );
        } else {
            $this->logService->write(
                $design->getId(),
                $design::class,
                'Desing <b>' . $design->getNameField() . '</b>: baseDesign is missing'
            );
        }

        return true;
    }

    private function handleDesign(int $mainDesignId, string $designName): Design
    {
        $design = $this->designRepository->getByDesignId($mainDesignId);
        if ($design === null) {
            $design = DesignFactory::create($designName, $mainDesignId);
            $design->save();
            $this->logService->write(
                $design->getId(),
                Design::class,
                'Design <b>' . $design->getNameField() . '</b> Created'
            );
        }

        return $design;
    }

    private function handleProduct(
        Design $design,
        ProductType $productType,
        array $appearanceIds,
        float $priceSale,
        string $sellableId,
        string $appearanceBaseUrl
    ): Product {
        $productType->setPublished(true);
        $productType->save();

        $product = $this->productRepository->getByProductTypeAndDesignId(
            (string)$productType->getId(),
            (string)$design->getId(),
            false
        );

        if ($product === null) {
            $product = ProductFactory::create(
                $design->getNameField() . ' - ' . $productType->getNameField(),
                (string)$productType->getId(),
                (string)$design->getId(),
                $sellableId,
                true
            );
        }
        $product->appearances = $appearanceIds;
        $product->appearanceBaseImageUrl = $appearanceBaseUrl;
        $product->priceSale = $priceSale;
        $product->setPublished(true);
        $product->save();

        return $product;
    }
}
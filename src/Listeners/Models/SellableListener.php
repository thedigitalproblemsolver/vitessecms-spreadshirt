<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use Phalcon\Events\Event;
use VitesseCms\Job\Services\BeanstalkService;
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
        private readonly BeanstalkService $beanstalkService
    ) {
    }

    public function getRepository(): SellableRepository
    {
        return $this->sellableRepository;
    }

    public function handleImport(Event $event, SellableDTO $sellableDTO)
    {
        $productType = $this->productTypeRepository->getByProductTypeId($sellableDTO->productTypeId, false);
        $appearanceBaseUrl = str_replace(
            'appearanceId=' . $sellableDTO->defaultAppearanceId,
            '[APPEARANCE_ID]',
            $sellableDTO->previewImage
        );

        $design = $this->handleDesign($sellableDTO->mainDesignId, $sellableDTO->name);
        $product = $this->handleProduct(
            $design,
            $productType,
            $sellableDTO->appearanceIds,
            $sellableDTO->priceSale,
            $appearanceBaseUrl
        );

        $this->beanstalkService->createListenerJob(
            'Covert Spreadshirt product to shop Product',
            ProductEnum::CONVERT_TO_SHOP_PRODUCT->value,
            $product
        );
    }

    private function handleDesign(int $mainDesignId, string $designName): Design
    {
        $design = $this->designRepository->getByDesignId($mainDesignId);
        if ($design === null) {
            $design = DesignFactory::create($designName, $mainDesignId);
            $design->save();
        }

        return $design;
    }

    private function handleProduct(
        Design $design,
        ProductType $productType,
        array $appearanceIds,
        float $priceSale,
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
                $design->getNameField(),
                (string)$productType->getId(),
                (string)$design->getId(),
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
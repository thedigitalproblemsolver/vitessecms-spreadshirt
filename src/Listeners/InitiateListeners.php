<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Shop\Repositories\TaxRateRepository;
use VitesseCms\Spreadshirt\Enums\ProductEnum;
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Enums\SpreadShirtSettingEnum;
use VitesseCms\Spreadshirt\Helpers\ProductTypeHelper;
use VitesseCms\Spreadshirt\Listeners\Admin\AdminMenuListener;
use VitesseCms\Spreadshirt\Listeners\Models\ProductListener;
use VitesseCms\Spreadshirt\Listeners\Models\SellableListener;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $injectable): void
    {
        $injectable->eventsManager->attach(
            'adminMenu',
            new AdminMenuListener(
                $injectable->setting->has(SpreadShirtSettingEnum::API_KEY->value),
                $injectable->configuration->getLanguageShort()
            )
        );
        $injectable->eventsManager->attach(
            SellableEnum::LISTENER->value,
            new SellableListener(
                new SellableRepository(),
                new DesignRepository(Design::class),
                new ProductRepository(Product::class),
                new ProductTypeRepository(),
                $injectable->jobQueue,
                $injectable->log
            )
        );
        $injectable->eventsManager->attach(
            ProductEnum::LISTENER->value,
            new ProductListener(
                new ProductRepository(Product::class),
                new ItemRepository(),
                new ProductTypeRepository(),
                new DesignRepository(Design::class),
                $injectable->setting,
                new ProductTypeHelper($injectable->eventsManager),
                $injectable->configuration->getUploadDir(),
                $injectable->log,
                $injectable->jobQueue,
                $injectable->eventsManager,
                new TaxRateRepository(TaxRate::class)
            )
        );
    }
}

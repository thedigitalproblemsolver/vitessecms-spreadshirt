<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Spreadshirt\Enums\ProductEnum;
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Enums\SpreadShirtSettingEnum;
use VitesseCms\Spreadshirt\Helpers\ProductTypeHelper;
use VitesseCms\Spreadshirt\Listeners\Admin\AdminMenuListener;
use VitesseCms\Spreadshirt\Listeners\Models\ProductListener;
use VitesseCms\Spreadshirt\Listeners\Models\SellableListener;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach(
            'adminMenu',
            new AdminMenuListener(
                $di->setting->has(SpreadShirtSettingEnum::API_KEY->value),
                $di->configuration->getLanguageShort()
            )
        );
        $di->eventsManager->attach(
            SellableEnum::LISTENER->value,
            new SellableListener(
                new SellableRepository(),
                new DesignRepository(),
                new ProductRepository(Product::class),
                new ProductTypeRepository(),
                $di->jobQueue,
                $di->log
            )
        );
        $di->eventsManager->attach(
            ProductEnum::LISTENER->value,
            new ProductListener(
                new ProductRepository(Product::class),
                new ItemRepository(),
                new ProductTypeRepository(),
                new DesignRepository(),
                $di->setting,
                new ProductTypeHelper($di->eventsManager),
                $di->configuration->getUploadDir(),
                $di->log,
                $di->jobQueue,
                $di->eventsManager
            )
        );
    }
}

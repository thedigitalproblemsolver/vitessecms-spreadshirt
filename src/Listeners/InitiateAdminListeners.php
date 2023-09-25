<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Spreadshirt\Controllers\AdmindesignController;
use VitesseCms\Spreadshirt\Controllers\AdminprinttypeController;
use VitesseCms\Spreadshirt\Controllers\AdminproductController;
use VitesseCms\Spreadshirt\Controllers\AdminproducttypeController;
use VitesseCms\Spreadshirt\Controllers\AdminsellableController;
use VitesseCms\Spreadshirt\Enums\PrintTypeEnum;
use VitesseCms\Spreadshirt\Enums\ProductTypeEnum;
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Enums\SettingEnum;
use VitesseCms\Spreadshirt\Listeners\Admin\AdmindesignControllerListener;
use VitesseCms\Spreadshirt\Listeners\Admin\AdminMenuListener;
use VitesseCms\Spreadshirt\Listeners\Admin\AdminproductControllerListener;
use VitesseCms\Spreadshirt\Listeners\Controllers\AdminprinttypeControllerListeners;
use VitesseCms\Spreadshirt\Listeners\Controllers\AdminproducttypeControllerListener;
use VitesseCms\Spreadshirt\Listeners\Controllers\AdminsellableControllerListeners;
use VitesseCms\Spreadshirt\Listeners\Models\PrintTypeListener;
use VitesseCms\Spreadshirt\Listeners\Models\ProductTypeListener;
use VitesseCms\Spreadshirt\Listeners\Models\SellableListener;
use VitesseCms\Spreadshirt\Repositories\PrintTypeRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

final class InitiateAdminListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach(
            'adminMenu',
            new AdminMenuListener(
                $di->setting->has(SettingEnum::SPREADSHIRT_API_KEY),
                $di->configuration->getLanguageShort()
            )
        );

        self::addModels($di);
        self::addControllers($di);
    }

    private static function addModels(InjectableInterface $di): void
    {
        $di->eventsManager->attach(
            ProductTypeEnum::LISTENER->value,
            new ProductTypeListener(new ProductTypeRepository())
        );
        $di->eventsManager->attach(PrintTypeEnum::LISTENER->value, new PrintTypeListener(new PrintTypeRepository()));
        $di->eventsManager->attach(SellableEnum::LISTENER->value, new SellableListener(new SellableRepository()));
    }

    private static function addControllers(InjectableInterface $di): void
    {
        $di->eventsManager->attach(AdminproductController::class, new AdminproductControllerListener());
        $di->eventsManager->attach(AdmindesignController::class, new AdmindesignControllerListener());
        $di->eventsManager->attach(AdminproducttypeController::class, new AdminproducttypeControllerListener());
        $di->eventsManager->attach(AdminprinttypeController::class, new AdminprinttypeControllerListeners());
        $di->eventsManager->attach(AdminsellableController::class, new AdminsellableControllerListeners());
    }
}

<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Spreadshirt\Enums\SellableEnum;
use VitesseCms\Spreadshirt\Enums\SettingEnum;
use VitesseCms\Spreadshirt\Listeners\Admin\AdminMenuListener;
use VitesseCms\Spreadshirt\Listeners\Models\SellableListener;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\SellableRepository;

class InitiateListeners implements InitiateListenersInterface
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
        $di->eventsManager->attach(
            SellableEnum::LISTENER->value,
            new SellableListener(
                new SellableRepository(),
                new DesignRepository(),
                new ProductRepository(),
                new ProductTypeRepository()
            )
        );
    }
}

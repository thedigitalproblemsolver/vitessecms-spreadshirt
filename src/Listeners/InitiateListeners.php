<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Spreadshirt\Enums\SettingEnum;
use VitesseCms\Spreadshirt\Listeners\Admin\AdminMenuListener;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach('adminMenu', new AdminMenuListener(
            $di->setting->has(SettingEnum::SPREADSHIRT_API_KEY),
            $di->configuration->getLanguageShort()
        ));
    }
}

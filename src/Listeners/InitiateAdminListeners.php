<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Spreadshirt\Controllers\AdmindesignController;
use VitesseCms\Spreadshirt\Controllers\AdminproductController;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdminproductController::class, new AdminproductControllerListener());
        $eventsManager->attach(AdmindesignController::class, new AdmindesignControllerListener());
    }
}

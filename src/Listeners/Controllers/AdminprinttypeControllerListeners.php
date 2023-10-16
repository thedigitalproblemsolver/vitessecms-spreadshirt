<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Controllers;

use Phalcon\Events\Event;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Spreadshirt\Controllers\AdminprinttypeController;

final class AdminprinttypeControllerListeners
{
    public function adminListFilter(
        Event $event,
        AdminprinttypeController $controller,
        AdminlistFormInterface $form
    ): void {
        $form->addNameField($form);
        $form->addPublishedField($form);
    }
}
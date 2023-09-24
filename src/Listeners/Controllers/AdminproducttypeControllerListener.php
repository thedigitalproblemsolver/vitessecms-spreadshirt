<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Controllers;

use Phalcon\Events\Event;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Spreadshirt\Controllers\AdminproducttypeController;

final class AdminproducttypeControllerListener
{
    public function adminListFilter(
        Event $event,
        AdminproducttypeController $controller,
        AdminlistFormInterface $form
    ): void {
        $form->addNameField($form);
        $form->addPublishedField($form);
    }
}
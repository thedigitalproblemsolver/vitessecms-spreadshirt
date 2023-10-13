<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Controllers;

use Phalcon\Events\Event;
use VitesseCms\Admin\Factories\AdminListButtonFactory;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Admin\Models\AdminListButtonIterator;
use VitesseCms\Spreadshirt\Controllers\AdminsellableController;

final class AdminsellableControllerListeners
{
    public function adminListFilter(
        Event $event,
        AdminsellableController $controller,
        AdminlistFormInterface $form
    ): void {
        $form->addNameField($form);
        $form->addPublishedField($form);
    }

    public function adminListButtons(Event $event, AdminListButtonIterator $adminListButtonIterator): void
    {
        $adminListButtonIterator->add(
            AdminListButtonFactory::create(
                'btn btn-outline-info fa fa-refresh',
                'admin/spreadshirt/adminsellable/reload',
                'Reload ProductTypes'
            )
        );
    }
}
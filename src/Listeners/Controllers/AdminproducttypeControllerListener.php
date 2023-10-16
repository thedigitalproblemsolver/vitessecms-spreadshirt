<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Controllers;

use Phalcon\Events\Event;
use VitesseCms\Admin\Factories\AdminListButtonFactory;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Admin\Models\AdminListButtonIterator;
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

    public function adminListButtons(Event $event, AdminListButtonIterator $adminListButtonIterator): void
    {
        $adminListButtonIterator->add(
            AdminListButtonFactory::create(
                'btn btn-outline-info fa fa-refresh',
                'admin/spreadshirt/adminproducttype/reload',
                'Reload ProductTypes'
            )
        );
    }
}
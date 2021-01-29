<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Admin\AbstractAdminlistFilterForm;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\ProductType;

class AdminlistProductForm extends AbstractAdminlistFilterForm
{
    public static function getAdminlistForm(
        AbstractFormInterface $form,
        BaseObjectInterface $item
    ): void {
        self::addNameField($form);
        $form->_(
            'select',
            'Design',
            'filter[design]',
            ['options' => Design::class]
        )->_(
            'select',
            'ProductType',
            'filter[productType]',
            ['options' => ProductType::class]
        );
        self::addPublishedField($form);
    }
}

<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

final class PrintTypeForm extends AbstractForm implements AdminModelFormInterface
{
    public function buildForm(): void
    {
        $productionTechniques = [];
        if ($this->setting->has('SPREADSHIRT_DATAGROUP_PRODUCTIONTECHNIQUES')) :
            Item::setFindValue('datagroup', $this->setting->get('SPREADSHIRT_DATAGROUP_PRODUCTIONTECHNIQUES'));
            $productionTechniques = Item::findAll();
        endif;

        $this->addText('printTypeId', 'printTypeId', (new Attributes())->setRequired())
            ->addDropdown(
                'Productiontechnique',
                'productionTechnique',
                (new Attributes())->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions($productionTechniques))
            )
            ->addSubmitButton('%CORE_SAVE%');
    }
}

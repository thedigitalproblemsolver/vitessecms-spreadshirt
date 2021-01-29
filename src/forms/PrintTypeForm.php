<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;

/**
 * Class PrintTypeForm
 */
class PrintTypeForm extends AbstractForm implements InjectableInterface
{
    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function initialize(): void
    {
        $productionTechniques = [];
        if($this->setting->has('SPREADSHIRT_DATAGROUP_PRODUCTIONTECHNIQUES')) :
            Item::setFindValue('datagroup', $this->setting->get('SPREADSHIRT_DATAGROUP_PRODUCTIONTECHNIQUES'));
            $productionTechniques = Item::findAll();
        endif;

        $this->_(
            'text',
            'printTypeId',
            'printTypeId',
            [
                'readonly' => true
            ]
        )->_(
            'select',
            'Productiontechnique',
            'productionTechnique',
            [
                'options' => ElementHelper::arrayToSelectOptions($productionTechniques),
                'inputClass' => 'select2'
            ]
        )->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}

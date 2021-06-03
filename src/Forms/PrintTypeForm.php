<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use Phalcon\Mvc\Collection\Exception;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

/**
 * Class PrintTypeForm
 */
class PrintTypeForm extends AbstractForm implements InjectableInterface
{
    /**
     * @throws Exception
     */
    public function initialize(): void
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
                    ->setOptions(ElementHelper::arrayToSelectOptions($productionTechniques)))
            ->addSubmitButton('%CORE_SAVE%');
    }
}

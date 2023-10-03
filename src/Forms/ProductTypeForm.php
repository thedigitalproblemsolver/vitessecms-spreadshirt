<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Enums\SpreadShirtSettingEnum;

final class ProductTypeForm extends AbstractForm implements AdminModelFormInterface
{
    public function buildForm(): void
    {
        $html = '<div class="row">
            <div class="col-12 col-md-6 col-lg-2">
                <img src="' . $this->entity->_('previewImage') . '" />
            </div>';
        foreach ($this->entity->_('appearances') as $appearanceId => $appearance) :
            $html .= '<div class="col-12 col-md-6 col-lg-2">
                <img src="' . $appearance['image'] . '" />&nbsp;' . $appearance['colorName'] . ' (' . $appearanceId . ')<br />';
            foreach ($appearance['stockStates'] as $size => $stock) {
                $html .= $size . ' : ' . $stock . '<br/>';
            }
            $html .= '<br /></div>';
        endforeach;
        $html .= '</div>';

        $productTypeItems = [];
        if ($this->setting->has(SpreadShirtSettingEnum::MANUFACTURER_DATAGROUP->value)) :
            Item::setFindValue(
                'datagroup',
                $this->setting->getString(SpreadShirtSettingEnum::MANUFACTURER_DATAGROUP->value)
            );
            Item::addFindOrder('name');
            $productTypeItems = Item::findAll();
        endif;

        $this->addText('productTypeId', 'productTypeId', (new Attributes())->setReadonly())
            ->addDropdown(
                'Manufacturer',
                'manufacturer',
                (new Attributes())->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions($productTypeItems, [], true))
            )
            ->addHtml($html)
            ->addText('Introtext', 'introtext')
            ->addEditor('Bodytext', 'bodytext')
            ->addEditor('Sizetable', 'sizeTable')
            ->addSubmitButton('%CORE_SAVE%');
    }
}

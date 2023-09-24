<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Enum\SystemEnum;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Spreadshirt\Factories\PrintTypeFactory;
use VitesseCms\Spreadshirt\Models\PrintType;

final class ProductTypeForm extends AbstractForm implements AdminModelFormInterface
{
    public function buildForm(): void
    {
        Datagroup::setFindValue('component', SystemEnum::COMPONENT_WEBSHOP_PRODUCT);
        Datagroup::setFindValue('parentId', null);
        $datagroups = Datagroup::findAll();
        $itemOptions = [];
        foreach ($datagroups as $datagroup) :
            Item::setFindValue('datagroup', (string)$datagroup->getId());
            $items = Item::findAll();
            foreach ($items as $itemOption) :
                $itemOptions[(string)$itemOption->getId()] = $itemOption->_('name');
                $itemOptions = ItemHelper::buildItemTree(
                    (string)$itemOption->getId(),
                    $itemOptions
                );
            endforeach;
        endforeach;

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
        if ($this->setting->has('SPREADSHIRT_DATAGROUP_MANUFACTURER')) :
            Item::setFindValue('datagroup', $this->setting->get('SPREADSHIRT_DATAGROUP_MANUFACTURER'));
            Item::addFindOrder('name');
            $productTypeItems = Item::findAll();
        endif;

        $productTypeDTO = $this->spreadshirt->productType->get($this->entity->getInt('productTypeId'));
        $views = [];
        foreach ($productTypeDTO->views as $view) {
            $views[(int)$view->id] = $view->name;
        }
        foreach ($productTypeDTO->printAreas as $printArea) {
            $printAreas[(int)$printArea->id] = $views[(int)$printArea->defaultView->id];
        }

        $printTypesIds = [];
        foreach ($productTypeDTO->appearances as $appearance) {
            foreach ($appearance->printTypes as $printType) {
                $printTypeId = (int)$printType->id;
                if (!isset($printTypes[$printTypeId])) :
                    PrintType::setFindValue('printTypeId', $printTypeId);
                    if (PrintType::count() === 0):
                        $printTypeDTO = $this->spreadshirt->printType->get($printTypeId);
                        $type = PrintTypeFactory::create(
                            $printTypeDTO->name,
                            $printTypeId,
                            true
                        );
                        $type->save();
                    else :
                        PrintType::setFindValue('printTypeId', $printTypeId);
                        $type = PrintType::findFirst();
                    endif;
                    $printTypesIds[$printTypeId] = $type->_('name');
                endif;
            }
        }

        $this->addText('productTypeId', 'productTypeId', (new Attributes())->setReadonly())
            ->addDropdown(
                'Create product as Child of',
                'productParentItem',
                (new Attributes())->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions($itemOptions))
            )
            ->addDropdown(
                'Manufacturer',
                'manufacturer',
                (new Attributes())->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions($productTypeItems, [], true))
            )
            ->addNumber('Purchase price ex VAT', 'price_purchase', (new Attributes())->setStep(0.01))
            ->addNumber('Sale Price incl. VAT', 'price_sale', (new Attributes())->setStep(0.01))
            ->addDropdown(
                'Tax-Rate',
                'taxrate',
                (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions(TaxRate::findAll()))
            )
            ->addDropdown(
                'productTypePrintAreaId',
                'productTypePrintAreaId',
                (new Attributes())->setRequired()
                    ->setDefaultValue((int)$this->entity->_('productTypePrintAreaId'))
                    ->setOptions(ElementHelper::arrayToSelectOptions($printAreas))
            )
            ->addDropdown(
                'printTypeId',
                'printTypeId',
                (new Attributes())->setRequired()
                    ->setDefaultValue((int)$this->entity->_('printTypeId'))
                    ->setOptions(ElementHelper::arrayToSelectOptions($printTypesIds))
            )
            ->addHtml($html)
            ->addText('Introtext', 'introtext')
            ->addEditor('Bodytext', 'bodytext')
            ->addEditor('Sizetable', 'sizeTable')
            ->addSubmitButton('%CORE_SAVE%');
    }
}

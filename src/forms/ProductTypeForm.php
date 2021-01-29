<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Enum\SystemEnum;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Spreadshirt\Factories\PrintTypeFactory;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\PrintType;
use VitesseCms\Spreadshirt\Models\ProductType;

class ProductTypeForm extends AbstractForm implements ModuleInterface
{
    /**
     * @param ProductType $item
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function initialize(ProductType $item): void
    {
        Datagroup::setFindValue('component',SystemEnum::COMPONENT_WEBSHOP_PRODUCT);
        Datagroup::setFindValue('parentId',null);
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
                <img src="'.$item->_('previewImage').'" />
            </div>';
        foreach($item->_('appearances') as $appearanceId => $appearance) :
            $html .= '<div class="col-12 col-md-6 col-lg-2">
                <img src="'.$appearance['image'].'" />&nbsp;'.$appearance['colorName'].' ('.$appearanceId.')<br />';
            foreach ($appearance['stockStates'] as $size => $stock) {
                $html .= $size.' : '.$stock.'<br/>';
            }
            $html .= '<br /></div>';
        endforeach;
        $html .= '</div>';

        $productTypeItems = [];
        if($this->setting->has('SPREADSHIRT_DATAGROUP_MANUFACTURER')) :
            Item::setFindValue('datagroup', $this->setting->get('SPREADSHIRT_DATAGROUP_MANUFACTURER'));
            Item::addFindOrder('name');
            $productTypeItems = Item::findAll();
        endif;

        $productTypeXml = $this->spreadshirt->productType->get((int)$item->_('productTypeId'));
        $views = [];
        foreach($productTypeXml->views->view as $view) :
            $views[(int)XmlUtil::getAttribute($view,'id')] = (string)$view->name;
        endforeach;
        foreach($productTypeXml->printAreas->printArea as $printArea) :
            $printAreaId = (int)XmlUtil::getAttribute($printArea,'id');
            $printAreas[$printAreaId] = $views[(int)XmlUtil::getAttribute($printArea->defaultView,'id')];
        endforeach;

        $printTypesIds = [];
        foreach($productTypeXml->appearances->appearance as $appearance) :
            foreach ($appearance->printTypes->printType as $printType) :
                $printTypeId = (int) XmlUtil::getAttribute($printType,'id');
                if(!isset($printTypes[$printTypeId])) :
                    PrintType::setFindValue('printTypeId', (string)$printTypeId);
                    if( PrintType::count() === 0 ):
                        PrintType::setFindValue('printTypeId', (int)$printTypeId);
                        if( PrintType::count() === 0 ):
                            $printTypeXml = $this->spreadshirt->printType->get($printTypeId);
                            $type = PrintTypeFactory::create(
                                (string)$printTypeXml->name,
                                $printTypeId,
                                true
                            );
                            $type->save();
                        else :
                            PrintType::setFindValue('printTypeId', $printTypeId);
                            $type = PrintType::findFirst();
                        endif;
                    else :
                        PrintType::setFindValue('printTypeId', (string)$printTypeId);
                        $type = PrintType::findFirst();
                    endif;
                    $printTypesIds[$printTypeId] = $type->_('name');
                endif;
            endforeach;
        endforeach;

        $this->_(
            'text',
            'productTypeId',
            'productTypeId',
            [
                'readonly' => true
            ]
        )->_(
            'select',
            'Create product as Child of',
            'productParentItem',
            [
                'options' => ElementHelper::arrayToSelectOptions($itemOptions),
                'inputClass' => 'select2'
            ]
        )->_(
            'select',
            'Manufacturer',
            'manufacturer',
            [
                'options' => ElementHelper::arrayToSelectOptions($productTypeItems,[], true),
                'inputClass' => 'select2'
            ]
        )->_(
            'number',
            'Purchase price ex VAT',
            'price_purchase',
            [ 'step' => '0.01']
        )->_(
            'number',
            'Sale Price incl. VAT',
            'price_sale',
            [ 'step' => '0.01']
        )->_(
            'select',
            'Tax-Rate',
            'taxrate',
            [
                'options' => TaxRate::class
            ]
        )->_(
            'select',
            'productTypePrintAreaId',
            'productTypePrintAreaId',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions($printAreas),
                'value' => (int)$item->_('productTypePrintAreaId'),
            ]
        )->_(
            'select',
            'printTypeId',
            'printTypeId',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions($printTypesIds),
                'value' => (int)$item->_('printTypeId')
            ]
        )->_(
            'html',
            'html',
            'html',
            [
                'html' => $html
            ]
        )->_(
            'text',
            'Introtext',
            'introtext'
        )->_(
            'textarea',
            'Bodytext',
            'bodytext',
            [
                'inputClass' => 'editor'
            ]
        )->_(
            'textarea',
            'Sizetable',
            'sizeTable',
            [
                'inputClass' => 'editor'
            ]
        )->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}

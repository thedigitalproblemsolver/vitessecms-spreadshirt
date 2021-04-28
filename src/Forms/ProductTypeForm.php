<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use Phalcon\Mvc\Collection\Exception;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Enum\SystemEnum;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Media\Enums\AssetsEnum;
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
     * @throws Exception
     */
    public function initialize(ProductType $item): void
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
                <img src="' . $item->_('previewImage') . '" />
            </div>';
        foreach ($item->_('appearances') as $appearanceId => $appearance) :
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

        $productTypeXml = $this->spreadshirt->productType->get((int)$item->_('productTypeId'));
        $views = [];
        foreach ($productTypeXml->views->view as $view) :
            $views[(int)XmlUtil::getAttribute($view, 'id')] = (string)$view->name;
        endforeach;
        foreach ($productTypeXml->printAreas->printArea as $printArea) :
            $printAreaId = (int)XmlUtil::getAttribute($printArea, 'id');
            $printAreas[$printAreaId] = $views[(int)XmlUtil::getAttribute($printArea->defaultView, 'id')];
        endforeach;

        $printTypesIds = [];
        foreach ($productTypeXml->appearances->appearance as $appearance) :
            foreach ($appearance->printTypes->printType as $printType) :
                $printTypeId = (int)XmlUtil::getAttribute($printType, 'id');
                if (!isset($printTypes[$printTypeId])) :
                    PrintType::setFindValue('printTypeId', (string)$printTypeId);
                    if (PrintType::count() === 0):
                        PrintType::setFindValue('printTypeId', (int)$printTypeId);
                        if (PrintType::count() === 0):
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

        $this->addText('productTypeId', 'productTypeId', (new Attributes())->setReadonly())
            ->addDropdown(
                'Create product as Child of',
                'productParentItem',
                (new Attributes())->setInputClass(AssetsEnum::SELECT2)
                    ->setOptions(ElementHelper::arrayToSelectOptions($itemOptions)))
            ->addDropdown(
                'Manufacturer',
                'manufacturer',
                (new Attributes())->setInputClass(AssetsEnum::SELECT2)
                    ->setOptions(ElementHelper::arrayToSelectOptions($productTypeItems, [], true)))
            ->addNumber('Purchase price ex VAT', 'price_purchase', (new Attributes())->setStep(0.01))
            ->addNumber('Sale Price incl. VAT', 'price_sale', (new Attributes())->setStep(0.01))
            ->addDropdown(
                'Tax-Rate',
                'taxrate',
                (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions(TaxRate::findAll())))
            ->addDropdown(
                'productTypePrintAreaId',
                'productTypePrintAreaId',
                (new Attributes())->setRequired()
                    ->setDefaultValue((int)$item->_('productTypePrintAreaId'))
                    ->setOptions(ElementHelper::arrayToSelectOptions($printAreas)))
            ->addDropdown(
                'printTypeId',
                'printTypeId',
                (new Attributes())->setRequired()
                    ->setDefaultValue((int)$item->_('printTypeId'))
                    ->setOptions(ElementHelper::arrayToSelectOptions($printTypesIds)))
            ->addHtml($html)
            ->addText('Introtext', 'introtext')
            ->addEditor('Bodytext', 'bodytext')
            ->addEditor('Sizetable', 'sizeTable')
            ->addSubmitButton('%CORE_SAVE%');
    }
}

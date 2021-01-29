<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Factories\PrintTypeFactory;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\PrintType;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;

class ProductForm extends AbstractForm implements ModuleInterface
{
    public function initialize(?Product $item = null): void
    {
        $readOnly = false;
        if ($item && $item->_('productId')) :
            $readOnly = true;
        endif;

        ProductType::setFindPublished(false);
        $this->addDropdown(
            'ProductType',
            'productType',
            (new Attributes())
                ->setRequired(true)
                ->setReadonly($readOnly)
                ->setOptions(ElementHelper::arrayToSelectOptions(ProductType::findAll()))
        );

        if ($item && $item->_('productType')) :
            ProductType::setFindPublished(false);
            $productType = ProductType::findById($item->_('productType'));
            $productTypeXml = $this->spreadshirt->productType->get((int)$productType->_('productTypeId'));
            $printTypeIds = [];
            $printAreas = [];
            $views = [];
            foreach ($productTypeXml->views->view as $view) :
                $views[(int)XmlUtil::getAttribute($view, 'id')] = (string)$view->name;
            endforeach;

            foreach ($productTypeXml->appearances->appearance as $appearance) :
                foreach ($appearance->printTypes->printType as $printType) :
                    $printTypeId = (int)XmlUtil::getAttribute($printType, 'id');
                    if (!\in_array($printTypeId, $printTypeIds, true)) :
                        $printTypeIds[] = $printTypeId;
                    endif;
                endforeach;
            endforeach;

            foreach ($productTypeXml->printAreas->printArea as $printArea) :
                $printAreaId = (int)XmlUtil::getAttribute($printArea, 'id');
                $printAreas[$printAreaId] = $views[(int)XmlUtil::getAttribute($printArea->defaultView, 'id')];
            endforeach;

            $this->_(
                'select',
                'productTypePrintAreaId',
                'productTypePrintAreaId',
                [
                    'required' => 'required',
                    'options'  => ElementHelper::arrayToSelectOptions($printAreas),
                    'value'    => (int)$item->_('productTypePrintAreaId'),
                    'readonly' => $readOnly,
                ]
            );

            /*echo '<pre>';
            foreach($printTypeIds as $id) :
                $printTypeXml = $this->spreadshirt->printType->get((int)$id);
                //var_dump((string)$printTypeXml->name);
            endforeach;
            die();*/
            Design::setFindValue('printTypeIds', ['$in' => $printTypeIds]);
            $this->_(
                'select',
                'Design',
                'design',
                [
                    'required' => 'required',
                    'options'  => ElementHelper::arrayToSelectOptions(Design::findAll()),
                    'readonly' => $readOnly,
                ]
            )->_(
                'text',
                'Scale',
                'scale',
                [
                    'required' => 'required',
                ]
            )->_(
                'text',
                'Offset top',
                'offsetTop',
                [
                    'inputType' => 'number',
                ]
            );

            $combinedPrintTypeIds = [];
            if ($item->_('design')) :
                Design::setFindPublished(false);
                $design = Design::findById($item->getDesignId());
                foreach (array_intersect($design->_('printTypeIds'), $printTypeIds) as $key => $value) :
                    $printTypeXml = $this->spreadshirt->printType->get((int)$value);
                    $combinedPrintTypeIds[(int)$value] = (string)$printTypeXml->name;
                    PrintType::setFindValue('printTypeId', (string)$value);
                    if (PrintType::count() === 0):
                        PrintType::setFindValue('printTypeId', (int)$value);
                        if (PrintType::count() === 0):
                            PrintTypeFactory::create(
                                (string)$printTypeXml->name,
                                (int)$value,
                                true
                            )->save();
                        endif;
                    endif;
                endforeach;
                $this->_(
                    'select',
                    'printTypeId',
                    'printTypeId',
                    [
                        'required' => 'required',
                        'options'  => ElementHelper::arrayToSelectOptions($combinedPrintTypeIds),
                        'value'    => (int)$item->_('printTypeId'),
                        'readonly' => $readOnly,
                    ]
                );
            endif;

            if (in_array((int)$item->_('printTypeId'), [2, 16, 14], true)):
                $printType = $this->spreadshirt->printType->getById((int)$item->_('printTypeId'));
                $dropDownOptions = [];
                while ($printType->getColors()->valid()):
                    $color = $printType->getColors()->current();
                    $dropDownOptions[$color->getId()] = $color->getName().' ( '.$color->getHex().' )';
                    $printType->getColors()->next();
                endwhile;
                $this->addDropdown(
                    'PrintTypeBaseColor',
                    'PrintTypeBaseColor',
                    (new Attributes())
                        ->setOptions(ElementHelper::arrayToSelectOptions($dropDownOptions))
                        ->setRequired(true)
                );
            else :
                $item->setPrintTypeBaseColor('');
                $this->addHidden('PrintTypeBaseColor');
            endif;

            if (\is_array($item->_('appearances'))) :
                $this->_('html', '', '', ['html' => '<div class="row">']);
                foreach ((array)$item->_('appearances') as $appearance) :
                    $checked = true;
                    if (
                        \is_array($item->_('selectedVariations'))
                        && \count($item->_('selectedVariations')) > 0
                        && !isset($item->_('selectedVariations')[$appearance['color']])
                    ) :
                        $checked = false;
                    endif;

                    $this->_('html', '', '', ['html' => '<div class="col-12 col-md-6 col-lg-2">'])->_(
                        'checkbox',
                        '<img onclick="$(this).closest(\'.form-group\').toggleClass(\'selected\')" src="'.$appearance['image'].'?height=250"/>',
                        'selectedVariations['.$appearance['color'].']',
                        [
                            'checked' => $checked,
                        ]
                    )->_('html', '', '', ['html' => '</div>']);
                endforeach;
                $this->_('html', '', '', ['html' => '</div>']);
            endif;
        endif;

        if (
            $item->_('productType')
            && $item->_('productTypePrintAreaId')
            && $item->_('design')
            && $item->_('scale')
            && $item->_('printTypeId')
        ) :
            $this->_(
                'checkbox',
                'Render SpreadShirt',
                'renderSpreadShirt'
            );
            if ($item->_('published')) :
                $this->addToggle('Render webshop product', 'renderShopProduct');
            endif;
        endif;

        $this->addSubmitButton('%CORE_SAVE%');

        if ($this->spreadshirt->product->hasErrors()) :
            $this->addHtml(implode('<br/>', $this->spreadshirt->product->getErrors()));
        endif;
    }
}

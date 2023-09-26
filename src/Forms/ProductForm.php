<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;

final class ProductForm extends AbstractForm implements ModuleInterface
{
    public function initialize(?Product $item = null): void
    {
        ProductType::setFindPublished(false);
        $this->addDropdown(
            'ProductType',
            'productType',
            (new Attributes())
                ->setRequired(true)
                ->setReadonly()
                ->setOptions(ElementHelper::arrayToSelectOptions(ProductType::findAll()))
        );

        if ($item !== null) :
            $this->addDropdown(
                'Design',
                'design',
                (new Attributes())->setRequired()
                    ->setReadonly()
                    ->setOptions(ElementHelper::arrayToSelectOptions(Design::findAll()))
            );

            if ($item->appearances !== null) :
                $this->addHtml('<div class="row">');
                foreach ($item->appearances as $appearance) :
                    $this->addHtml(
                        '<div class="col-12 col-md-6 col-lg-2">
                        <img src="' . str_replace(
                            '[APPEARANCE_ID]',
                            'appearanceId=' . $appearance,
                            $item->appearanceBaseImageUrl
                        ) . '"/>
                        </div>'
                    );
                endforeach;
                $this->addHtml('</div>');
            endif;
        endif;

        /*if (
            $item->_('productType')
            && $item->_('productTypePrintAreaId')
            && $item->_('design')
            && $item->_('scale')
            && $item->_('printTypeId')
        ) :
            $this->addToggle('Render SpreadShirt', 'renderSpreadShirt');
            if ($item->_('published')) :
                $this->addToggle('Render webshop product', 'renderShopProduct');
            endif;
        endif;*/

        $this->addSubmitButton('%CORE_SAVE%');
    }
}

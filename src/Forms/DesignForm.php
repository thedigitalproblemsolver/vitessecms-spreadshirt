<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use Phalcon\Tag;
use VitesseCms\Content\Models\Item;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Design;

final class DesignForm extends AbstractForm implements ModuleInterface
{
    public function initialize(Design $item): void
    {
        /*$designCategories = $this->spreadshirt->design->getCategories();
        $designCategoryOptions = [];
        foreach ($designCategories->designCategory as $designCategory):
            $name = (string)$designCategory->name;
            $designCategoryOptions[XmlUtil::getAttribute($designCategory, 'id')] = $name;
            if (!empty($designCategory->designCategories)) {
                foreach ($designCategory->designCategories->designCategory as $category) {
                    $nameTwo = (string)$category->name;
                    $designCategoryOptions[XmlUtil::getAttribute($category, 'id')] = $name . ' > ' . $nameTwo;
                    foreach ($category->designCategories->designCategory as $c) {
                        $nameThree = (string)$c->name;
                        $designCategoryOptions[XmlUtil::getAttribute($c, 'id')] = $name . ' > ' . $nameTwo . ' > ' . $nameThree;
                    }
                }
            }
        endforeach;*/

        $datagroup = $this->setting->get('SPREADSHIRT_BASEDESIGN_DATAGROUP');
        Item::setFindValue('datagroup', $datagroup);
        Item::setFindPublished(false);
        Item::addFindOrder('name');
        $designs = Item::findAll();

        //$html = serialize($item->_('printTypeIds'));
        $html = '';
        if ($item->_('baseDesign')) {
            Item::setFindPublished(false);
            $design = Item::findById($item->_('baseDesign'));
            $file = $this->config->get('uploadDir') . $design->_('spreadshirtRasterizedImage');
            if (is_file($file)) {
                $html = '<br />' . Tag::image([
                        'src' => $this->configuration->getUploadUri() . '/' . $design->_(
                                'spreadshirtRasterizedImage'
                            ) . '?h=250'
                    ]);
            }
        }

        $this->addDropdown(
            'Base Design',
            'baseDesign',
            (new Attributes())->setRequired()->setOptions(ElementHelper::arrayToSelectOptions($designs))
        )
            ->addText('DesignId', 'designId', (new Attributes())->setReadonly())
            /*->addDropdown(
                'Design categories',
                'designCategories',
                (new Attributes())->setRequired()
                    ->setMultiple()
                    ->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions($designCategoryOptions))
            )*/
            //->addNumber('Design price on marketplace', 'designPrice')
            ->addHtml($html)
            /*->addText(
                'Scale',
                'scale',
                (new Attributes())->setRequired()
            )*/ ->addSubmitButton('%CORE_SAVE%');
    }
}

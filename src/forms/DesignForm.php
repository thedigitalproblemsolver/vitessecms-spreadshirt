<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Design;
use Phalcon\Tag;

class DesignForm extends AbstractForm implements ModuleInterface
{
    public function initialize(Design $item): void
    {
        $designCategories = $this->spreadshirt->design->getCategories();
        $designCategoryOptions = [];
        foreach ($designCategories->designCategory as $designCategory):
            $name = (string)$designCategory->name;
            $designCategoryOptions[XmlUtil::getAttribute($designCategory, 'id')] = $name;
            if (!empty($designCategory->designCategories)) {
                foreach ($designCategory->designCategories->designCategory as $category) {
                    $nameTwo = (string)$category->name;
                    $designCategoryOptions[XmlUtil::getAttribute($category, 'id')] = $name.' > '.$nameTwo;
                    foreach ($category->designCategories->designCategory as $c) {
                        $nameThree = (string)$c->name;
                        $designCategoryOptions[XmlUtil::getAttribute($c, 'id')] = $name.' > '.$nameTwo.' > '.$nameThree;
                    }
                }
            }
        endforeach;

        $datagroup = $this->setting->get('SPREADSHIRT_BASEDESIGN_DATAGROUP');
        Item::setFindValue('datagroup', $datagroup);
        Item::setFindPublished(false);
        $designs = Item::findAll();

        $html = serialize($item->_('printTypeIds'));
        if ($item->_('baseDesign')) :
            Item::setFindPublished(false);
            $design = Item::findById($item->_('baseDesign'));
            $file = $this->config->get('uploadDir').$design->_('spreadshirtRasterizedImage');
            if (is_file($file)) :
                $html .= '<br />'.Tag::image([
                        'src' => $this->configuration->getUploadUri().'/'.$design->_('spreadshirtRasterizedImage').'?h=250'
                    ]);
            endif;
        endif;

        $this->_(
            'select',
            'Base Design',
            'baseDesign',
            [
                'options'  => ElementHelper::arrayToSelectOptions($designs),
                'required' => 'required',

            ]
        )->_(
            'text',
            'DesignId',
            'designId',
            [
                'readonly' => true,
            ]
        )->_(
            'select',
            'Design categories',
            'designCategories',
            [
                'options'  => ElementHelper::arrayToSelectOptions($designCategoryOptions),
                'required' => 'required',
                'multiple' => true,
                'inputClass' => 'select2'
            ]
        )->_(
            'number',
            'Design price on marketplace',
            'designPrice'
        )->_(
            'html',
            'html',
            'html',
            [
                'html' => $html,
            ]
        )->_(
            'text',
            'Scale',
            'scale',
            [
                'required' => 'required',
            ]
        )->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}

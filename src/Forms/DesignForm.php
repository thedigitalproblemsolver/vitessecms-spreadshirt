<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use Phalcon\Tag;
use VitesseCms\Content\Models\Item;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Enums\SpreadShirtSettingEnum;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Design;

final class DesignForm extends AbstractForm implements ModuleInterface
{
    public function initialize(Design $item): void
    {
        $datagroup = $this->setting->get(SpreadShirtSettingEnum::DESIGN_DATAGROUP->value);
        Item::setFindValue('datagroup', $datagroup);
        Item::setFindPublished(false);
        Item::addFindOrder('name');
        $designs = Item::findAll();
        
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
            ->addHtml($html)
            ->addSubmitButton('%CORE_SAVE%');
    }
}

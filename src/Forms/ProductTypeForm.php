<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use stdClass;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Content\Enum\ItemEnum;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Enum\SystemEnum;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Datagroup\Enums\DatagroupEnum;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Enums\SpreadShirtSettingEnum;

final class ProductTypeForm extends AbstractForm implements AdminModelFormInterface
{
    private readonly DatagroupRepository $datagroupRepository;
    private readonly ItemRepository $itemRepository;

    public function __construct($entity = null, array $userOptions = [])
    {
        parent::__construct($entity, $userOptions);

        $this->datagroupRepository = $this->eventsManager->fire(DatagroupEnum::GET_REPOSITORY->value, new stdClass());
        $this->itemRepository = $this->eventsManager->fire(ItemEnum::GET_REPOSITORY, new stdClass());
    }

    public function buildForm(): void
    {
        $datagroups = $this->datagroupRepository->findAll(
            new FindValueIterator([
                new FindValue('component', SystemEnum::COMPONENT_WEBSHOP_PRODUCT),
                new FindValue('parentId', null)
            ])
        );
        $itemOptions = [];
        while ($datagroups->valid()) {
            $datagroup = $datagroups->current();
            $items = $this->itemRepository->findAll(
                new FindValueIterator([new FindValue('datagroup', (string)$datagroup->getId())])
            );
            foreach ($items as $itemOption) :
                $itemOptions[(string)$itemOption->getId()] = $itemOption->_('name');
                $itemOptions = ItemHelper::buildItemTree((string)$itemOption->getId(), $itemOptions);
            endforeach;
            $datagroups->next();
        }

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
            ->addHtml($html)
            ->addText('Introtext', 'introtext')
            ->addEditor('Bodytext', 'bodytext')
            ->addEditor('Sizetable', 'sizeTable')
            ->addSubmitButton('%CORE_SAVE%');
    }
}

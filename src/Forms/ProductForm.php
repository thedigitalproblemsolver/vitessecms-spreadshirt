<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Forms;

use stdClass;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Content\Enum\ItemEnum;
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
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\ProductType;

final class ProductForm extends AbstractForm implements AdminModelFormInterface
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
        ProductType::setFindPublished(false);
        $this->addDropdown(
            'ProductType',
            'productType',
            (new Attributes())
                ->setRequired(true)
                ->setReadonly()
                ->setOptions(ElementHelper::arrayToSelectOptions(ProductType::findAll()))
        );

        if ($this->entity !== null) :
            $this->addDropdown(
                'Design',
                'design',
                (new Attributes())->setRequired()
                    ->setReadonly()
                    ->setOptions(ElementHelper::arrayToSelectOptions(Design::findAll()))
            )->addDropdown(
                'Create product as Child of',
                'productParentItem',
                (new Attributes())->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions($this->getItemOptions()))
            );

            if ($this->entity->appearances !== null) :
                $this->addHtml('<div class="row">');
                foreach ($this->entity->appearances as $appearance) :
                    $this->addHtml(
                        '<div class="col-12 col-md-6 col-lg-2">
                        <img src="' . str_replace(
                            '[APPEARANCE_ID]',
                            'appearanceId=' . $appearance,
                            $this->entity->appearanceBaseImageUrl
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

    private function getItemOptions(): array
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

        return $itemOptions;
    }
}

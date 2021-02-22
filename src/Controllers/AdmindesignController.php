<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Database\Interfaces\BaseCollectionInterface;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Form\Forms\BaseForm;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Spreadshirt\Factories\DesignFactory;
use VitesseCms\Spreadshirt\Factories\ProductFactory;
use VitesseCms\Spreadshirt\Forms\DesignForm;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;

class AdmindesignController
    extends AbstractAdminController
    implements ModuleInterface, AdminRepositoriesInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Design::class;
        $this->classForm = DesignForm::class;
    }

    public function afterPublish(BaseCollectionInterface $item): void
    {
        $products = $this->repositories->product
            ->getByDesign((string)$item->getId(), false);

        while ($products->valid()) :
            $product = $products->current();
            $ItemIsPublished = $item->isPublished();
            $productType = $this->repositories->productType->getById(
                $product->getProductTypeId(), false
            );
            if ($ItemIsPublished && ($productType === null || !$productType->isPublished())) :
                $ItemIsPublished = false;
            endif;

            $shopItems = $this->repositories->item->findAll(
                new FindValueIterator(
                    [new FindValue('spreadShirtProductId', (string)$product->getId())]
                ),
                false
            );
            while ($shopItems->valid()) :
                $shopItem = $shopItems->current();
                $shopItem->setPublished($ItemIsPublished)->save();
                $shopItems->next();
            endwhile;

            $product->setPublished($ItemIsPublished)->save();
            $products->next();
        endwhile;
    }

    public function generateProductsAction(string $designId): void
    {
        $design = Design::findById($designId);
        $productTypes = ProductType::findAll();
        $counter = 0;
        foreach ($productTypes as $productType) :
            Product::setFindValue('design', $designId);
            Product::setFindValue('productType', (string)$productType->getId());
            Product::setFindPublished(false);
            if (
                Product::count() === 0
                && !empty($productType->_('productTypePrintAreaId'))
                && !empty($productType->_('printTypeId'))
                && !empty($design->_('scale'))
            ) :
                ProductFactory::create(
                    (string)$productType->getId(),
                    $productType->_('productTypePrintAreaId'),
                    $designId,
                    $productType->_('printTypeId'),
                    (float)$design->_('scale')
                )->save();
                $counter++;
            endif;
        endforeach;
        $this->flash->message('notice', $counter . ' products created');
        $this->redirect();
    }

    public function importFormAction(): void
    {
        $spreadshirtDesigns = $this->spreadshirt->design->getAll();

        $form = new BaseForm();
        foreach ($spreadshirtDesigns->design as $design):
            $designId = XmlUtil::getAttribute($design, 'id');
            $designs = $this->repositories->design->countAll(
                new FindValueIterator([new FindValue('designId', $designId)]),
                false
            );
            if ($designs === 0) :
                $form->addToggle(
                    (string)$design->name,
                    'design[' . $designId . ']',
                    (new Attributes())->setDefaultValue($designId)
                );
            endif;
        endforeach;

        $form->addSubmitButton('Import');
        $this->view->setVar('content', $form->renderForm('admin/spreadshirt/admindesign/parseImportForm'));

        $this->prepareView();
    }

    public function parseImportFormAction(): void
    {
        foreach ($this->request->get('design') as $designId):
            $designs = $this->repositories->design->countAll(
                new FindValueIterator([new FindValue('designId', $designId)]),
                false
            );
            if ($designs === 0) :
                $design = $this->spreadshirt->design->get($designId);
                DesignFactory::create((string)$design->name, $designId)->save();
            endif;
        endforeach;

        $this->flash->setSucces('The designs are imported');
        $this->redirect();
    }
}

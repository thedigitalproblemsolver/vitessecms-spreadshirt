<?php
declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Content\Factories\ItemFactory;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\AbstractController;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Shop\Enum\SizeAndColorEnum;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\PrintType;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;

class ProductController extends AbstractController implements ModuleInterface
{
    public function renderShopProductAction(): void
    {
        Product::setFindValue('renderShopProduct', '1');
        $product = Product::findFirst();
        if ($product) {
            $this->renderShopProduct($product);
            $product->set('renderShopProduct', false)->save();
        }

        $this->disableView();
    }

    protected function renderShopProduct(AbstractCollection $item): void
    {
        if ($item->_('design') && $item->_('productType')) :
            $productType = ProductType::findById($item->_('productType'));
            if ($productType->_('productParentItem')) :
                Item::setFindPublished(false);
                Item::setFindValue('spreadShirtProductId', (string)$item->getId());
                if (Item::count() === 0) :
                    $design = Design::findById($item->_('design'));
                    $taxrate = TaxRate::findById($productType->_('taxrate'));
                    Item::setFindValue('parentId', $productType->_('productParentItem'));
                    $exampleItem = Item::findFirst();
                    PrintType::setFindValue('printTypeId', $item->_('printTypeId'));
                    $printType = PrintType::findFirst();

                    $minDelivery = $maxDelivery = '';
                    if (!empty($this->setting->get('SPREADSHIRT_MIN_DELIVERY'))) :
                        Setting::setFindValue('calling_name', 'SPREADSHIRT_MIN_DELIVERY');
                        $minDeliverySetting = Setting::findFirst();
                        $minDelivery = $minDeliverySetting->value;
                    endif;
                    if (!empty($this->setting->get('SPREADSHIRT_MAX_DELIVERY'))) :
                        Setting::setFindValue('calling_name', 'SPREADSHIRT_MAX_DELIVERY');
                        $maxDeliverySetting = Setting::findFirst();
                        $maxDelivery = $maxDeliverySetting->value;
                    endif;

                    if ($exampleItem) {
                        $newItem = ItemFactory::create(
                            $design->_('name'),
                            $exampleItem->_('datagroup'),
                            [],
                            false,
                            $productType->_('productParentItem')
                        )
                            ->set('spreadShirtProductId', (string)$item->getId())
                            ->set('taxrate', $productType->_('taxrate'))
                            ->set('price_purchase', $productType->_('price_purchase'))
                            ->set('price_sale', $productType->_('price_sale'))
                            ->set('manufacturer', $productType->_('manufacturer'))
                            ->set('design', $design->_('baseDesign'))
                            ->set('manufacturingTechnique', $printType->_('productionTechnique'))
                            ->set(
                                'price',
                                $productType->_('price_sale') / (100 + (float)$taxrate->_('taxrate')) * 100
                            )
                            ->set('gender', $exampleItem->_('gender'))
                            ->set('minimalDeliveryTime', $minDelivery)
                            ->set('maximumDeliveryTime', $maxDelivery);
                        $this->setVariations($item, $newItem, $productType);
                        $newItem->set('published', true);
                        $newItem->set('addtocart', true);
                        $newItem->save();
                    } else {
                        die('Geen voorbeeld item gevonden');
                    }
                endif;
            endif;
        endif;
    }

    protected function setVariations(
        AbstractCollection $item,
        AbstractCollection $newItem,
        ProductType $productType
    ): void {
        $productTypeXml = $this->spreadshirt->productType->get((int)$productType->_('productTypeId'));
        $sizes = $variations = [];
        foreach ($productTypeXml->sizes->size as $size) :
            $sizes[] = (string)$size->name;
        endforeach;

        $imageDir = $this->config->get('uploadDir');
        foreach ((array)$item->_('appearances') as $appearance) :
            if (isset($item->_('selectedVariations')[$appearance['color']])) :
                $imageFile = 'products/' . FileUtil::sanatize(
                        $item->_('name') . ' ' . $appearance['colorName']
                    ) . '.jpg';
                if (!is_file($imageDir . $imageFile)) :
                    file_put_contents(
                        $imageDir . $imageFile,
                        $this->spreadshirt->product->getUrl($appearance['image'] . '.jpg?height=1200')
                    );
                endif;

                if (empty($newItem->_('image'))) :
                    $newItem->set('image', $imageFile);
                endif;

                foreach ($sizes as $size) :
                    if (isset(SizeAndColorEnum::sizes[$size])) :
                        if (strtolower($size) === 'one size') :
                            $size = 'ONE SIZE';
                        endif;

                        if (isset($productType->_('appearances')[$appearance['colorId']]['stockStates'][$size])) :
                            $variations[] = [
                                'sku' => str_replace(
                                    ' ',
                                    '_',
                                    strtoupper($appearance['colorName'] . '_' . $size)
                                ),
                                'size' => $size,
                                'color' => $appearance['color'],
                                'stock' => (int)$productType->_(
                                    'appearances'
                                )[$appearance['colorId']]['stockStates'][$size],
                                'stockMinimal' => 10,
                                'ean' => '',
                                'image' => [$imageFile],
                            ];
                        endif;
                    else :
                        die('Maat ' . $size . ' niet in systeem');
                    endif;
                endforeach;
            endif;
        endforeach;

        $newItem->set('variations', $variations);
    }

    //https://craftbeershirts.nl/spreadshirt/product/moveDesign
    public function moveDesignAction(): void
    {
        /*$imageDir = $this->config->get('uploadDir');

        Product::setFindValue('design','5b6b41b5583b4175c874cd59');
        $products = Product::findAll();
        foreach ($products as $product) :
            foreach ((array)$product->_('appearances') as $appearance) :
                $imageFile = 'products/'.FileUtil::sanatize($product->_('name').' '.$appearance['colorName']).'jpg';
                echo $imageDir.$imageFile;
                var_dump(file_put_contents(
                    $imageDir.$imageFile,
                    $this->spreadshirt->product->getUrl($appearance['image'].'.jpg?height=1200')
                ));
                echo '<br />';
                //$product->set('design', '5b6b41b5583b4175c874cd59')->save();
            endforeach;
        endforeach;*/
        echo 'klaar';
        die();
    }
}

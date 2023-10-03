<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Models;

use Phalcon\Events\Event;
use VitesseCms\Content\Factories\ItemFactory;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Log\Services\LogService;
use VitesseCms\Setting\Services\SettingService;
use VitesseCms\Shop\Enum\SizeAndColorEnum;
use VitesseCms\Spreadshirt\Enums\ShopEnum;
use VitesseCms\Spreadshirt\Enums\SpreadShirtSettingEnum;
use VitesseCms\Spreadshirt\Helpers\ProductTypeHelper;
use VitesseCms\Spreadshirt\Models\Design;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;

final class ProductListener
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ItemRepository $itemRepository,
        private readonly ProductTypeRepository $productTypeRepository,
        private readonly DesignRepository $designRepository,
        private readonly SettingService $settingService,
        private readonly ProductTypeHelper $productTypeHelper,
        private readonly string $uploadDir,
        private readonly LogService $logService
    ) {
    }

    public function getRepository(): ProductRepository
    {
        return $this->productRepository;
    }

    public function convertToShopProduct(Event $event, Product $product): void
    {
        $shopProduct = $this->itemRepository->findFirst(
            new FindValueIterator([
                new FindValue(ShopEnum::SPREADSHIRT_PRODUCT_ID_FIELDNAME->value, (string)$product->getId())
            ]),
            false
        );
        $productType = $this->productTypeRepository->getById($product->productType);
        $design = $this->designRepository->getById($product->design);
        $parentId = $this->getParentId($product, $productType, $design);
        if ($parentId === null) {
            $this->logService->write(
                $productType->getId(),
                $productType::class,
                'ProductType <b>' . $productType->getNameField() . '</b>: missing a ShopCategory'
            );
        } else {
            if ($shopProduct === null) {
                $category = $this->itemRepository->getById($parentId);

                $shopProduct = ItemFactory::create(
                    $design->getNameField(),
                    $this->settingService->getString(SpreadShirtSettingEnum::BASEPRODUCT_DATAGROUP->value),
                    [],
                    false,
                    $parentId
                );
                $shopProduct->set('spreadShirtProductId', (string)$product->getId());
                $shopProduct->set(
                    'taxrate',
                    $this->settingService->getString(SpreadShirtSettingEnum::PRODUCT_TAXRATE->value)
                );
                //$shopProduct->set('price_purchase', $productType->_('price_purchase'));
                $shopProduct->set('manufacturer', $productType->manufacturer);
                $shopProduct->set('design', $design->baseDesign);
                //$shopProduct->set('manufacturingTechnique', $printType->_('productionTechnique'));
                /*$shopProduct->set(
                    'price',
                    $productType->_('price_sale') / (100 + (float)$taxrate->_('taxrate')) * 100
                );*/
                $shopProduct->set('gender', $category->getParentId());
                $shopProduct->setPublished(true);
                $shopProduct->set('addtocart', true);
                echo 'nieuw product';
            }
            $shopProduct->set('price_sale', $product->priceSale);
            $shopProduct->set('price', $product->priceSale);
            $shopProduct->set(
                'minimalDeliveryTime',
                $this->settingService->getRaw(SpreadShirtSettingEnum::MIN_DELIVERY->value)
            );
            $shopProduct->set(
                'maximumDeliveryTime',
                $this->settingService->getRaw(SpreadShirtSettingEnum::MAX_DELIVERY->value)
            );
            $this->setVariations($product, $shopProduct, $productType);
            $shopProduct->save();
        }
    }

    private function getParentId(Product $product, ProductType $productType, Design $design): ?string
    {
        $designItem = $this->itemRepository->getById($design->baseDesign);
        if ($designItem->getString('shopDesignBaseStore') === '') {
            $this->logService->write(
                $designItem->getId(),
                $designItem::class,
                'Design <b>' . $designItem->getNameField() . '</b>: shopDesignBaseStore is empty'
            );

            return null;
        }

        $genders = $this->itemRepository->findAll(
            new FindValueIterator([new FindValue('parentId', $designItem->getString('shopDesignBaseStore'))]),
            false
        );

        $genderIds = [];
        while ($genders->valid()) {
            $gender = $genders->current();
            $genderIds[] = (string)$gender->getId();
            $genders->next();
        }

        $productItem = $this->itemRepository->findFirst(
            new FindValueIterator([
                new FindValue('spreadshirtProductTypes', (string)$productType->getId()),
                new FindValue('parentId', $genderIds, 'in')
            ])
        );

        if ($productItem !== null) {
            return (string)$productItem->getId();
        }

        return null;
    }

    private function setVariations(Product $product, Item $shopProduct, ProductType $productType): void
    {
        $sizes = $variations = [];
        $baseImageUrl = str_replace(
            ['width=500', 'height=500'],
            ['width=1200', 'height=1200'],
            $product->appearanceBaseImageUrl
        );

        $productTypeDTO = $this->productTypeHelper->get($productType->productTypeId);
        foreach ($productTypeDTO->sizes as $size) :
            $sizes[$size->id] = (string)$size->name;
        endforeach;

        foreach ($productTypeDTO->appearances as $appearance) {
            if (in_array($appearance->id, $product->appearances)) {
                $imageFile = 'products/' . FileUtil::sanatize(
                        $product->getNameField() . ' ' . str_replace('/', ' ', $appearance->name) . '.jpg'
                    );
                if (!is_file($this->uploadDir . $imageFile)) {
                    $options = [
                        'http' => [
                            'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36' . rand(
                                )
                        ]
                    ];
                    $context = stream_context_create($options);
                    file_put_contents(
                        $this->uploadDir . $imageFile,
                        file_get_contents(
                            str_replace('[APPEARANCE_ID]', 'appearanceId=' . $appearance->id, $baseImageUrl),
                            false,
                            $context
                        )
                    );
                }

                if (empty($shopProduct->_('image'))) {
                    $shopProduct->set('image', $imageFile);
                }
                foreach ($productTypeDTO->stockStates as $sockState) {
                    if (
                        $sockState->available === true &&
                        $sockState->appearance->id === $appearance->id &&
                        isset($sizes[$sockState->size->id]) &&
                        isset(SizeAndColorEnum::sizes[$sizes[$sockState->size->id]])
                    ) {
                        $size = $sizes[$sockState->size->id];
                        if (strtolower($size) === 'one size') :
                            $size = 'ONE SIZE';
                        endif;

                        $variations[] = [
                            'sku' => str_replace(
                                [' ', '/'],
                                '_',
                                strtoupper($appearance->name . '_' . $size)
                            ),
                            'size' => $size,
                            'color' => $appearance->colors[0]->value,
                            'stock' => $sockState->quantity,
                            'stockMinimal' => 10,
                            'ean' => '',
                            'image' => [$imageFile],
                        ];
                    }
                }
            }
        }
        $shopProduct->set('variations', $variations);
    }
}
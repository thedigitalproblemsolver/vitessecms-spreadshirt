<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use MongoDB\BSON\ObjectId;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Basket;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;

final class AdmincheckoutController
    extends AbstractAdminController
    implements ModuleInterface
{
    public function indexAction(): void
    {
        Order::setFindValue('orderState.calling_name', OrderStateEnum::PAID);
        Order::setFindLimit(99);
        Order::addFindOrder('orderId', -1);
        $orders = Order::findAll();

        $this->view->setVar(
            'content',
            $this->view->renderTemplate(
                'checkout',
                $this->configuration->getVendorNameDir() . 'spreadshirt/src/Resources/views/admin/',
                ['orders' => $orders]
            )
        );
        $this->prepareView();
    }

    public function orderProductsAction(): void
    {
        $redirect = null;
        if ($this->request->isPost()) {
            $basket = new Basket();
            $basket->setId(new ObjectId());
            foreach ($this->request->get('products') as $productId => $product) {
                if (!empty($product['quantity'])) {
                    $spreadShirtProduct = Product::findById($productId);
                    ProductType::setFindPublished(false);
                    $productType = ProductType::findById($spreadShirtProduct->_('productType'));
                    foreach ($product['quantity'] as $variation => $quantity) {
                        $variation = array_reverse(
                            explode(
                                '_',
                                str_replace('ONE_SIZE', 'ONE SIZE', $variation)
                            )
                        );
                        $size = $variation[0];
                        unset($variation[0]);
                        $colorName = strtolower(implode(' ', array_reverse($variation)));
                        $appearance = [];
                        $hasAppearance = false;
                        foreach ($productType->appearances as $appearance) :
                            if (
                                $appearance['colorName'] === $colorName ||
                                str_replace('_', ' ', $appearance['colorName']) === $colorName
                            ) {
                                $hasAppearance = true;
                                break;
                            }
                        endforeach;

                        if ($hasAppearance) {
                            if ($size === 'One-size' || $size === 'ONE_SIZE') {
                                $orderSize = '29';
                            } else {
                                $orderSize = $productType->_('sizesMap')[$size];
                            }

                            $this->spreadshirt->basket->addItem(
                                $spreadShirtProduct->sellableId,
                                (int)$quantity,
                                (string)$orderSize,
                                (string)$appearance['colorId']
                            );
                        }
                    }
                }
            }

            $spreadshirtBasket = $this->spreadshirt->basket->create();
            $basket->set('basketId', $spreadshirtBasket->id);
            $basket->save();
            $redirect = $this->spreadshirt->basket->getCheckoutUrl($spreadshirtBasket->id);
        }

        $this->redirect($redirect);
    }
}

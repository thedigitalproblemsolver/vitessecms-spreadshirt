<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Spreadshirt\Interfaces\ModuleInterface;
use VitesseCms\Spreadshirt\Models\Basket;
use VitesseCms\Spreadshirt\Models\Product;
use VitesseCms\Spreadshirt\Models\ProductType;
use MongoDB\BSON\ObjectId;

class AdmincheckoutController
    extends AbstractAdminController
    implements ModuleInterface
{
    public function indexAction(): void
    {
        Order::setFindValue('orderState.calling_name', OrderStateEnum::PAID);
        //Order::setFindValue('orderState.calling_name', ['$in' => [OrderStateEnum::PAID, OrderStateEnum::PREORDER]]);
        Order::setFindLimit(99);
        Order::addFindOrder('orderId', -1);
        $orders = Order::findAll();
        //echo '<pre>';
        /*$items = $orders[0]->_('items');
        $items['products'][1]['spreadShirtProductId'] = '5b3736f0583b4107e946db44';
        $orders[0]->set('items', $items);*/

        //var_dump($orders[0]->_('items')['products']);
        //die();

        /*ViewHelper::setVar('orders', $orders);
        ViewHelper::setTemplatePath($this->config->get('rootDir').'src/spreadshirt/Resources/views/admin/');
        ViewHelper::setTemplate('checkout');*/

        $this->view->setVar('content', $this->view->renderTemplate(
            'checkout',
            $this->configuration->getRootDir().'src/spreadshirt/Resources/views/admin/',
            ['orders' => $orders]
        ));
        $this->prepareView();
    }

    public function orderProductsAction(): void
    {
        $redirect = null;
        if ($this->request->isPost()) {
            $basket = new Basket();
            $basket->setId(new ObjectId());
            $basket->set('basketId', $this->spreadshirt->basket->create((string)$basket->getId()));
            //echo '<pre>';
            foreach ($this->request->get('products') as $productId => $product) :
                if (!empty($product['quantity'])) :
                    $spreadShirtProduct = Product::findById($productId);
                    ProductType::setFindPublished(false);
                    $productType = ProductType::findById($spreadShirtProduct->_('productType'));
                    foreach ($product['quantity'] as $variation => $quantity) :
                        //var_dump($variation);
                        $variation = array_reverse(explode('_',
                            str_replace('ONE_SIZE', 'ONE SIZE', $variation)
                        ));
                        $size = $variation[0];
                        unset($variation[0]);
                        $colorName = strtolower(implode(' ', array_reverse($variation)));
                        $appearance = [];
                        $hasAppearance = false;
                        /*                        var_dump($colorName);
                                                var_dump($spreadShirtProduct->_('appearances')['productId'] == 510671387 );
                                                die();*/
                        foreach ($spreadShirtProduct->_('appearances') as $appearance) :
                            /*if($appearance['productId'] == 510671387) {
                                $appearance
                            }*/
                            if (in_array($appearance['productId'], [510671387, 511309387])) {
                                $colorName = 'white';
                            }
                            if (
                                $appearance['colorName'] === $colorName ||
                                str_replace('_', ' ', $appearance['colorName']) === $colorName
                            ):
                                $hasAppearance = true;
                                break;
                            endif;
                        endforeach;
                        /*                    string(224) "<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <error xmlns="http://api.spreadshirt.net"><message>ProductType is out of stock. ProductType id: 6, SizeId: 38, AppearanceId: 388</message><error>086011</error></error>
                        "
                        string(79) "https://checkout.spreadshirt.net/?basketId=62a3f016-8a9e-4542-8711-e539c4c91ad7"*/
                        //var_dump($hasAppearance);

                        if ($hasAppearance) :
                            if ($size === 'One-size' || $size === 'ONE_SIZE') :
                                $orderSize = '29';
                            else :
                                $orderSize = $productType->_('sizesMap')[$size];
                            endif;

                            $this->spreadshirt->basket->addItem(
                                $basket->_('basketId'),
                                (string) $appearance['productId'],
                                (int) $quantity,
                                (string) $orderSize,
                                (string) $appearance['colorId']
                            );
                        endif;
                    endforeach;
                endif;
            endforeach;
            //die();
            $redirect = $this->spreadshirt->basket->getCheckoutUrl($basket->_('basketId'));
        }

        $this->redirect($redirect);
    }
}

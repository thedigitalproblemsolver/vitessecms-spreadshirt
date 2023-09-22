<?php

namespace VitesseCms\Spreadshirt\Helpers;

use Phalcon\Events\Manager;
use SimpleXMLElement;
use VitesseCms\Core\Utils\XmlUtil;

/**
 * Class BasketHelper
 */
class BasketHelper extends AbstractSpreadShirtHelper
{
    protected string $basketUrl;

    public function __construct(Manager $eventsManager)
    {
        parent::__construct($eventsManager);

        $this->basketUrl = 'https://api.spreadshirt.net/api/v1/baskets';
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function create(string $token): string
    {
        $basketXml = new SimpleXMLElement(
            '<basket xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://api.spreadshirt.net">
    <token>' . $token . '</token>
</basket>'
        );
        $ch = $this->getCurlInstance($this->basketUrl, 'POST', 'application/xml');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $basketXml->asXML());
        $result = curl_exec($ch);
        curl_close($ch);

        return XmlUtil::getAttribute(new SimpleXMLElement($result), 'id');
    }

    /**
     * @param string $basketId
     * @param string $productId
     * @param int $quantity
     * @param string $size
     * @param string $appearance
     *
     * @return SimpleXMLElement
     */
    public function addItem(
        string $basketId,
        string $productId,
        int $quantity,
        string $size,
        string $appearance

    ): SimpleXMLElement {
        $itemXml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <basketItem xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://api.spreadshirt.net">
           <quantity>' . $quantity . '</quantity>
           <element id="' . $productId . '" type="sprd:product" xlink:href="https://api.spreadshirt.net/api/v1/shops/' . $this->shopId . '/products/' . $productId . '">
              <properties>
              <property key="appearance">' . $appearance . '</property>
                 <property key="size">' . $size . '</property>
              </properties>
           </element>
        </basketItem>'
        );

        $ch = $this->getCurlInstance($this->basketUrl . '/' . $basketId . '/items', 'POST', 'application/xml');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $itemXml->asXML());
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }

    /**
     * @param string $basketId
     *
     * @return string
     */
    public function getCheckoutUrl(string $basketId): string
    {
        $result = new SimpleXMLElement($this->getUrl($this->basketUrl . '/' . $basketId . '/checkout'));
        $namespaces = $result->getNamespaces(true);

        return (string)$result->attributes($namespaces['xlink']);
    }
}

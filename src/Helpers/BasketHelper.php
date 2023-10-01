<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use Phalcon\Events\Manager;
use stdClass;

final class BasketHelper extends AbstractSpreadShirtHelper
{
    private string $basketUrl;
    private array $basketItems;

    public function __construct(Manager $eventsManager)
    {
        parent::__construct($eventsManager);

        $this->basketUrl = 'https://api.spreadshirt.net/api/v1/baskets';
        $this->basketItems = ['basketItems' => []];
    }

    public function create(): stdClass
    {
        $ch = $this->getCurlInstance($this->basketUrl . '?mediaType=json', 'POST', 'application/json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->basketItems));
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result);
    }

    public function addItem(
        string $productId,
        int $quantity,
        string $size,
        string $appearance

    ): void {
        $this->basketItems['basketItems'][] = [
            'quantity' => $quantity,
            'element' => [
                'id' => $productId,
                'type' => 'sprd:sellable',
                'properties' => [
                    [
                        'key' => 'size',
                        'value' => $size
                    ],
                    [
                        'key' => 'appearance',
                        'value' => $appearance
                    ],
                ],
                'shop' => [
                    'id' => $this->shopId
                ]
            ]
        ];
    }

    public function getCheckoutUrl(string $basketId): string
    {
        $result = json_decode($this->getUrl($this->basketUrl . '/' . $basketId . '?mediaType=json'));

        return $result->links[0]->href;
    }
}

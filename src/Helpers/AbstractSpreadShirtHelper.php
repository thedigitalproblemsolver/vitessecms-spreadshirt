<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use Phalcon\Events\Manager;
use SimpleXMLElement;
use stdClass;
use VitesseCms\Core\Enum\ViewEnum;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Mustache\DTO\RenderTemplateDTO;
use VitesseCms\Setting\Enum\SettingEnum;
use VitesseCms\Setting\Services\SettingService;

abstract class AbstractSpreadShirtHelper
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $shopId;
    protected string $userId;
    protected string $baseUrl;
    protected string $userUrl;
    protected string $apiLogin;
    protected string $apiPassword;
    protected ?string $sessionId;
    protected ViewService $viewService;
    protected SettingService $settingService;

    public function __construct(protected readonly Manager $eventsManager)
    {
        $this->viewService = $this->eventsManager->fire(ViewEnum::ATTACH_SERVICE_LISTENER, new stdClass());
        $this->settingService = $this->eventsManager->fire(SettingEnum::ATTACH_SERVICE_LISTENER->value, new stdClass());

        $this->apiKey = $this->settingService->getString('SPREADSHIRT_API_KEY');
        $this->shopId = $this->settingService->getString('SPREADSHIRT_SHOP_ID');
        $this->userId = $this->settingService->getString('SPREADSHIRT_USER_ID');
        $this->apiLogin = $this->settingService->getString('SPREADSHIRT_API_LOGIN');
        $this->apiPassword = $this->settingService->getString('SPREADSHIRT_API_PASSWORD');
        $this->apiSecret = $this->settingService->getString('SPREADSHIRT_API_SECRET');
        $this->baseUrl = 'https://api.spreadshirt.net/api/v1/shops/' . $this->shopId . '/';
        $this->userUrl = 'https://api.spreadshirt.net/api/v1/users/' . $this->userId . '/';
        $this->sessionId = null;
    }

    public function getUrl(string $url, string $method = 'GET'): string
    {
        $ch = $this->getCurlInstance($url, strtoupper($method));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    protected function getCurlInstance(
        string $url,
        string $method,
        ?string $contentType = null,
        bool $login = false
    ) {
        $headers = [
            $this->createSprdAuthHeader($method, $url, $login),
            'User-Agent: CraftBeerShirts/1.0 (https://craftbeermerchandise.com; info@craftbeermerchandise.com)',
        ];
        if ($contentType):
            $headers[] = 'Content-Type: ' . $contentType;
        endif;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return $ch;
    }

    protected function createSprdAuthHeader(string $method, string $url, bool $login = false): string
    {
        $time = time() * 1000;

        $data = $method . ' ' . $url . ' ' . $time;
        $sig = sha1($data . ' ' . $this->apiSecret);

        $return = 'Authorization: SprdAuth apiKey="' . $this->apiKey . '", data="' . $data . '", sig="' . $sig . '"';

        if ($login) :
            $this->login();
            $return .= ', sessionId="' . $this->sessionId . '"';
        endif;

        return $return;
    }

    protected function login(): void
    {
        if ($this->sessionId === null) :
            $login = $this->eventsManager->fire(
                \VitesseCms\Mustache\Enum\ViewEnum::RENDER_TEMPLATE_EVENT,
                new RenderTemplateDTO(
                    'api_login',
                    '',
                    [
                        'apiLogin' => $this->apiLogin,
                        'apiPassword' => $this->apiPassword,
                    ]
                )
            );

            $ch = curl_init('http://api.spreadshirt.net/api/v1/sessions');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, '');

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'cURL error: ' . curl_error($ch);
                die();
            }

            curl_close($ch);
            
            $this->sessionId = XmlUtil::getAttribute(new SimpleXMLElement($result), 'id');
        endif;
    }

    protected function parseHttpHeaders($header, $headername): ?string
    {
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) :
            if (preg_match('/(' . $headername . '): (.+)/m', $field, $match)) :
                return $match[2];
            endif;
        endforeach;

        return null;
    }
}

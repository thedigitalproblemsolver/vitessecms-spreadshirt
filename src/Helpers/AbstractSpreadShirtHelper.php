<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Core\Utils\XmlUtil;
use \SimpleXMLElement;

abstract class AbstractSpreadShirtHelper extends AbstractInjectable
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiSecret;

    /**
     * @var string
     */
    protected $shopId;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $userUrl;

    /**
     * @var string
     */
    protected $apiLogin;

    /**
     * @var string
     */
    protected $apiPassword;

    /**
     * @var ?string
     */
    protected $sessionId;

    /**
     * @var ViewService
     */
    protected $view;

    public function __construct(ViewService $view)
    {
        $this->apiKey = $this->setting->get('SPREADSHIRT_API_KEY');
        $this->shopId = $this->setting->get('SPREADSHIRT_SHOP_ID');
        $this->userId = $this->setting->get('SPREADSHIRT_USER_ID');
        $this->apiLogin = $this->setting->get('SPREADSHIRT_API_LOGIN');
        $this->apiPassword = $this->setting->get('SPREADSHIRT_API_PASSWORD');
        $this->apiSecret = $this->setting->get('SPREADSHIRT_API_SECRET');
        $this->baseUrl = 'https://api.spreadshirt.net/api/v1/shops/' . $this->shopId . '/';
        $this->userUrl = 'https://api.spreadshirt.net/api/v1/users/' . $this->userId . '/';
        $this->sessionId = null;
        $this->view = $view;
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
    )
    {
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
            $login = simplexml_load_string($this->view->renderModuleTemplate(
                'spreadshirt',
                'api_login',
                'xml',
                [
                    'apiLogin' => $this->apiLogin,
                    'apiPassword' => $this->apiPassword,
                ]
            ));

            $ch = curl_init('http://api.spreadshirt.net/api/v1/sessions');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $login->asXML());
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

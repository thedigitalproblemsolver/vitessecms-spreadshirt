<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use SimpleXMLElement;
use VitesseCms\Core\Utils\XmlUtil;

final class DesignHelper extends AbstractSpreadShirtHelper
{
    public function get(string $designId): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'designs/' . $designId, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }

    public function getAll(): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->userUrl . 'designs', 'GET', null, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }

    public function createDesign(string $name, string $description): string
    {
        $ch = $this->getCurlInstance(
            $this->baseUrl . 'designs',
            'POST'
        //'application/xml'
        );
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <design xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://api.spreadshirt.net">
                <name>' . $name . '</name>
                <description>' . $description . '</description>
            </design>'
        );
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump($result);
        die();
        return XmlUtil::getAttribute(new SimpleXMLElement($result), 'id');
    }

    public function updateDesign(string $id, string $name, string $description, float $price, array $designCategories)
    {
        $designCategoryString = '';
        foreach ($designCategories as $designCategory) :
            $designCategoryString .= '<designCategory id="' . $designCategory . '"/>';
        endforeach;

        $ch = $this->getCurlInstance($this->baseUrl . 'designs/' . $id, 'PUT', 'application/xml');
        curl_setopt(
            $ch,
            CURLOPT_PUT,
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <design 
                xmlns:xlink="http://www.w3.org/1999/xlink" 
                xmlns="http://api.spreadshirt.net"
                weight="0.0" 
                xlink:href="' . $this->baseUrl . 'designs/' . $id . '" 
                id="' . $id . '"
            >
                <name>' . $name . '</name>
                <description>' . $description . '</description>
                <designCategories>
                    ' . $designCategoryString . '
                </designCategories>
                <price>
                    <vatExcluded>' . $price . '</vatExcluded>
                    <vatIncluded>' . $price . '</vatIncluded>
                    <vat>0</vat>
                    <currency xlink:href="https://api.spreadshirt.net/api/v1/currencies/1" id="1"/>
                </price>
            </design>'
        );
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @param string $designId
     *
     * @return string
     */
    public function getImageUploadUrl(string $designId): string
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'designs/' . $designId, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        $start = strpos($result, 'resource xlink:href="') + 21;
        $end = strpos($result, '"', $start);

        return substr($result, $start, $end - $start);
    }

    public function uploadDesign(string $uploadUrl, string $image)
    {
        $ch = $this->getCurlInstance($uploadUrl . '?method=PUT', 'PUT', mime_content_type($image));
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($image));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /*public function getCategories(): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'designCategories/', 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        var_dump($result);
        die();

        return new SimpleXMLElement($result);
    }*/
}

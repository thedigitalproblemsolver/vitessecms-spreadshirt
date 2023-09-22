<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Helpers;

use Phalcon\Events\Manager;
use SimpleXMLElement;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Mustache\DTO\RenderTemplateDTO;
use VitesseCms\Mustache\Enum\ViewEnum;
use VitesseCms\Spreadshirt\DTO\ProductDTO;
use VitesseCms\Spreadshirt\Factories\DesignApiFactory;
use VitesseCms\Spreadshirt\Factories\ProductTypeAppearanceFactory;
use VitesseCms\Spreadshirt\Interfaces\RepositoryInterface;
use VitesseCms\Spreadshirt\Models\DesignApi;
use VitesseCms\Spreadshirt\Models\Product;

use function count;

class ProductHelper extends AbstractSpreadShirtHelper
{
    protected ProductDTO $productDTO;
    //protected array $namespaces;
    protected SimpleXMLElement $productType;
    protected array $errors;
    protected DesignApi $designApi;

    public function __construct(Manager $eventsManager)
    {
        parent::__construct($eventsManager);

        $ch = $this->getCurlInstance($this->baseUrl, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        $this->productDTO = new ProductDTO(json_decode($result));
        //$this->namespaces = $this->productDTO->getNamespaces(true);
        $this->errors = [];
    }

    /*public function getNamespaces(): array
    {
        return $this->namespaces;
    }*/

    public function getAppearances(
        Product $product,
        ProductTypeHelper $productTypeHelper,
        PrintTypeHelper $printTypeHelper,
        RepositoryInterface $repositoryCollection
    ): array {
        $design = $repositoryCollection->design->getById($product->getDesignId(), false);
        $productType = $repositoryCollection->productType->getById($product->getProductTypeId(), false);
        if ($design === null || $productType === null):
            return [];
        endif;

        $product->set('name', $productType->_('name') . ' - ' . $design->_('name'), true);
        $productTypeXml = $productTypeHelper->get($productType->_('productTypeId'));

        $this->setDesign($design->getDesignId());
        $this->setProductType($productType->getProductTypeId());

        $appearances = [];
        foreach ($productTypeXml->appearances->appearance as $appearance) :
            $productTypeAppearance = ProductTypeAppearanceFactory::createFromXml($appearance);
            $preparedProduct = $this->prepareProductXml(
                $product,
                $productTypeAppearance->getId(),
                $printTypeHelper->get((int)$product->_('printTypeId'))
            );

            //TODO what if one printype is missing , like in cooking Apron
            $productId = $this->createProduct($preparedProduct);
            if ($productId > 0) :
                $productXml = $this->getProductXmlById($productId);
                $resource = $productXml->resources->resource[0];
                $attributes = $resource->attributes($this->getNamespaces()['xlink']);
                $colorHex = $productTypeAppearance->getColors()[0];
                if (count($productTypeAppearance->getColors()) === 2) :
                    $colorHex = $productTypeAppearance->getColors()[1];
                endif;
                $appearances[] = [
                    'productId' => $productId,
                    'color' => strtolower($colorHex),
                    'colorId' => $productTypeAppearance->getId(),
                    'colorName' => str_replace('/', '_', $productTypeAppearance->getName()),
                    'image' => (string)$attributes->href,
                ];
            endif;
        endforeach;

        return $appearances;
    }

    public function setDesign(string $designId): ProductHelper
    {
        $attributes = $this->productDTO->designs->attributes($this->namespaces['xlink']);
        $ch = $this->getCurlInstance($attributes->href . '/' . $designId, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        //$this->design = new SimpleXMLElement($result);
        $this->designApi = DesignApiFactory::createFromXml(new SimpleXMLElement($result));

        return $this;
    }

    public function setProductType(int $productTypeId): ProductHelper
    {
        $attributes = $this->productDTO->productTypes->attributes($this->namespaces['xlink']);
        $ch = $this->getCurlInstance($attributes->href . '/' . $productTypeId, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        $this->productType = new SimpleXMLElement($result);

        return $this;
    }

    public function prepareProductXml(
        Product $product,
        int $productTypeAppearanceId,
        SimpleXMLElement $printType
    ): SimpleXMLElement {
        $printArea = null;

        foreach ($this->productType->printAreas->printArea as $current) :
            if (XmlUtil::getAttribute($current, 'id') === $product->getProductTypePrintAreaId()) :
                $printArea = $current;
            endif;
        endforeach;

        $printColorRGBs = '';
        $printColorIds = $product->getPrintTypeBaseColor();
        if (
            $product->getPrintTypeBaseColor() === null
            && $this->designApi->getFileExtension() === 'svg'
        ) :
            $printColorRGBs = implode(',', $this->designApi->getColors());
            //$printColorIds  = implode(',',$this->designApi->getColorIds());
        endif;

        switch ($this->designApi->getFileExtension()):
            case 'svg':
                $dimensions = $this->getSvgDimensions($printType, $product->getScale());
                break;
            case 'png':
            default:
                $dimensions = $this->getPngDimensions($printType, $product->getScale());
        endswitch;

        $productXml = $this->eventsManager->fire(
            ViewEnum::RENDER_TEMPLATE_EVENT,
            new RenderTemplateDTO(
                'create_product',
                $this->router->getModuleName() . '/src/Resources/xml/',
                [
                    'printColorRGBs' => $printColorRGBs,
                    'productTypeId' => XmlUtil::getAttribute($this->productType, 'id'),
                    'appearanceId' => $productTypeAppearanceId,
                    'printAreaId' => $product->getProductTypePrintAreaId(),
                    'printTypeId' => XmlUtil::getAttribute($printType, 'id'),
                    'offsetLeft' => ((doubleval($printArea->boundary->size->width) - doubleval(
                                $dimensions['width']
                            )) / 2),
                    'offsetTop' => ((doubleval($printArea->boundary->size->height) - doubleval(
                                    $dimensions['height']
                                )) / 4) + $product->getOffsetTop(),
                    'imageWidth' => $dimensions['width'],
                    'imageHeight' => $dimensions['height'],
                    'designId' => $this->designApi->getId(),
                    'printColorIds' => $printColorIds,
                ]
            )
        );

        return new SimpleXMLElement($productXml);
    }

    public function getSvgDimensions(SimpleXMLElement $printType, float $scale = 1.0): array
    {
        return [
            'width' => $printType->size->width * $scale,
            'height' => $printType->size->height * $scale,
        ];
    }

    public function getPngDimensions(
        SimpleXMLElement $printType,
        float $scale
    ): array {
        return [
            'width' => $printType->size->width * $scale,
            'height' => $printType->size->height * $scale,
        ];
        /*$dimensions = ['width' => 0, 'height' => 0];

        $exp = ($this->design->size->width / $printArea->boundary->size->width) * $scale;

        $mmWidth = $printArea->boundary->size->width;
        $mmHeight = $printArea->boundary->size->height;
        if (
            (float)$printType->size->width < (float)$mmWidth
            || (float)$printType->size->height < (float)$mmHeight
        ) :
            $mmWidth = $printType->size->width;
            $mmHeight = $printType->size->height;
        endif;
        $boundaryWidthInPx = (float)($mmWidth * $exp) / 25.4;
        $boundaryHeightInPx = (float)($mmHeight * $exp) / 25.4;

        $dimensions['width'] = (float)$this->design->size->width;
        $dimensions['height'] = (float)$this->design->size->height;

        if (
            $dimensions['width'] > $boundaryWidthInPx
            || $dimensions['height'] > $boundaryHeightInPx
        ) :
            $dimensions['width'] = $boundaryWidthInPx;
            $dimensions['height'] = $boundaryHeightInPx;
        endif;

        return $dimensions;*/
    }

    public function createProduct(SimpleXMLElement $product): int
    {
        $attributes = $this->productDTO->products->attributes($this->namespaces['xlink']);

        $ch = $this->getCurlInstance($attributes->href, 'POST', 'application/xml');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $product->asXML());
        $result = curl_exec($ch);
        curl_close($ch);
        $productId = (int)XmlUtil::getAttribute(new SimpleXMLElement($result), 'id');

        if ($productId) :
            return $productId;
        else :
            $this->errors[] = $result;
        endif;

        return 0;
    }

    public function getProductXmlById(int $id): SimpleXMLElement
    {
        $ch = $this->getCurlInstance($this->baseUrl . 'products/' . $id, 'GET');
        $result = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($result);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) !== 0;
    }
}

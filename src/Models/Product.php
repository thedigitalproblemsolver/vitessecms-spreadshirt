<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Models;

use VitesseCms\Database\AbstractCollection;

class Product extends AbstractCollection
{
    /**
     * @var ?string
     */
    public $productType;

    /**
     * @var ?string
     */
    public $design;

    /**
     * @var ?string
     */
    public $productTypePrintAreaId;

    /**
     * @var ?string
     */
    public $printTypeId;

    /**
     * @var array
     */
    public $appearances;

    /**
     * @var float
     */
    public $scale;

    /**
     * @var int
     */
    public $offsetTop;

    /**
     * @var ?int
     */
    public $PrintTypeBaseColor;

    public function beforeSave()
    {
        if (
            !empty($this->productType)
            && !empty($this->design)
            && empty($this->_('name'))
        ) :
            $design = Design::findById($this->design);
            $productType = ProductType::findById($this->productType);
            $this->set('name', $productType->_('name').' - '.$design->_('name'), true);
        endif;
    }

    public function getProductTypeId(): ?string
    {
        return $this->productType;
    }

    public function getDesignId(): ?string
    {
        return $this->design;
    }

    public function getProductTypePrintAreaId(): ?string
    {
        return $this->productTypePrintAreaId;
    }

    public function getPrintTypeId(): ?string
    {
        return $this->printTypeId;
    }

    public function getAppearances(): array
    {
        return $this->appearances ?? [];
    }

    public function setAppearances(array $appearances): Product
    {
        $this->appearances = $appearances;

        return $this;
    }

    public function getScale(): float
    {
        return $this->scale ? (float)$this->scale : (float)0;
    }

    public function getOffsetTop(): int
    {
        return $this->offsetTop ? (int)$this->offsetTop : 0;
    }

    public function getPrintTypeBaseColor(): ?int
    {
        if (!empty($this->PrintTypeBaseColor)):
            return (int) $this->PrintTypeBaseColor;
        endif;

        return null;
    }

    public function setPrintTypeBaseColor($PrintTypeBaseColor): Product
    {
        $this->PrintTypeBaseColor = $PrintTypeBaseColor;

        return $this;
    }
}

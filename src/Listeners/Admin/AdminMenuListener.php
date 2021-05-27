<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners\Admin;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use Phalcon\Events\Event;

class AdminMenuListener
{
    /**
     * @var bool
     */
    private $hasApiKey;

    /**
     * @var string
     */
    private $languageShort;

    public function __construct(bool $hasApiKey, string $languageShort)
    {
        $this->hasApiKey = $hasApiKey;
        $this->languageShort = $languageShort;
    }

    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($this->hasApiKey) :
            $children = new AdminMenuNavBarChildren();
            $children->addChild('Order', 'admin/spreadshirt/admincheckout/index')
                ->addChild('Products', 'admin/spreadshirt/adminproduct/adminList')
                ->addChild('Designs', 'admin/spreadshirt/admindesign/adminList')
                ->addChild('ProductTypes', 'admin/spreadshirt/adminproducttype/adminList')
                ->addChild('PrintTypes', 'admin/spreadshirt/adminprinttype/adminList')
                ->addChild('Settings', 'admin/setting/adminsetting/adminList?filter[name.' . $this->languageShort . ']=spreadshirt')
                ->addChild('Render ShopProduct', 'spreadshirt/product/rendershopproduct ');

            $adminMenu->addDropdown('Spreadshirt', $children);
        endif;
    }
}

<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use Phalcon\Di;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if (
            $adminMenu->getUser()->getPermissionRole() === 'superadmin'
            && $adminMenu->getSetting()->has('SPREADSHIRT_API_KEY')
        ) :
            $children = new AdminMenuNavBarChildren();
            $children->addChild('Order', 'admin/spreadshirt/admincheckout/index')
                ->addChild('Products', 'admin/spreadshirt/adminproduct/adminList')
                ->addChild('Designs', 'admin/spreadshirt/admindesign/adminList')
                ->addChild('ProductTypes', 'admin/spreadshirt/adminproducttype/adminList')
                ->addChild('PrintTypes', 'admin/spreadshirt/adminprinttype/adminList')
                ->addChild('Settings', 'admin/setting/adminsetting/adminList?filter[name.'.
                    Di::getDefault()->get('configuration')->getLanguageShort().
                    ']=spreadshirt'
                )
                ->addChild('Render ShopProduct','spreadshirt/product/rendershopproduct ')
            ;

            $adminMenu->addDropdown('Spreadshirt', $children);
        endif;
    }
}

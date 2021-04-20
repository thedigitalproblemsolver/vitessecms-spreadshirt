<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Utils\XmlUtil;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Spreadshirt\Controllers\AdmindesignController;
use VitesseCms\Spreadshirt\Models\Design;
use function count;

class AdmindesignControllerListener
{
    public function beforeModelSave(Event $event, AdmindesignController $controller, Design $design): void
    {
        if (!$design->_('designId')) :
            Item::setFindPublished(false);
            $baseDesign = Item::findById($design->_('baseDesign'));
            $file = $controller->config->get('uploadDir') . $baseDesign->_('spreadshirtRasterizedImage');
            if (is_file($file)) :
                $design->set('designId',
                    $controller->spreadshirt->design->createDesign($design->_('name'), $baseDesign->_('description')));
                $controller->spreadshirt->design->uploadDesign(
                    $controller->spreadshirt->design->getImageUploadUrl($design->_('designId')),
                    $file
                );
            endif;
        endif;

        if ($design->_('designId') && empty($design->_('printTypeIds'))) :
            $designXml = $controller->spreadshirt->design->get($design->_('designId'));
            $printTypeIds = [];
            foreach ($designXml->printTypes->printType as $printType) :
                $printTypeIds[] = (int)XmlUtil::getAttribute($printType, 'id');
            endforeach;
            if (count($printTypeIds)) :
                $design->set('printTypeIds', $printTypeIds);
            endif;
        endif;

        if (MongoUtil::isObjectId($design->_('baseDesign'))) :
            Item::setFindPublished(false);
            $baseDesign = Item::findById($design->_('baseDesign'));
            $design->name = $baseDesign->name;
        endif;
    }
}

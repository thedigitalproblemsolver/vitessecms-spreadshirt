<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Controllers;

use Phalcon\Mvc\Collection\Exception;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Spreadshirt\Forms\PrintTypeForm;
use VitesseCms\Spreadshirt\Models\PrintType;

/**
 * Class AdminprinttypeController
 */
class AdminprinttypeController extends AbstractAdminController
{
    /**
     * construct
     * @throws Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = PrintType::class;
        $this->classForm = PrintTypeForm::class;
    }
}

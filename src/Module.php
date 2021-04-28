<?php declare(strict_types=1);

namespace VitesseCms\Spreadshirt;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\RepositoryCollection;
use VitesseCms\Spreadshirt\Services\SpreadshirtService;
use Phalcon\DiInterface;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        $di->setShared('spreadshirt', new SpreadshirtService($di->getView()));
        $di->setShared('repositories', new RepositoryCollection(
            new ProductRepository(),
            new ItemRepository(),
            new DesignRepository(),
            new ProductTypeRepository()
        ));
        parent::registerServices($di, 'Spreadshirt');
    }
}

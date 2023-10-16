<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt;

use Phalcon\Di\DiInterface;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Spreadshirt\Helpers\BasketHelper;
use VitesseCms\Spreadshirt\Helpers\DesignHelper;
use VitesseCms\Spreadshirt\Helpers\PrintTypeHelper;
use VitesseCms\Spreadshirt\Helpers\ProductHelper;
use VitesseCms\Spreadshirt\Helpers\ProductTypeHelper;
use VitesseCms\Spreadshirt\Helpers\ProductTypeViewHelper;
use VitesseCms\Spreadshirt\Helpers\SellableHelper;
use VitesseCms\Spreadshirt\Repositories\DesignRepository;
use VitesseCms\Spreadshirt\Repositories\ProductRepository;
use VitesseCms\Spreadshirt\Repositories\ProductTypeRepository;
use VitesseCms\Spreadshirt\Repositories\RepositoryCollection;
use VitesseCms\Spreadshirt\Services\SpreadshirtService;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        $di->setShared(
            'spreadshirt',
            new SpreadshirtService(
                new ProductHelper($di->getEventsManager()),
                new DesignHelper($di->getEventsManager()),
                new PrintTypeHelper($di->getEventsManager()),
                new ProductTypeHelper($di->getEventsManager()),
                new ProductTypeViewHelper($di->getEventsManager()),
                new BasketHelper($di->getEventsManager()),
                new SellableHelper($di->getEventsManager())
            )
        );
        $di->setShared(
            'repositories',
            new RepositoryCollection(
                new ProductRepository(),
                new ItemRepository(),
                new DesignRepository(),
                new ProductTypeRepository()
            )
        );
        parent::registerServices($di, 'Spreadshirt');
    }
}

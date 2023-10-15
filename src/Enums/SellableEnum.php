<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Enums;

enum SellableEnum: string
{
    case LISTENER = 'SellableListener';
    case GET_REPOSITORY = 'SellableListener:getRepository';
    case HANDLE_IMPORT = 'SellableListener:handleImport';
}
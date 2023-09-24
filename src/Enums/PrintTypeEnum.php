<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Enums;

enum PrintTypeEnum: string
{
    case LISTENER = 'PrintTypeListener';
    case GET_REPOSITORY = 'PrintTypeListener:getRepository';
}
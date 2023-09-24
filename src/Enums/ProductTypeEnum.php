<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Enums;

enum ProductTypeEnum: string
{
    case LISTENER = 'ProductTypeListener';
    case GET_REPOSITORY = 'ProductTypeListener:getRepository';
}
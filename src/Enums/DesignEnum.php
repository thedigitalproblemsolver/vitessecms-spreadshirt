<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Enums;

enum DesignEnum: string
{
    case LISTENER = 'DesignListener';
    case GET_REPOSITORY = 'DesignListener:getRepository';
}
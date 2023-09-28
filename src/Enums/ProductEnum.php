<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\Enums;

enum ProductEnum: string
{
    case LISTENER = 'ProductListener';
    case GET_REPOSITORY = 'ProductListener:getRepository';
    case CONVERT_TO_SHOP_PRODUCT = 'ProductListener:convertToShopProduct';
}
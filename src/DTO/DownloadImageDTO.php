<?php

declare(strict_types=1);

namespace VitesseCms\Spreadshirt\DTO;

final class DownloadImageDTO
{
    public function __construct(
        public readonly string $source,
        public readonly string $target
    ) {
    }
}
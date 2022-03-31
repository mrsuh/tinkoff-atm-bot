<?php

namespace App\Tinkoff\DTO;

use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class Point extends DataTransferObject
{
    public string $id      = '';
    public string $address = '';

    /** @var Limit[] */
    #[CastWith(ArrayCaster::class, itemType: Limit::class)]
    public array $limits = [];
}

<?php

namespace App\Tinkoff\DTO;

use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\DataTransferObject;

class Coordinates extends DataTransferObject
{
    #[MapFrom('lat')]
    public float $latitude = 0;

    #[MapFrom('lng')]
    public float $longitude = 0;
}

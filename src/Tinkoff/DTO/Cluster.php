<?php

namespace App\Tinkoff\DTO;

use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\MapFrom;
use Spatie\DataTransferObject\Casters\ArrayCaster;
use Spatie\DataTransferObject\DataTransferObject;

class Cluster extends DataTransferObject
{
    public string $id = '';

    /** @var Point[] */
    #[CastWith(ArrayCaster::class, itemType: Point::class)]
    public array $points = [];

    #[MapFrom('bounds.bottomLeft')]
    public Coordinates $bottomLeftCoordinates;

    #[MapFrom('bounds.topRight')]
    public Coordinates $topRightCoordinates;
}

<?php

namespace App\Tinkoff\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Limit extends DataTransferObject
{
    public const USD = 'USD';
    public const RUB = 'RUB';
    public const EUR = 'EUR';

    public string $currency = '';
    public int    $amount   = 0;
}

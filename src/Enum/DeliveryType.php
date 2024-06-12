<?php

namespace App\Enum;

enum DeliveryType: string
{
    case SELF_DELIVERY = 'selfdelivery';
    case COURIER = 'courier';
}

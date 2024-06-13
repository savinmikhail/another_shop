<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PAYED = 'payed';
    case IN_ASSEMBLY = 'in_assembly';
    case READY_TO_PICKUP = 'ready_to_pickup';
    case IN_DELIVERY = 'in_delivery';
    case CANCELED = 'canceled';
    case COMPLETED = 'completed';
}

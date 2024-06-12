<?php

namespace App\Enum;

enum MessageType: string
{
    case SMS = 'sms';
    case EMAIL = 'email';
}

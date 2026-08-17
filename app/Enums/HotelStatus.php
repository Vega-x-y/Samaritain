<?php

namespace App\Enums;

enum HotelStatus: string
{
    case AVAILABLE = 'available';
    case SOLD = 'sold';
    case RENTED = 'rented';
}

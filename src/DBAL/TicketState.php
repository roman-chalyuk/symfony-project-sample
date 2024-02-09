<?php

declare(strict_types=1);

namespace App\DBAL;

class TicketState extends EnumType
{
    public const REGISTERED = 'registered';
    public const PAID = 'paid';
    public const CANCELED = 'canceled';

    public const TYPES = [
        self::REGISTERED,
        self::PAID,
        self::CANCELED,
    ];

    protected $name = 'ticket_state';
    protected $values = self::TYPES;
}

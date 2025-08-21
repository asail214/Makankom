<?php

namespace App\Exceptions;

class BusinessLogicException extends ApiException
{
    public static function ticketNotAvailable(): self
    {
        return new self(
            'Requested tickets are not available',
            'TICKETS_NOT_AVAILABLE',
            422
        );
    }

    public static function eventNotApproved(): self
    {
        return new self(
            'Event is not approved for ticket sales',
            'EVENT_NOT_APPROVED',
            403
        );
    }

    public static function orderCannotBeCancelled(): self
    {
        return new self(
            'Order cannot be cancelled at this stage',
            'ORDER_CANCELLATION_NOT_ALLOWED',
            422
        );
    }

    public static function scanPointNotAuthorized(): self
    {
        return new self(
            'Scan point not authorized for this event',
            'SCAN_POINT_UNAUTHORIZED',
            403
        );
    }
}
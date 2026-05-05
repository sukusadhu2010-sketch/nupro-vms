<?php

namespace App\Exceptions;

use Exception;

class QuotationAlreadyConvertedException extends Exception
{
    public function __construct(string $message = 'Quotation has already been converted to a sales order.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}


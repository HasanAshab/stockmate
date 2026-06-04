<?php

namespace App\Exceptions;


class InsufficientStockException extends APIException
{
    public function __construct() {
        parent::__construct('Insufficient Stock', 400);
    }
}
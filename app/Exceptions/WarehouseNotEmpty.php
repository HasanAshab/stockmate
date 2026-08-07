<?php

namespace App\Exceptions;

class WarehouseNotEmpty extends APIException
{
    public function __construct()
    {
        parent::__construct('Cannot delete warehouse with existing stock. Transfer or clear stock first.', 400);
    }
}

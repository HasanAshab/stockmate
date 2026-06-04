<?php

namespace App\Exceptions;

use Exception;

class APIException extends Exception
{
    protected $message;

    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->message,
        ], $this->code);
    }
}

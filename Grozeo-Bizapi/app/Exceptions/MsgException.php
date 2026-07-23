<?php

namespace App\Exceptions;

use Exception;

class MsgException extends Exception
{
    public function toJsonResponse()
    {
        $code = $this->getCode() ?: 400;
        return new ErrorResponse($this->getMessage(), $code);
    }
}

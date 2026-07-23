<?php

namespace App\Exceptions;

use App\Http\Responses\ErrorResponse;
use Exception;

class OfferException extends Exception
{
    public function toJsonResponse()
    {
        $code = $this->getCode() ?: 400;
        return new ErrorResponse($this->getMessage(), $code);
    }
}

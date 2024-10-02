<?php

namespace App\Exception;

use Throwable;

class NotAllowedException extends \Exception
{
    public function __construct(string $reason)
    {
        parent::__construct('This action was not allowed due to : ' . $reason);
    }

}
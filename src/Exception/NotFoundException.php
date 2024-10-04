<?php

namespace App\Exception;

class NotFoundException extends \Exception
{
    public function __construct(string $message = 'Entity not found')
    {
        parent::__construct($message);
    }
}
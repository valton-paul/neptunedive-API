<?php

namespace App\Exception;

class AlreadyExistsException extends \Exception
{

    public function __construct(String $message = "You try to add something that exists already")
    {
        parent::__construct(
            $message
        );
    }

}
<?php

namespace App\Exception;

class AlreadyExistsException extends \Exception
{

    public function __construct()
    {
        parent::__construct(
            "You try to add something that exists already"
        );
    }

}
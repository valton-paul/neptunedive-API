<?php

namespace App\Exception;

class MemberNotFoundException extends \Exception
{

    public function __construct()
    {
        parent::__construct(
            'Member not found'
        );
    }

}
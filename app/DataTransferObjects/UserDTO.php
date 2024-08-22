<?php

namespace App\DataTransferObjects;

class UserDTO
{
    public $name;
    public $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
}

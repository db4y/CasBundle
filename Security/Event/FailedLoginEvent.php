<?php

namespace db4y\CasBundle\Security\Event;

use Symfony\Component\EventDispatcher\Event;

class FailedLoginEvent extends Event
{
    /**
     * @var
     */
    private $username;

    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }
}

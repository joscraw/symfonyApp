<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmailCheck
 * @package App\Model
 */
class EmailCheck
{

    /**
     * @var string
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.")
     */
    protected $emailAddress;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return EmailCheck
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

}
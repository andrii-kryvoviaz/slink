<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

class RegistrationIsNotAllowed extends \LogicException
{
    public function __construct(string $message = 'Sign up is not allowed')
    {
        parent::__construct($message);
    }
}
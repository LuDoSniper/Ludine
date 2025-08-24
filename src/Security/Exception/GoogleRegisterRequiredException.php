<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GoogleRegisterRequiredException extends AuthenticationException {}
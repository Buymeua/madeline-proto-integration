<?php

declare(strict_types=1);

namespace Buyme\MadelineProtoIntegration\Exceptions\Auth;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class MadelineNotLoggedInException extends Exception
{
    protected $code = Response::HTTP_UNAUTHORIZED;
}

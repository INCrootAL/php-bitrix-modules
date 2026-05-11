<?php

namespace Incrootal\Migration\Exceptions;

use Exception;
use Incrootal\Migration\Out;
use Throwable;

class ConsoleException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = Out::prepareToConsole('[red]'. $message .'[/]');

        parent::__construct($message, $code, $previous);
    }
}

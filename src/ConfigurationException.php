<?php

namespace Etdte\SvcHttp;

use Exception;

final class ConfigurationException extends Exception
{
    /**
     * Throws itself, obviously
     *
     * @throws \Etdte\SvcHttp\ConfigurationException
     */
    public static function notSettled()
    {
        throw new static('Service is not configured properly, check configuration.');
    }
}

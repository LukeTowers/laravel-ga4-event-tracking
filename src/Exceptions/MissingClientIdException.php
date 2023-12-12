<?php

namespace DevPro\GA4EventTracking\Exceptions;

use Exception;

class MissingClientIdException extends Exception
{
    protected $message = 'Missing Client ID. Please set a client ID or use the provided Blade directive.';
}

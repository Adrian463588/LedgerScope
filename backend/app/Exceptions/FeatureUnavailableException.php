<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class FeatureUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'This feature is not available yet.')
    {
        parent::__construct($message);
    }
}

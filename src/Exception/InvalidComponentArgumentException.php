<?php

declare(strict_types=1);

namespace Boson\Component\Http\Exception;

use Boson\Contracts\Http\Exception\InvalidComponentArgumentExceptionInterface;

class InvalidComponentArgumentException extends \InvalidArgumentException implements
    InvalidComponentArgumentExceptionInterface {}

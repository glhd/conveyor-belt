<?php

namespace Glhd\ConveyorBelt\Exceptions;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Throwable;

class AbortConveyorBeltException extends RuntimeException
{
	public function __construct(string $message = '', int $code = Command::FAILURE, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}

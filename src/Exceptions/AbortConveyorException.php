<?php

namespace Glhd\Conveyor\Exceptions;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Throwable;

class AbortConveyorException extends RuntimeException
{
	public function __construct(string $message = '', int $code = Command::FAILURE, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}

<?php

namespace Glhd\ConveyorBelt\Support;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CollectedException
{
	public Throwable $exception;
	
	public ?string $label = null;
	
	protected bool $verbose;
	
	public function __construct(Throwable $exception, bool $verbose, string $row_name, $item = null)
	{
		$this->exception = $exception;
		$this->verbose = $verbose;
		
		if ($item instanceof Model) {
			$this->label = "{$row_name} #{$item->getKey()}";
		}
	}
	
	public function __toString()
	{
		$message = $this->exception->getMessage();
		
		if ($this->label) {
			$message = "[{$this->label}] $message";
		}
		
		if ($this->verbose) {
			$message .= "\n".$this->exception->getTraceAsString();
		}
		
		return $message;
	}
}

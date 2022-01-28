<?php

namespace Glhd\ConveyorBelt\Concerns;

use Closure;

trait InteractsWithOutputDuringProgress
{
	public function progressMessage($message)
	{
		$this->conveyor_belt->progress->message($message);
	}
	
	public function progressSubMessage($message)
	{
		$this->conveyor_belt->progress->subMessage($message);
	}
	
	public function withoutProgress(Closure $callback)
	{
		return $this->conveyor_belt->progress->interrupt($callback);
	}
	
	public function confirm($question, $default = false)
	{
		return $this->withoutProgress(fn() => parent::confirm($question, $default));
	}
	
	public function ask($question, $default = null)
	{
		return $this->withoutProgress(fn() => parent::ask($question, $default));
	}
	
	public function anticipate($question, $choices, $default = null)
	{
		return $this->withoutProgress(fn() => parent::anticipate($question, $choices, $default));
	}
	
	public function askWithCompletion($question, $choices, $default = null)
	{
		return $this->withoutProgress(fn() => parent::askWithCompletion($question, $choices, $default));
	}
	
	public function secret($question, $fallback = true)
	{
		return $this->withoutProgress(fn() => parent::secret($question, $fallback));
	}
	
	public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false)
	{
		return $this->withoutProgress(fn() => parent::choice($question, $choices, $default, $attempts, $multiple));
	}
	
	public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
	{
		return $this->withoutProgress(fn() => parent::table($headers, $rows, $tableStyle, $columnStyles));
	}
	
	public function info($string, $verbosity = null)
	{
		return $this->withoutProgress(fn() => parent::info($string, $verbosity));
	}
	
	public function line($string, $style = null, $verbosity = null)
	{
		return $this->withoutProgress(fn() => parent::line($string, $style, $verbosity));
	}
	
	public function comment($string, $verbosity = null)
	{
		return $this->withoutProgress(fn() => parent::comment($string, $verbosity));
	}
	
	public function question($string, $verbosity = null)
	{
		return $this->withoutProgress(fn() => parent::question($string, $verbosity));
	}
	
	public function error($string, $verbosity = null)
	{
		return $this->withoutProgress(fn() => parent::error($string, $verbosity));
	}
	
	public function warn($string, $verbosity = null)
	{
		return $this->withoutProgress(fn() => parent::warn($string, $verbosity));
	}
	
	public function alert($string)
	{
		return $this->withoutProgress(fn() => parent::alert($string));
	}
	
	public function newLine($count = 1)
	{
		if ($this->conveyor_belt->progress->enabled()) {
			return;
		}
		
		return parent::newLine($count);
	}
}

<?php

namespace Glhd\ConveyorBelt\Concerns;

use Closure;
use Symfony\Component\Console\Output\OutputInterface;

trait RespectsVerbosity
{
	public function whenVerbose(Closure $closure, $default = null)
	{
		if ($this->getOutput()->isVerbose()) {
			return $closure();
		}
		
		return value($default);
	}
	
	public function confirmWhenVerbose($question, $default = false)
	{
		return $this->whenVerbose(fn() => $this->confirm($question, $default), $default);
	}
	
	public function askWhenVerbose($question, $default = null)
	{
		return $this->whenVerbose(fn() => $this->ask($question, $default), $default);
	}
	
	public function anticipateWhenVerbose($question, $choices, $default = null)
	{
		return $this->whenVerbose(fn() => $this->anticipate($question, $choices, $default), $default);
	}
	
	public function askWithCompletionWhenVerbose($question, $choices, $default = null)
	{
		return $this->whenVerbose(fn() => $this->askWithCompletion($question, $choices, $default), $default);
	}
	
	public function secretWhenVerbose($question, $fallback = true, $default = null)
	{
		return $this->whenVerbose(fn() => $this->secret($question, $fallback), $default);
	}
	
	public function choiceWhenVerbose($question, array $choices, $default = null, $attempts = null, $multiple = false)
	{
		return $this->whenVerbose(fn() => $this->choice($question, $choices, $default, $attempts, $multiple), $default);
	}
	
	public function tableWhenVerbose($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
	{
		return $this->whenVerbose(fn() => $this->table($headers, $rows, $tableStyle, $columnStyles));
	}
	
	public function infoWhenVerbose($string, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
	{
		$this->info($string, $verbosity);
	}
	
	public function lineWhenVerbose($string, $style = null, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
	{
		$this->line($string, $style, $verbosity);
	}
	
	public function commentWhenVerbose($string, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
	{
		$this->comment($string, $verbosity);
	}
	
	public function questionWhenVerbose($string, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
	{
		$this->question($string, $verbosity);
	}
	
	public function errorWhenVerbose($string, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
	{
		$this->error($string, $verbosity);
	}
	
	public function warnWhenVerbose($string, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
	{
		$this->warn($string, $verbosity);
	}
	
	public function alertWhenVerbose($string)
	{
		return $this->whenVerbose(fn() => $this->alert($string));
	}
	
	public function newLineWhenVerbose($count = 1)
	{
		return $this->whenVerbose(fn() => $this->newLine($count));
	}
}
